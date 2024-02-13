<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Command\SetArticles;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\Response\GetArticle\Article;

class SetCategories
{
    /** @var WboConfig */
    protected $wboConfig;

    /** @var EntityRepository */
    protected $categoryTranslationRepository;

    /** @var EntityRepository */
    protected $productCategoryRepository;

    /** @var Context */
    protected $context;

    public function __construct(
        WboConfig $wboConfig,
        EntityRepository $categoryTranslationRepository,
        EntityRepository $productCategoryRepository,
        Context $context
    ) {
        $this->wboConfig = $wboConfig;
        $this->categoryTranslationRepository = $categoryTranslationRepository;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->context = $context;
    }

    public function execute(Article $article, array &$productData): void
    {
        if (!$this->wboConfig->get(WboConfig::SET_CATEGORIES_ACTIVATED)) {
            return;
        }

        if (!count($article->getWareGroups())){
            return;
        }

        $collection = $this->categoryTranslationRepository->search(
            (new Criteria())->addFilter(new EqualsAnyFilter('name', $article->getWareGroups()))
        , $this->context);
        if (!$collection->count()) {
            return;
        }

        $productData['categories'] = [];
        foreach ($collection as $categoryTranslation) {
            $productData['categories'][] = [
                'id' => $categoryTranslation->getCategoryId()
            ];
        }
    }
}
