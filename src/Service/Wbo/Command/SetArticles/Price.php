<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Command\SetArticles;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Currency\CurrencyEntity;
use Sumedia\Wbo\Service\Wbo\Response\GetArticle\Article;

class Price
{
    protected EntityRepository $currencyRepository;
    protected EntityRepository $productRepository;
    protected Context $context;

    public function __construct(
        EntityRepository $currencyRepository,
        EntityRepository $productRepository,
        Context $context
    ) {
        $this->currencyRepository = $currencyRepository;
        $this->productRepository = $productRepository;
        $this->context = $context;
    }

    public function execute(Article $article, array &$productData): void
    {
        $currencyEntity = $this->getCurrencyEntity('EUR');
        $product = $this->getProductByArticle($article);

        if(null === $product) {
            $productData['price'] = [[
                'currencyId' => $currencyEntity->getId(),
                'gross' => $article->getPrice(),
                'net' => $article->getPrice() / (1 + $article->getTaxPercent() / 100),
                'linked' => false
            ]];
            return;
        }

        $price = $product->getPrice();
        /** @var \Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price $priceElement */
        $priceElement = current($price->getElements());
        $listPrice = $priceElement->getListPrice();

        if ($listPrice) {
            $productData['price'] = [[
                'currencyId' => $currencyEntity->getId(),
                'gross' => $priceElement->getGross(),
                'net' => $priceElement->getGross() / (1 + $article->getTaxPercent() / 100),
                'linked' => false,
                'listPrice' => [
                    "net" => $article->getPrice() / (1 + $article->getTaxPercent() / 100),
                    "gross" => $article->getPrice(),
                    "linked" => true,
                    "currencyId" => $currencyEntity->getId()
                ],
                'percentage' => [
                    'net' => 0,
                    'gross' => $priceElement->getGross() / $article->getPrice() * 100
                ]
            ]];
        } else {
            $productData['price'] = [[
                'currencyId' => $currencyEntity->getId(),
                'gross' => $article->getPrice(),
                'net' => $article->getPrice() / (1 + $article->getTaxPercent() / 100),
                'linked' => false
            ]];
        }
    }

    protected function getProductByArticle(Article $article): ?ProductEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $article->getProductNumber()));

        return $this->productRepository->search($criteria, $this->context)->first();
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
