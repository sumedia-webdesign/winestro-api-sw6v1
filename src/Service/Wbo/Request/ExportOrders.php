<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Request;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\Response\ExportOrders as ExportOrdersResponse;

class ExportOrders extends RequestAbstract
{
    protected $apiAction = 'newOrder';

    protected $responseClass = ExportOrdersResponse::class;

    /** @var EntityRepository */
    protected $productRepository;

    /** @var EntityRepository */
    protected $wboArticlesRepository;

    /** @var Context */
    protected $context;

    public function __construct(
        WboConfig $wboConfig,
        string $apiAction = null,
        string $responseClass = null,
        string $urlClass = null,
        EntityRepository $productRepository,
        EntityRepository $wboArticlesRepository,
        Context $context
    ) {
        parent::__construct($wboConfig, $apiAction, $responseClass, $urlClass);
        $this->productRepository = $productRepository;
        $this->wboArticlesRepository = $wboArticlesRepository;
        $this->context = $context;
    }

    public function getSalutation(): string
    {
        return $this->get('anrede');
    }

    public function setSalutation(string $salutation): void
    {
        $this->set('anrede', $salutation);
    }

    public function getCompany(): string
    {
        return $this->get('firma');
    }

    public function setCompany(string $company): void
    {
        $this->set('firma', $company);
    }

    public function getFirstname(): string
    {
        return $this->get('name');
    }

    public function setFirstname(string $firstname): void
    {
        $this->set('name', $firstname);
    }

    public function getLastname(): string
    {
        return $this->get('nname');
    }

    public function setLastname(string $lastname): void
    {
        $this->set('nname', $lastname);
    }

    public function getStreet(): string
    {
        return $this->get('strasse');
    }

    public function setStreet(string $street): void
    {
        $this->set('strasse', $street);
    }

    public function getStreetnumber(): string
    {
        return $this->get('hnummer');
    }

    public function setStreetnumber(string $streetnumber): void
    {
        $this->set('hnummer', $streetnumber);
    }

    public function getZip(): string
    {
        return $this->get('plz');
    }

    public function setZip(string $zip): void
    {
        $this->set('plz', $zip);
    }

    public function getCity(): string
    {
        return $this->get('ort');
    }

    public function setCity(string $city): void
    {
        $this->set('ort', $city);
    }

    public function getCountry(): string
    {
        return $this->get('land');
    }

    public function setCountry(string $country): void
    {
        $this->set('land', $country);
    }

    public function getEmail(): string
    {
        return $this->get('email');
    }

    public function setEmail(string $email): void
    {
        $this->set('email', $email);
    }

    public function getPhone(): string
    {
        return $this->get('telefon');
    }

    public function setPhone(string $phone): void
    {
        $this->set('telefon', $phone);
    }

    public function getPayment(): string
    {
        return $this->get('zahlungsart');
    }

    public function getDeliveryCompany(): string
    {
        return $this->get('l_firma');
    }

    public function setDeliveryCompany(string $deliveryCompany): void
    {
        $this->set('l_firma', $deliveryCompany);
    }

    public function getDeliveryFirstname(): string
    {
        return $this->get('l_vorname');
    }

    public function setDeliveryFirstname(string $deliveryFirstname): void
    {
        $this->set('l_vorname', $deliveryFirstname);
    }

    public function getDeliveryLastname(): string
    {
        return $this->get('l_nachname');
    }

    public function setDeliveryLastname(string $deliveryLastname): void
    {
        $this->set('l_nachname', $deliveryLastname);
    }

    public function getDeliveryStreet(): string
    {
        return $this->get('l_strasse');
    }

    public function setDeliveryStreet(string $deliveryStreet): void
    {
        $this->set('l_strasse', $deliveryStreet);
    }

    public function getDeliveryZip(): string
    {
        return $this->get('l_plz');
    }

    public function setDeliveryZip(string $deliveryZip): void
    {
        $this->set('l_plz', $deliveryZip);
    }

    public function getDeliveryCity(): string
    {
        return $this->get('l_ort');
    }

    public function setDeliveryCity(string $deliveryCity): void
    {
        $this->set('l_ort', $deliveryCity);
    }

    public function getDeliveryCountry(): string
    {
        return $this->get('l_land');
    }

    public function setDeliveryCountry(string $deliveryCountry): void
    {
        $this->set('l_land', $deliveryCountry);
    }

    public function getDeliveryStreetNumber(): string
    {
        return $this->get('l_hnummer');
    }

    public function setDeliveryStreetNumber(string $deliveryStreetNumber): void
    {
        $this->set('l_hnummer', $deliveryStreetNumber);
    }

    public function getBankaccountnumber(): string
    {
        return $this->get('kto');
    }

    public function setBankaccountnumber(string $bankaccountnumber): void
    {
        $this->set('kto', $bankaccountnumber);
    }

    public function getBankaccountowner(): string
    {
        return $this->get('ktoInh');
    }

    public function setBankaccountowner(string $bankaccountowner): void
    {
        $this->set('ktoInh', $bankaccountowner);
    }

