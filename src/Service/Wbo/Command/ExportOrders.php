<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Service\Wbo\Command;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\ConnectorInterface;
use Sumedia\Wbo\Service\Wbo\PaymentMatcher;
use Sumedia\Wbo\Service\Wbo\Request\ExportOrders as ExportOrdersRequest;
use Sumedia\Wbo\Service\Wbo\ShippingMatcher;
use Sumedia\Wbo\Service\WboPayments;

class ExportOrders extends AbstractCommand implements CommandInterface
{
    protected WboConfig $config;
    protected ConnectorInterface $connector;
    protected Context $context;
    protected ExportOrdersRequest $exportOrdersRequest;
    protected EntityRepository $pluginRepository;
    protected EntityRepository $orderRepository;
    protected EntityRepository $wboOrdersRepository;
    protected EntityRepository $orderAddressRepository;
    protected EntityRepository $orderDeliveryRepository;
    protected EntityRepository $orderTransactionRepository;
    protected EntityRepository $paymentMethodRepository;
    protected EntityRepository $shippingMethodRepository;
    protected EntityRepository $orderLineItemRepository;
    protected EntityRepository $customerRepository;
    protected EntityRepository $salutationRepository;
    protected EntityRepository $countryRepository;
    protected EntityRepository $wboArticlesRepository;
    protected EntityRepository $productRepository;

    public function __construct(
        LoggerInterface $debugLogger,
        LoggerInterface $errorLogger,
        WboConfig $wboConfig,
        ConnectorInterface $connector,
        Context $context,
        ExportOrdersRequest $exportOrdersRequest,
        EntityRepository $pluginRepository,
        EntityRepository $orderRepository,
        EntityRepository $wboOrdersRepository,
        EntityRepository $orderAddressRepository,
        EntityRepository $orderDeliveryRepository,
        EntityRepository $orderTransactionRepository,
        $paymentMethodRepository,
        EntityRepository $shippingMethodRepository,
        EntityRepository $orderLineItemRepository,
        EntityRepository $customerRepository,
        EntityRepository $salutationRepository,
        EntityRepository $countryRepository,
        EntityRepository $wboArticlesRepository,
        EntityRepository $productRepository
    ) {
        parent::__construct($errorLogger, $debugLogger, $wboConfig);
        $this->connector = $connector;
        $this->context = $context;
        $this->pluginRepository = $pluginRepository;
        $this->exportOrdersRequest = $exportOrdersRequest;
        $this->orderRepository = $orderRepository;
        $this->wboOrdersRepository = $wboOrdersRepository;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->orderDeliveryRepository = $orderDeliveryRepository;
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->shippingMethodRepository = $shippingMethodRepository;
        $this->orderLineItemRepository = $orderLineItemRepository;
        $this->customerRepository = $customerRepository;
        $this->salutationRepository = $salutationRepository;
        $this->countryRepository = $countryRepository;
        $this->wboArticlesRepository = $wboArticlesRepository;
        $this->productRepository = $productRepository;
    }

    public function execute(): void
    {
        $this->debug('wbo: exporting orders');

        if (!$this->isActive()) {
            $this->debug('wbo is not active');
            return;
        }

        try {
            $this->migrateOldOrders();
            $orders = $this->getNewestOrders();
            $this->exportOrders($orders);
        } catch(\Throwable $e) {
            $this->logException($e);
        }
    }

    protected function migrateOldOrders(): void
    {
        if (null !== $this->wboOrdersRepository->search((new Criteria())->setLimit(1), $this->context)->first()) {
            return;
        }

        $orderCriteria = new Criteria();
        $orderCriteria->addFilter(new EqualsFilter('customFields.wbo_order_exported_flag', 1));
        $orders = $this->orderRepository->search($orderCriteria, $this->context);
        foreach ($orders as $order) {
            $this->wboOrdersRepository->create([[
                'id' => Uuid::randomHex(),
                'orderId' => $order->getId(),
                'wboOrderNumber' => (string) ($order->getCustomFields()['wbo_order_number'] ?: '-1'),
                'createdAt' => $order->getCreatedAt()
            ]], $this->context);
        }
    }

