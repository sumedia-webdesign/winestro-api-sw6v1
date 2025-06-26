<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Command\SetArticles;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Currency\CurrencyEntity;

class PriceArrayBuilder
{
    protected EntityRepository $currencyRepository;
    protected Context $context;

    public function __construct(
        EntityRepository $currencyRepository,
        Context $context
    ) {
        $this->currencyRepository = $currencyRepository;
        $this->context = $context;
    }

    public function build(?ProductEntity $product, float $wboPrice, float $wboTaxRate) : array
    {
        $currencyEntity = $this->getCurrencyEntity('EUR');

        if(null === $product) {
            return [
                'currencyId' => $currencyEntity->getId(),
                'gross' => $wboPrice,
                'net' => $wboPrice / (1 + $wboTaxRate / 100),
                'linked' => false
            ];
        }

        $price = $product->getPrice();
        /** @var Price $priceElement */
        $priceElement = current($price->getElements());
        $listPrice = $priceElement->getListPrice();

        if ($listPrice) {
            return [
                'currencyId' => $currencyEntity->getId(),
                'gross' => $priceElement->getGross(),
                'net' => $wboPrice / (1 + $wboTaxRate / 100),
                'linked' => false,
                'listPrice' => [
                    "net" => $wboPrice / (1 + $wboTaxRate / 100),
                    "gross" => $wboPrice,
                    "linked" => true,
                    "currencyId" => $currencyEntity->getId()
                ],
                'percentage' => [
                    'net' => 0,
                    'gross' => $priceElement->getGross() / $wboPrice * 100
                ]
            ];
        } else {
            return [
                'currencyId' => $currencyEntity->getId(),
                'gross' => $wboPrice,
                'net' => $wboPrice / (1 + $wboTaxRate / 100),
                'linked' => false
            ];
        }
    }

    protected function getCurrencyEntity(string $currencyCode) : CurrencyEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('isoCode', $currencyCode));

        $currencyCollection = $this->currencyRepository->search($criteria, $this->context);
        if ($currencyCollection->count()) {
            return $currencyCollection->first();
        }

        throw new \RuntimeException('could not fetch currency entity');
    }
}