    public function getBankcode(): string
    {
        return $this->get('blz');
    }

    public function setBankcode(string $bankcode): void
    {
        $this->set('blz', $bankcode);
    }

    public function getBankaccountiban(): string
    {
        return $this->get('iban');
    }

    public function setBankaccountiban(string $bankaccountiban): void
    {
        $this->set('iban', $bankaccountiban);
    }

    public function getBankaccountbic(): string
    {
        return $this->get('bic');
    }

    public function setBankaccountbic(string $bankaccountbic): void
    {
        $this->set('bic', $bankaccountbic);
    }

    public function getVouchercode(): string
    {
        return $this->get('gutscheincode');
    }

    public function setVouchercode(string $vouchercode): void
    {
        $this->set('gutscheincode', $vouchercode);
    }

    public function getFee(): string
    {
        return $this->get('gebuehr');
    }

    public function setFee(string $fee): void
    {
        $this->set('gebuehr', $fee);
    }

    public function getPaymenttype(): string
    {
        return $this->get('zahlungsart');
    }

    public function setPaymenttype(string $paymenttype): void
    {
        $this->set('zahlungsart', $paymenttype);
        $this->set('zahlung', $paymenttype);
    }

    public function getNotice(): string
    {
        return $this->get('referenz');
    }

    public function setNotice(string $notice): void
    {
        $this->set('referenz', $notice);
    }

    public function getDeliverycosts(): string
    {
        return $this->get('versandkosten');
    }

    public function setDeliverycosts(string $deliverycosts): void
    {
        $this->set('versandkosten', $deliverycosts);
    }

    public function getPaypalTransactionCode(): string
    {
        $this->get('woo_transaktions_code');
    }

    public function setPaypalTransactionCode(string $code): void
    {
        $this->set('woo_tansaktions_code', $code);
    }

    public function getNoEmail(): bool
    {
        return is_string($this->get('keine_mail'));
    }

    public function setNoEmail(): void
    {
        $this->set('keine_mail', 'keine_mail');
    }

    public function unsetNoEmail(): void
    {
        $this->set('keine_mail', null);
    }

    public function setShippingId(int $shippingId): void
    {
        $this->set('id_lieferart', $shippingId);
    }

    public function getShippingId(): int
    {
        return (int) $this->get('id_lieferart');
    }

    public function setTransactionCode(string $transactionCode): void
    {
        $this->set('woo_transaktions_code', $transactionCode);
    }

    public function getTransactionCode(): string
    {
        return $this->get('woo_transaktion_code');
    }

    public function setOrderItems(array $orderItems): void
    {
        $i = 1;
        $items = [];
        $discountItems = [];
        /** @var OrderLineItemEntity $orderItem */
        foreach ($orderItems AS $orderItem)
        {
            if ($this->isArticleDiscount($orderItem)) {
                $discountItems[] = [
                    'artikel_sonderpreis' => $orderItem->getTotalPrice(),
                    'wein_anzahl' => $orderItem->getQuantity(),
                    'wein_id' => 'rabatt'
                ];
                continue;
            }

            $productNumber = $orderItem->getPayload()['productNumber'] ?? null;
            if ($orderItem->getProductId()) {
                $product = $this->productRepository->search(
                    new Criteria([$orderItem->getProductId()]),
                    $this->context
                )->first();
                if ($product) {
                    $productNumber = $product->getProductNumber();
                }
            }

            if (null === $productNumber) {
                continue;
            }

            $wboArticle = $this->wboArticlesRepository->search((new Criteria())->addSorting(new FieldSorting('productNumber', FieldSorting::ASCENDING))
                ->addFilter(new EqualsFilter('productNumber', $productNumber)), Context::createDefaultContext())->last();

            $quantity = $orderItem->getQuantity();

            if ($orderItem->getUnitPrice() != $wboArticle->getPrice()) {
                $items['artikel_sonderpreis' . $i] = $orderItem->getUnitPrice();
            }
            $items['wein_anzahl' . $i] = $quantity;
            $items['wein_id' . $i] = $wboArticle->getArticleNumber();

            $i++;
        }

        foreach($discountItems as $item) {
            $items['artikel_sonderpreis' . $i] = $item['artikel_sonderpreis'];
            $items['wein_anzahl' . $i] = $item['wein_anzahl'];
            $items['wein_id' . $i] = $item['wein_id'];
            $i++;
        }

        $this->set('positionen', $i-1);

        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }

    protected function isArticleDiscount(OrderLineItemEntity $orderLineItem): bool
    {
        return $orderLineItem->getType() == LineItem::PROMOTION_LINE_ITEM_TYPE;
    }

    protected function getProductById(string $productId): ProductEntity
    {
        $search = $this->productRepository->search(new Criteria([$productId]), Context::createDefaultContext());
        $product = $search->get($productId);
        if (!($product instanceof ProductEntity)) {
            throw new \RuntimeException('could not fetch product');
        }
        return $product;
    }
}