    protected function getNewestOrders() : array
    {
        $newestOrders = [];

        $orderCriteria = new Criteria();
        $orderCriteria->addFilter(new RangeFilter('createdAt', [
            RangeFilter::GT => $this->getLastOrderDate()->format('Y-m-d H:i:s')
        ]));
        $orders = $this->orderRepository->search($orderCriteria, $this->context);
        foreach ($orders as $order) {
            $wboOrder = $this->wboOrdersRepository->search(
                (new Criteria())
                    ->addFilter(new EqualsFilter('orderId', $order->getId())
            ), $this->context)->first();
            if (null === $wboOrder) {
                $newestOrders[] = $order;
            }
        }

        return $newestOrders;
    }

    protected function getLastOrderDate() : \DateTimeImmutable
    {
        $date = $this->getLastWboOrdersTableDate();
        if ($date === null) {
            $date = new \DateTimeImmutable();
            $date = $date->sub(new \DateInterval('P1D'));
            return $date;
        }
        return $date;
    }

    protected function getLastWboOrdersTableDate(): ?\DateTimeImmutable
    {
        $order = $this->wboOrdersRepository->search(
            (new Criteria())
                ->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING))
                ->setLimit(1),
            $this->context
        )->first();
        if ($order === null) {
            return null;
        }
        return $order->getCreatedAt();
    }

    protected function exportOrders(array $orders): void
    {
        foreach ($orders as $order) {
            $orderNumber = $this->exportOrder($order);
            if (null !== $orderNumber) {
                $this->setOrderExportedOrderNumber($order, $orderNumber);
            }
        }
    }

    protected function exportOrder(OrderEntity $order): ?string
    {
        $items = $this->getWboLineItems($order);
        if (!count($items)) {
            return "-1";
        }

        $billingAddress = $this->getBillingAddress($order);
        $shippingAddress = $this->getShippingAddress($order);

        if (null == $shippingAddress) {
            $shippingAddress = $billingAddress;
        }

        $customer = $this->getCustomer($order);
        if (null === $customer) {
            return "-1";
        }
        $shippingCosts = $order->getShippingCosts()->getTotalPrice();
        $paymentMethodId = $this->getPaymentMethodId($order);
        $wboPaymentMethodCode = $this->paymentMethodIdToWboCode($paymentMethodId);
        $paypalTransactionId = $this->getPaypalTransactionId($order);
        $customerComment = $order->getCustomerComment() ?: '';
        $shippingMethodId = $this->getShippingMethodId($order);
        $wboShippingMethodCode = $this->shippingMethodIdToWboCode($shippingMethodId);

        try {
            return $this->exportOrderToWbo(
                $order,
                $customer,
                $items,
                $billingAddress,
                $shippingAddress,
                $shippingCosts,
                $wboPaymentMethodCode,
                $wboShippingMethodCode,
                $paypalTransactionId,
                $customerComment
            );
        } catch(\Throwable $e) {
            $this->logException($e);
        }
        return null;
    }

    protected function getBillingAddress(OrderEntity $order) : ?OrderAddressEntity
    {
        $billingAddressId = $order->getBillingAddressId();

        $billingAddressCriteria = new Criteria();
        $billingAddressCriteria->addFilter(new EqualsFilter('id', $billingAddressId));
        $billingAddress = $this->orderAddressRepository->search($billingAddressCriteria, $this->context)->first();

        if (!$billingAddress) {
            throw new \RuntimeException('no billing address');
        }

        return $billingAddress;
    }

    protected function getShippingAddress(OrderEntity $order) : ?OrderAddressEntity
    {
        $orderId = $order->getId();

        $orderDeliveryCriteria = new Criteria();
        $orderDeliveryCriteria->addFilter(new EqualsFilter('orderId', $orderId));
        $delivery = $this->orderDeliveryRepository->search($orderDeliveryCriteria, $this->context)->first();

        if (!$delivery) {
            throw new \RuntimeException('no shipping address');
        }

        $shippingAddressCriteria = new Criteria();
        $shippingAddressCriteria->addFilter(new EqualsFilter('id', $delivery->getShippingOrderAddressId()));
        return $this->orderAddressRepository->search($shippingAddressCriteria, $this->context)->first();
    }

    protected function getCustomer(OrderEntity $order) : ?CustomerEntity
    {
        if (!$order->getOrderCustomer() instanceof OrderCustomerEntity) {
            $customer = new CustomerEntity();
            $customer->setEmail('');
            return $customer;
        }

        $customerCriteria = new Criteria();
        $customerCriteria->addFilter(new EqualsFilter('id', $order->getOrderCustomer()->getCustomerId()));
        $customer = $this->customerRepository->search($customerCriteria, $this->context)->first();

        return $customer;
    }

    protected function getPaymentMethodId(OrderEntity $order) : string
    {
        $orderTransactionCriteria = new Criteria();
        $orderTransactionCriteria->addFilter(new EqualsFilter('orderId', $order->getId()));
        $transaction = $this->orderTransactionRepository->search($orderTransactionCriteria, $this->context)->first();

        if (!$transaction) {
            throw new \RuntimeException('no transaction');
        }

        $paymentMethodId = $transaction->getPaymentMethodId();

        return $this->paymentMethodRepository->search(
            new Criteria([$paymentMethodId]),
            $this->context
        )->get($paymentMethodId)->getId();
    }

    protected function getShippingMethodId(OrderEntity $order) : string
    {
        $orderDeliveryCriteria = new Criteria();
        $orderDeliveryCriteria->addFilter(new EqualsFilter('orderId', $order->getId()));
        $delivery = $this->orderDeliveryRepository->search($orderDeliveryCriteria, $this->context)->first();

        if (!$delivery) {
            throw new \RuntimeException('no delivery');
        }

        $shippingMethodId = $delivery->getShippingMethodId();

        return $this->shippingMethodRepository->search(
            new Criteria([$shippingMethodId]),
            $this->context
        )->get($shippingMethodId)->getId();
    }

    protected function getPaypalTransactionId(OrderEntity $order) : string
    {
        $orderTransactionCriteria = new Criteria();
        $orderTransactionCriteria->addFilter(new EqualsFilter('orderId', $order->getId()));
        $transaction = $this->orderTransactionRepository->search($orderTransactionCriteria, $this->context)->first();

        if (!$transaction) {
            return '';
        }

        $customFields = $transaction->getCustomFields();
        return class_exists('Swag\PayPal\SwagPayPal')
            ? $customFields[\Swag\PayPal\SwagPayPal::ORDER_TRANSACTION_CUSTOM_FIELDS_PAYPAL_ORDER_ID] ?? ''
            : '';
    }

    protected function getWboLineItems(OrderEntity $order) : array
    {
        $orderLineItemCriteria = new Criteria();
        $orderLineItemCriteria->addFilter(new EqualsFilter('orderId', $order->getId()));
        $orderLineItems = $this->orderLineItemRepository->search($orderLineItemCriteria, $this->context);

        $items = [];
        foreach ($orderLineItems as $orderLineItem) {
            if (!$this->isWboLineItem($orderLineItem)) {
                continue;
            }
            $items[] = $orderLineItem;
        }

        return $items;
    }

    protected function isArticleDiscount(OrderLineItemEntity $orderLineItem): bool
    {
        return $orderLineItem->getType() == LineItem::PROMOTION_LINE_ITEM_TYPE;
    }

    protected function isWboLineItem(OrderLineItemEntity $orderLineItem): bool
    {
        if ($this->isArticleDiscount($orderLineItem)) {
            return true;
        }

        $productNumber = $orderLineItem->getPayload()['productNumber'] ?? null;
        if($orderLineItem->getProductId()) {
            $product = $this->productRepository->search(
                new Criteria([$orderLineItem->getProductId()]),
                $this->context
            )->first();
            if ($product) {
                $productNumber = $product->getProductNumber();
            }
        }

        $search = $this->wboArticlesRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('productNumber', $productNumber)),
            $this->context
        );

        return $search->count() ? true : false;
    }

    protected function paymentMethodIdToWboCode(string $paymentId): int
    {
        $paymentMatcher = new PaymentMatcher($this->wboConfig);
        $payments = $paymentMatcher->getPaymentIds();
        return $payments[$paymentId] ?? PaymentMatcher::WBO_PAYMENT_FALLBACK_ID;
    }

    protected function shippingMethodIdToWboCode(string $shippingId): int
    {
        $shippingMatcher = new ShippingMatcher($this->wboConfig);
        $shippings = $shippingMatcher->getShippingIds();
        return $shippings[$shippingId] ?? ShippingMatcher::WBO_SHIPPING_FALLBACK_ID;
    }

    protected function getSalutation(string $salutationid) : string
    {
        $salutationCriteria = new Criteria();
        $salutationCriteria->addFilter(new EqualsFilter('id', $salutationid));
        $salutation = $this->salutationRepository->search(
            $salutationCriteria,
            $this->context
        )->first();
        return $salutation->getTranslated()['displayName'];
    }

    protected function getCountry(string $countryId) : string
    {
        $countryCriteria = new Criteria();
        $countryCriteria->addFilter(new EqualsFilter('id', $countryId));
        $country = $this->countryRepository->search(
            $countryCriteria,
            $this->context
        )->first();
        return $country->getIso();
    }

    protected function splitStreet(string $string, string $part): string
    {
        if (preg_match('#^([^\d]*?)(\d+.*?)$#', $string, $matches)) {
            if ($part === 'street' && isset($matches[1])) {
                return $matches[1];
            } elseif ($part === 'number' && isset($matches[2])) {
                return $matches[2];
            }
            return $matches[1] . ' ' . ($matches[2] ?? '');
        }
        return $string;
    }

    protected function exportOrderToWbo(
        OrderEntity $order,
        CustomerEntity $customer,
        array $items,
        OrderAddressEntity $billingAddress,
        OrderAddressEntity $shippingAddress,
        float $shippingCosts,
        int $wboPaymentMethodCode,
        int $wboShippingMethodCode,
        string $paypalTransactionId,
        string $customerComment
    ): int {

        $this->exportOrdersRequest->setSalutation($this->getSalutation($billingAddress->getSalutationId()));
        $this->exportOrdersRequest->setCompany((string) $billingAddress->getCompany());
        $this->exportOrdersRequest->setFirstname($billingAddress->getFirstname());
        $this->exportOrdersRequest->setLastname($billingAddress->getLastname());
        $this->exportOrdersRequest->setStreet($this->splitStreet($billingAddress->getStreet(), 'street'));
        $this->exportOrdersRequest->setStreetnumber($this->splitStreet($billingAddress->getStreet(), 'number'));
        $this->exportOrdersRequest->setZip($billingAddress->getZipcode());
        $this->exportOrdersRequest->setCity($billingAddress->getCity());
        $this->exportOrdersRequest->setCountry($this->getCountry($billingAddress->getCountryId()));
        $this->exportOrdersRequest->setEmail($customer->getEmail());
        $this->exportOrdersRequest->setPhone((string) $billingAddress->getPhoneNumber());
        $this->exportOrdersRequest->setDeliveryCompany((string) $shippingAddress->getCompany());
        $this->exportOrdersRequest->setDeliveryFirstname($shippingAddress->getFirstName());
        $this->exportOrdersRequest->setDeliveryLastname($shippingAddress->getLastName());
        $this->exportOrdersRequest->setDeliveryStreet($this->splitStreet($shippingAddress->getStreet(), 'street'));
        $this->exportOrdersRequest->setDeliveryStreetNumber($this->splitStreet($shippingAddress->getStreet(), 'number'));
        $this->exportOrdersRequest->setDeliveryZip($shippingAddress->getZipcode());
        $this->exportOrdersRequest->setDeliveryCity($shippingAddress->getCity());
        $this->exportOrdersRequest->setDeliveryCountry($this->getCountry($shippingAddress->getCountryId()));
        $this->exportOrdersRequest->setPaymenttype($wboPaymentMethodCode);
        $this->exportOrdersRequest->setPaypalTransactionCode($paypalTransactionId);
        $this->exportOrdersRequest->setNotice($customerComment);
        $this->exportOrdersRequest->setDeliverycosts($shippingCosts);
        $this->exportOrdersRequest->setShippingId($wboShippingMethodCode);
        $this->exportOrdersRequest->setTransactionCode($paypalTransactionId);
        $this->exportOrdersRequest->setOrderItems($items);

        if (!$this->wboConfig->get(WboConfig::AUTOMATIC_ORDER_CONFIRMATION_MAIL_ENABLED)) {
            $this->exportOrdersRequest->setNoEmail();
        }

        $orderNumber = 0;
        try {
            $response = $this->connector->execute($this->exportOrdersRequest);
            if (!$response->isSuccessful()) {
                $this->log('could not export order (' . $order->getOrderNumber() . '): ' . $response->getError());
            } else {
                $orderNumber = (int) $response->get('nr');
            }
        } catch(\Throwable $e) {
            $this->logException($e);
        }

        return $orderNumber;
    }

    protected function setOrderExportedOrderNumber(OrderEntity $order, string $wboOrderNumber): void
    {
        $this->wboOrdersRepository->create([[
            'id' => Uuid::randomHex(),
            'orderId' => $order->getId(),
            'wboOrderNumber' => $wboOrderNumber,
            'createdAt' => new \DateTimeImmutable(),
        ]], $this->context);
    }
}
