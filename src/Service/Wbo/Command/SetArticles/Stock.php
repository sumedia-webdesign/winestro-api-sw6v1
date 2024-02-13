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
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\ConnectorInterface;
use Sumedia\Wbo\Service\Wbo\Request\GetBestand;
use Sumedia\Wbo\Service\Wbo\Response\GetArticle\Article;

class Stock
{
    /** @var EntityRepository */
    protected $wboArticlesRepository;

    /** @var EntityRepository */
    protected $productRepository;

    /** @var GetBestand */
    protected $getBestand;

    /** @var ConnectorInterface */
    protected $connector;

    /** @var WboConfig */
    protected $wboConfig;

    /** @var Context */
    protected $context;

    public function __construct(
        EntityRepository $wboArticlesRepository,
        EntityRepository $productRepository,
        GetBestand $getBestand,
        ConnectorInterface $connector,
        WboConfig $wboConfig,
        Context $context
    ) {
        $this->wboArticlesRepository = $wboArticlesRepository;
        $this->productRepository = $productRepository;
        $this->getBestand = $getBestand;
        $this->connector = $connector;
        $this->wboConfig = $wboConfig;
        $this->context = $context;
    }

    public function execute(Article $article, array &$productData): void
    {
        $stockDateTimestamp = $this->getStockDateTimestamp($article);
        $productData['stock'] = $this->getStockFromProduct($article);

        if ($this->isStockToUpdate($stockDateTimestamp)) {
            $stockDate = date('d-m-Y H:i:s');
            $productData['stock'] = $this->getStock($article);
            $productData['stockDate'] = $stockDate;
            $article->setStockDate($stockDate);
        }

        $article->setStock($productData['stock']);
    }

    protected function getStockDateTimestamp(Article $article): int
    {
        $stockDate = null;
        $criteria = (new Criteria())->addFilter(new EqualsFilter('productNumber', $article->getProductNumber()));
        $search = $this->wboArticlesRepository->search($criteria, $this->context);
        if ($search->count()) {
            $wboStockDate = $search->first()->getStockDate();
            if (substr($wboStockDate, 0, 4) !== '3000') { // let the stock_date be null
                $stockDate = new \DateTimeImmutable($search->first()->getStockDate());
            }
        }
        return $stockDate ? $stockDate->getTimestamp() : 0;
    }

    protected function isStockToUpdate(int $stockDateTimestamp): bool
    {
        if ($stockDateTimestamp < time() - 60 * 60 * 6) {
            return true;
        }
        return false;
    }

    protected function getStockFromProduct(Article $article): int
    {
        $criteria = (new Criteria())->addFilter(new EqualsFilter('productNumber', $article->getProductNumber()));
        $search = $this->productRepository->search($criteria, $this->context);
        if ($search->count()) {
            return $search->first()->getStock();
        }
        return $this->getStockFromTable($article);
    }

    protected function getStock(Article $article): int
    {
        if (!$this->wboConfig->get(WboConfig::STOCK_ENABLED)) {
            return $this->wboConfig->get(WboConfig::STOCK_DEFAULT) ?: 1;
        }

        $stock = $this->getStockFromProduct($article);

        $this->getBestand->setArticleNr($article->getArticleNumber());
        $response = $this->connector->execute($this->getBestand);
        if ($response->isSuccessful()) {
            $stock = $response->getStock();
        }

        // minimal required stock items
        $stock -= (int)$this->wboConfig->get(WboConfig::STOCK_MINIMAL);

        return 0 > $stock ? 0 : $stock;
    }

    protected function getStockFromTable(Article $article): int
    {
        $criteria = (new Criteria())->addFilter(new EqualsFilter('productNumber', $article->getProductNumber()));
        $search = $this->wboArticlesRepository->search($criteria, $this->context);
        if (!$search->count()) {
            return 0;
        }
        return $search->first()->getStock();
    }
}
