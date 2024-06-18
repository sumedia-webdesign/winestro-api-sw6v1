<?php declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Delivery;

use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryPosition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Entity\WboArticlesEntity;
use Sumedia\Wbo\Service\Context;
use Symfony\Component\DependencyInjection\Container;

class DeliveryQuantityFetcher
{
    protected EntityRepository $productRepository;
    protected EntityRepository $wboArticleRepository;
    protected WboConfig $wboConfig;
    protected Context $context;
    public static DeliveryQuantityFetcher $instance;
    public static ?Container $container;

    public function __construct(
        EntityRepository $productRepository,
        EntityRepository $wboArticleRepository,
        WboConfig $wboConfig,
        Context $context
    ) {
        $this->productRepository = $productRepository;
        $this->wboArticleRepository = $wboArticleRepository;
        $this->wboConfig = $wboConfig;
        $this->context = $context;
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = self::$container->get('Sumedia\Wbo\Service\Wbo\Delivery\DeliveryQuantityFetcher');
        }
        return self::$instance;
    }

    public function getQuantity(DeliveryPosition $deliveryPosition)
    {
        $countBottlesActivated = $this->wboConfig->get(WboConfig::COUNT_BOTTLES);
        if (!$countBottlesActivated) {
            return $deliveryPosition->getQuantity();
        }

        $lineItem = $deliveryPosition->getLineItem();
        $referenceId = $lineItem->getReferencedId();
        /** @var ProductEntity $product */
        $product = $this->productRepository->search(
            new Criteria([$referenceId]),
            $this->context
        )->first();
        $productNumber = $product->getProductNumber();

        /** @var WboArticlesEntity $wboArticle */
        $wboArticle = $this->wboArticleRepository->search(
            (new Criteria)->addFilter(
                new EqualsFilter('productNumber', $productNumber)
            ),
            $this->context
        )->first();

        if ($wboArticle) {
            return (int) $wboArticle->getBottles() * $deliveryPosition->getQuantity() ?: $deliveryPosition->getQuantity();
        }
        return $deliveryPosition->getQuantity();
    }
}