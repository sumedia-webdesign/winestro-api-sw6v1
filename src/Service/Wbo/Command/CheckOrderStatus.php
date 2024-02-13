<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Service\Wbo\Command;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryDefinition;
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
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\ConnectorInterface;
use Sumedia\Wbo\Service\Wbo\Request\CheckOrderStatus as CheckOrderStatusRequest;

class CheckOrderStatus extends AbstractCommand implements CommandInterface
{
    /** @var WboConfig */
    protected $config;

    /** @var ConnectorInterface */
    protected $connector;

    /** @var Context */
    protected $context;

    /** @var CheckOrderStatusRequest */
    protected $checkOrderStatus;

    /** @var EntityRepository */
    protected $orderRepository;

    /** @var EntityRepository */
    protected $orderDeliveryRepository;

    /** @var StateMachineRegistry */
    protected $stateMachineRegistry;

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
        $orderCriteria->addFilter(new RangeFilter('orderDateTime', [
            RangeFilter::GT => date('Y-m-d', time() - 60 * 60 * 24 * 30)
        ]));
        return $this->orderRepository->search($orderCriteria, $this->context);
    }

    protected function setOrdersStatus(EntitySearchResult $orders): void
    {
        foreach ($orders as $order) {
            $this->setOrderStatus($order);
        }
    }

    protected function setOrderStatus(OrderEntity $order): void
    {
        try {
            $status = $this->getOrderStatus($order);
            switch ($status) {
                case 'erledigt':
                    try {
                        $this->stateMachineRegistry->transition(new Transition(
                            OrderDefinition::ENTITY_NAME,
                            $order->getId(),
                            'complete',
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
                            'complete',
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
            $this->setOrderCustomFieldsStatus($order, $status);
        } catch(\Exception $e) {
            $this->logException($e);
        }
    }

    protected function getOrderStatus(OrderEntity $order): string
    {
        $customFields = $order->getCustomFields();
        if (!isset($customFields['wbo_order_number'])) {
            return '';
        }

        $this->checkOrderStatus->setOrderNumber($customFields['wbo_order_number']);

        $orderStatus = 'new';
        try {
            /** @var \Sumedia\Wbo\Service\Wbo\Response\CheckOrderStatus $response */
            $response = $this->connector->execute($this->checkOrderStatus);
            if (!$response->isSuccessful()) {
                $this->log('could not export order (' . $order->getOrderNumber() . '): ' . $response->getError());
            } else {
                $orderStatus = $response->getOrderStatus();
            }
        } catch(\Throwable $e) {
            $this->logException($e);
        }

        return $orderStatus;
    }

    protected function setOrderCustomFieldsStatus(OrderEntity $order, string $status): void
    {
        $customFields = $order->getCustomFields();
        $customFields['wbo_order_status'] = $status;
        $this->orderRepository->update([
            [
                'id' => $order->getId(),
                'customFields' => $customFields
            ]
        ], $this->context);
    }
}
