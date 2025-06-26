<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Service\Wbo\Command;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionEntity;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\ConnectorInterface;
use Sumedia\Wbo\Service\Wbo\Request\CheckOrderStatus as CheckOrderStatusRequest;

class CheckOrderStatus extends AbstractCommand implements CommandInterface
{
    protected WboConfig $config;
    protected ConnectorInterface $connector;
    protected Context $context;
    protected CheckOrderStatusRequest $checkOrderStatus;
    protected EntityRepository $orderRepository;
    protected EntityRepository $orderDeliveryRepository;
    protected StateMachineRegistry $stateMachineRegistry;

    public function __construct(
        LoggerInterface $debugLogger,
        LoggerInterface $errorLogger,
        WboConfig $wboConfig,
        ConnectorInterface $connector,
        Context $context,
        CheckOrderStatusRequest $checkOrderStatus,
        EntityRepository $orderRepository,
        EntityRepository $orderDeliveryRepository,
        StateMachineRegistry $stateMachineRegistry
    ) {
        parent::__construct($errorLogger, $debugLogger, $wboConfig);
        $this->connector = $connector;
        $this->context = $context;
        $this->checkOrderStatus = $checkOrderStatus;
        $this->orderRepository = $orderRepository;
        $this->orderDeliveryRepository = $orderDeliveryRepository;
        $this->stateMachineRegistry = $stateMachineRegistry;
    }

    public function execute(): void
    {
        $this->debug('wbo: update order status');

        if (!$this->isActive()) {
            $this->debug('wbo is not active');
            return;
        }

        try {
            $orders = $this->getOrders();
            $this->setOrdersStatus($orders);
        } catch(\Throwable $e) {
            $this->logException($e);
        }
    }

    protected function getOrders() : EntitySearchResult
    {
        $orderCriteria = new Criteria();
        $orderCriteria->addAssociation('transactions');
        $orderCriteria->addAssociation('deliveries');
        $orderCriteria->addFilter(new RangeFilter('orderDateTime', [
            RangeFilter::GT => date('Y-m-d', time() - 60 * 60 * 24 * 30)
        ]));
        //$orderCriteria->addFilter(new EqualsFilter('customFields.wbo_order_status', 'erledigt'));
        return $this->orderRepository->search($orderCriteria, $this->context);
    }

    protected function setOrdersStatus(EntitySearchResult $orders): void
    {
        foreach ($orders as $order) {
            $customFields = $this->getOrderCustomFields($order);
            $this->setOrderStatus($order, $customFields);
            $this->setPaymentStatus($order, $customFields);
            $this->setDeliveryStatus($order, $customFields);
            $this->setOrderCustomFields($order, $customFields);
        }
    }

    protected function getOrderCustomFields(OrderEntity $order): array
    {
        $customFields = $order->getCustomFields() ?? [];

        if (($customFields['wbo_order_number'] ?? '') == '') {
            return $customFields;
        }

        $this->checkOrderStatus->setOrderNumber($customFields['wbo_order_number']);

        try {
            /** @var \Sumedia\Wbo\Service\Wbo\Response\CheckOrderStatus $response */
            $response = $this->connector->execute($this->checkOrderStatus);
            if (!$response->isSuccessful()) {
                $this->log('could not export order (' . $order->getOrderNumber() . '): ' . $response->getError());
            } else {
                $customFields['wbo_order_status'] = $response->getOrderStatus();
                $customFields['wbo_payment_status'] = $response->getPaymentStatus();
                $customFields['wbo_delivery_link'] = $response->getDeliveryLink();
                $customFields['wbo_billing_number'] = $response->getBillingNumber();
            }
        } catch(\Throwable $e) {
            $this->logException($e);
        }

        return $customFields;
    }

    protected function setOrderCustomFields(OrderEntity $order, array $customFields): void
    {
        $customFields = array_replace_recursive($order->getCustomFields(), $customFields);
        $this->orderRepository->update([['id' => $order->getId(), 'customFields' => $customFields]], $this->context);
    }

    protected function setOrderStatus(OrderEntity $order, array $customFields): void
    {
        try {
            switch ($customFields['wbo_order_status'] ?? '') {
                case 'erledigt':
                    try {
                        $this->stateMachineRegistry->transition(new Transition(
                            OrderDefinition::ENTITY_NAME,
                            $order->getId(),
                            'completed',
                            'stateId'
                        ), $this->context);
                    } catch (\Exception $e) {
                        $this->stateMachineRegistry->transition(new Transition(
                            OrderDefinition::ENTITY_NAME,
                            $order->getId(),
                            'process',
                            'stateId'
                        ), $this->context);

                        $this->stateMachineRegistry->transition(new Transition(
                            OrderDefinition::ENTITY_NAME,
                            $order->getId(),
                            'completed',
                            'stateId'
                        ), $this->context);
                    }
                    break;
                case 'bearbeitung':
                    $this->stateMachineRegistry->transition(new Transition(
                        OrderDefinition::ENTITY_NAME,
                        $order->getId(),
                        'process',
                        'stateId'
                    ), $this->context);
                    break;
            }
        } catch(\Exception $e) {
            $this->logException($e);
        }
    }

    protected function setPaymentStatus(OrderEntity $order, array $customFields): void
    {
        try {
            $orderTransactions = $order->getTransactions();
            $orderTransaction = $orderTransactions->first();
            $orderTransactionId = $orderTransaction->getId();

            if (($customFields['wbo_payment_status'] ?? '') === 'bezahlt') {
                $this->stateMachineRegistry->transition(new Transition(
                    OrderTransactionDefinition::ENTITY_NAME,
                    $orderTransactionId,
                    'paid',
                    'stateId'
                ), $this->context);
            }
        } catch(\Exception $e) {
            $this->logException($e);
        }
    }

    protected function setDeliveryStatus(OrderEntity $order, array $customFields): void
    {
        try {
            if (($customFields['wbo_delivery_link'] ?? '') != '') {
                $orderDeliveries = $order->getDeliveries();
                $orderDelivery = $orderDeliveries->first();
                $orderDeliveryId = $orderDelivery->getId();

                $this->stateMachineRegistry->transition(new Transition(
                    OrderDeliveryDefinition::ENTITY_NAME, // 'order_delivery'
                    $orderDeliveryId,                     // ID der Lieferung
                    'ship',                               // oder 'complete' je nach Ziel
                    'stateId'
                ), $this->context);
            }
        } catch(\Exception $e) {
            $this->logException($e);
        }
    }
}
