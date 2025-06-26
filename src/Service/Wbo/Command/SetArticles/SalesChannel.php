<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Command\SetArticles;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\Response\GetArticle\Article;

class SalesChannel
{
    const VISIBILITY_DEEPLINK = 10;
    const VISIBILITY_SEARCH = 20;
    const VISIBILITY_ALL = 30;

    protected WboConfig $wboConfig;
    protected EntityRepository $productVisibilityRepository;
    protected Context $context;

    public function __construct(
        WboConfig $wboConfig,
        EntityRepository $productVisibilityRepository,
        Context $context
    ) {
        $this->wboConfig = $wboConfig;
        $this->productVisibilityRepository = $productVisibilityRepository;
        $this->context = $context;
    }

    public function execute(Article $article, array &$productData): void
    {
        $saleChannelIds = $this->wboConfig->get(WboConfig::SALE_CHANNEL_IDS);

        if (!count($saleChannelIds)) {
            return;
        }

        $visibilities = [];
        foreach ($saleChannelIds as $saleChannelId) {
            $visibilities[] = [
                'id' => Uuid::randomHex(),
                'salesChannelId' => $saleChannelId,
                'visibility' => self::VISIBILITY_ALL
            ];
        }

        $productVisibilities = $this->productVisibilityRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('productId', $productData['id'])),
            $this->context
        );
        if ($productVisibilities->count()) {
            foreach ($productVisibilities as $productVisibility) {
                $salesChannelId = $productVisibility->getSalesChannelId();
                foreach ($visibilities as &$visibility) {
                    if ($visibility['salesChannelId'] == $salesChannelId) {
                        $visibility['id'] = $productVisibility->getId();
                    }
                }
            }
        }

        $productData['visibilities'] = $visibilities;
    }
}
