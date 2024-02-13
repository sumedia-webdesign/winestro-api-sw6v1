<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Service\Wbo\Command\SetArticles;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\ArticleNumberParser;

class DeactivateOutOfStockBundles
{
    /** @var WboConfig */
    protected $wboConfig;

    /** @var EntityRepository */
    protected $wboArticle;

    /** @var EntityRepository */
    protected $product;

    /** @var Context */
    protected $context;

    public function __construct(
        WboConfig $wboConfig,
        EntityRepository $wboArticle,
        EntityRepository $product,
        Context $context
    ) {
        $this->wboConfig = $wboConfig;
        $this->wboArticle = $wboArticle;
        $this->product = $product;
        $this->context = $context;
    }

    public function checkBundles(): void
    {
        $bundleProducts = $this->getBundleProducts();
        $this->fetchProducts($bundleProducts);

        foreach ($bundleProducts as $bundleProduct) {
            foreach ($bundleProduct['products'] as $product) {
                if ($product->getAvailableStock() < 5) {
                    $data = [
                        'id' => $bundleProduct['product']->getId(),
                        'active' => false
                    ];
                    $this->product->update([$data], $this->context);
                    break;
                }
            }
        }
    }

    protected function getBundleProducts(): array
    {
        $products = [];
        $criteria = (new Criteria())->addFilter(new ContainsFilter('bundle', '{'));
        $bundleProducts = $this->wboArticle->search($criteria, $this->context);
        if (!$bundleProducts->count()) {
            return [];
        }
        foreach ($bundleProducts as $bundleProduct) {
            $productNumber = $bundleProduct->getProductNumber();
            $criteria = (new Criteria())->addFilter(new EqualsFilter('productNumber', $productNumber));
            $product = $this->product->search($criteria, $this->context)->first();
            if ($product) {
                $products[] = [
                    'wbo_article' => $bundleProduct,
                    'product' => $product
                ];
            }
        }
        return $products;
    }

    protected function fetchProducts(array &$bundleProducts): void
    {
        foreach ($bundleProducts as &$bundleProduct) {
            $productIds = [];
            $articleIds = array_keys($bundleProduct['wbo_article']->getBundle());
            $articleNumberParser = new ArticleNumberParser($this->wboConfig);
            foreach ($articleIds as $articleId) {
                $productIds[] = $articleNumberParser->parseArticleNumber($articleId);
            }

            $criteria = (new Criteria())->addFilter(new EqualsAnyFilter('productNumber', $productIds));
            $bundleProduct['products'] = $this->product->search($criteria, $this->context);
        }
    }
}
