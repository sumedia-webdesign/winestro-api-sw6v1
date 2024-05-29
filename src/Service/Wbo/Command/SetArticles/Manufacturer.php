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

class Manufacturer
{
    protected WboConfig $wboConfig;
    protected EntityRepository $manufacturer;
    protected Context $context;

    public function __construct(
        EntityRepository $manufacturer,
        WboConfig $wboConfig,
        Context $context
    ) {
        $this->manufacturer = $manufacturer;
        $this->wboConfig = $wboConfig;
        $this->context = $context;
    }

    public function execute(Article $article, array &$productData)
    {
        if (!$this->wboConfig->get('manufacturerEnabled')) {
            return;
        }

        $manufacturerId = $this->getManufacturerId($article->getManufacturer());

        if (!$manufacturerId) {
            return;
        }

        $productData['manufacturer'] = ['id' => $manufacturerId];
    }

    protected function getManufacturerId(string $manufacturer = '')
    {
        if (empty($manufacturer)) {
            return $this->wboConfig->get('manufacturerId');
        }

        $search = $this->manufacturer->search((new Criteria())->addFilter(new EqualsFilter('name', $manufacturer)), $this->context);
        if (!$search->count()) {
            $id = Uuid::randomHex();
            $this->manufacturer->create([[
                'id' => $id,
                'name' => $manufacturer,

            ]], $this->context);
            return $id;
        }

        return $search->first()->getId();
    }
}
