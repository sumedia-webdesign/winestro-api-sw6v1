<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Service\Wbo\Command;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Tax\TaxEntity;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\ArticleNumberParser;
use Sumedia\Wbo\Service\Wbo\Command\Exception\NoRelatedProductIdsException;
use Sumedia\Wbo\Service\Wbo\Command\SetArticles\DeactivateOutOfStockBundles;
use Sumedia\Wbo\Service\Wbo\Command\SetArticles\FilterCollection;
use Sumedia\Wbo\Service\Wbo\ConnectorInterface;
use Sumedia\Wbo\Service\Wbo\Converter\ConvertArticleToTableData;
use Sumedia\Wbo\Service\Wbo\Request\RequestInterface;
use Sumedia\Wbo\Service\Wbo\Response\GetArticle\Article;
use Sumedia\Wbo\Service\Wbo\Response\GetArticles;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetArticles extends AbstractCommand implements CommandInterface
{
    protected EntityRepository $wboArticlesRepository;
    protected EntityRepository $wboProductsRepository;
    protected EntityRepository $productRepository;
    protected EntityRepository $taxRepository;
    protected RequestInterface $getArticles;
    protected WboConfig $wboConfig;
    protected ConnectorInterface $connector;
    protected ArticleNumberParser $articleNumberParser;
    protected FilterCollection $filterCollection;
    protected DeactivateOutOfStockBundles $deactivateOutOfStockBundles;
    protected Context $context;
    protected array $parentProductIdsTmp = [];

    public function __construct(
        LoggerInterface $debugLogger,
        LoggerInterface $errorLogger,
        WboConfig $wboConfig,
        EntityRepository $wboArticlesRepository,
        EntityRepository $wboProductsRepository,
        EntityRepository $productRepository,
        EntityRepository $taxRepository,
        RequestInterface $getArticles,
        ConnectorInterface $connector,
        ArticleNumberParser $articleNumberParser,
        FilterCollection $filterCollection,
        DeactivateOutOfStockBundles $deactivateOutOfStockBundles,
        Context $context
    ) {
        parent::__construct($errorLogger, $debugLogger, $wboConfig);
        $this->wboArticlesRepository = $wboArticlesRepository;
        $this->wboProductsRepository = $wboProductsRepository;
        $this->productRepository = $productRepository;
        $this->taxRepository = $taxRepository;
        $this->getArticles = $getArticles;
        $this->wboConfig = $wboConfig;
        $this->connector = $connector;
        $this->articleNumberParser = $articleNumberParser;
        $this->filterCollection = $filterCollection;
        $this->deactivateOutOfStockBundles = $deactivateOutOfStockBundles;
        $this->context = $context;
    }

    public function execute(InputInterface $input = null, OutputInterface $output = null): void
    {
        $this->debug('wbo: update articles');

        if (!$this->isActive()) {
            $this->debug('wbo is not active');
            return;
        }

        try {
            /** @var GetArticles $response */
            $response = $this->connector->execute($this->getArticles);
            if (!$response->isSuccessful()) {
                $this->debug('Fetching articles not successfull');
                return;
            }

            $articles = $response->getArticles();
            $this->fetchProductNumbers($articles);
            $this->removeOutdatedOnProductNumberAndSetVersionId($articles);

            if ($this->wboConfig->get(WboConfig::PRODUCT_DATA_ACTIVATED)) {
                $articlesProductData = $this->setArticles($articles);
                $this->writeReferences($articlesProductData);
            }
            $this->setArticlesToTable($articles);
            $this->deactivateOutOfStockBundles();
        } catch(\Throwable $e) {
            $this->logException($e);
            throw $e;
        }
    }

    protected function removeOutdatedOnProductNumberAndSetVersionId(array &$articles): void
    {
        $productNumbers = array_map(fn ($article) => $article->getProductNumber(), $articles);
        $duplicatedProductNumbers = array_filter(array_count_values($productNumbers), fn ($productNumber) => $productNumber > 1 ? $productNumber : false);

        $articles = array_filter($articles, function($article) use(&$duplicatedProductNumbers) {
            if (!isset($duplicatedProductNumbers[$article->getProductNumber()])) {
                return true;
            }

            $parentProductIds = $this->getParentProductIds($article->getProductNumber());
            if ($duplicatedProductNumbers[$article->getProductNumber()] > 1) {
                $duplicatedProductNumbers[$article->getProductNumber()]--;
                return false;
            }
            if ($parentProductIds) {
                $article->setParentId($parentProductIds['parentId']);
                $article->setParentVersionId($parentProductIds['parentVersionId']);
            }
            return true;
        });
    }

    protected function getParentProductIds(string $productNumber): ?array
    {
        if (!isset($this->parentProductIdsTmp[$productNumber])) {
            $product = $this->productRepository->search((new Criteria())
                ->addFilter(new EqualsFilter('productNumber', $productNumber))
                ->addSorting(
                    new FieldSorting('createdAt', FieldSorting::DESCENDING)),
                    $this->context
                )->first();
            if (!$product) {
                $this->parentProductIdsTmp[$productNumber] = null;
            }
            if (!$product->getParentId()) {
                $this->parentProductIdsTmp[$productNumber] = [
                    'parentId' => $product->getId(),
                    'parentVersionId' => $product->getVersionId()
                ];
            } else {
                $this->parentProductIdsTmp[$productNumber] = [
                    'parentId' => $product->getParentId(),
                    'parentVersionId' => $product->getParentVersionId()
                ];
            }
        }
        return $this->parentProductIdsTmp[$productNumber];
    }

    protected function fetchProductNumbers(array $articles): void
    {
        /** @var Article $article */
        foreach ($articles as $article) {

            $productNumber = $article->getArticleNumber();
            if ($article->isWine()) {
                if (!$this->articleNumberParser->isValidArticleNumberFormat()) {
                    $this->debug('invalid article number format, please check configuration - can\'t import wine');
                    continue;
                }
                $article->setArticleNumberFormat($this->articleNumberParser->getFormat());
                $productNumber = $this->articleNumberParser->parseArticleNumber($article->getArticleNumber());
            }
            $article->setProductNumber($productNumber);
        }
    }

    protected function setArticles(array $articles): array
    {
        $articlesProductData = [];
        /** @var Article $article */
        foreach ($articles as $article) {

            if (!$this->isValidArticle($article)) {
                $this->debug('Get invalid article ' . $article->getProductNumber());
                continue;
            }

            $productNumber = $article->getProductNumber();
            $product = $this->getProduct($productNumber);
            $productId = $product ? $product->getId() : Uuid::randomHex();
            $taxId = $this->getTaxId($article->getTaxPercent());

            $productData = [
                'id' => $productId,
                'productNumber' => $productNumber,
                'name' => $article->getName(),
                'isCloseout' => true,
                'tax' => ['id' => $taxId],
                'weight' => $article->getWeight(),
                'purchasePrice' => 0,
                'description' => $article->getShopnotice(),
                'shippingFree' => $article->isFreeShipping(),
                'packUnit' => $article->getUnit()
            ];

            $this->filterCollection->execute($article, $productData);

            if ($productData['active'] !== true) {
                $this->debug('Could not activate product ' . $productData['productNumber']);
            }

            $articlesProductData[] = $productData;
        }

        $this->productRepository->upsert($articlesProductData, $this->context);

        $skipArticleNumbers = array_map(function($articleProductData) {
            return $articleProductData['productNumber'];
        }, $articlesProductData);

        $this->updateOutdatedArticleNumbers($skipArticleNumbers);

        return $articlesProductData;
    }

    protected function writeReferences(array $articlesProductData): void
    {
        $products = [];
        foreach ($articlesProductData as $articleProductData) {
            $productId = $articleProductData['id'];
            $articleId = $this->getArticleIdFromProductNumber($articleProductData['productNumber']);
            if (null !== $articleId) {
                $products[$productId] = $articleId;
            }
        }
        if (count($products)) {
            $data = [];
            foreach ($products as $productId => $articleId) {
                $data[] = [
                    'productId' => $productId,
                    'wboArticleId' => $articleId
                ];
            }
            $this->wboProductsRepository->upsert($data, $this->context);
        }
    }

    protected function getArticleIdFromProductNumber(string $productNumber): ?string
    {
        $search = $this->wboArticlesRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('productNumber', $productNumber)), $this->context
        );
        if ($search->count()) {
            return $search->first()->getId();
        }
        return null;
    }

    protected function isValidArticle(Article $article): bool
    {
        if (!$article->getName()) {
            return false;
        }
        return true;
    }

    protected function getProduct(string $productNumber): ?ProductEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));

        return $this->productRepository->search($criteria, $this->context)->first();
    }

    protected function getTaxId(float $taxRate) : string
    {
        $taxIds = [];
        $taxId = $this->wboConfig->get(WboConfig::TAX_ID);
        if ($taxId) {
            $taxIds[] = $taxId;
        }
        $taxId = $this->wboConfig->get(WboConfig::REDUCED_TAX_ID);
        if ($taxId) {
            $taxIds[] = $taxId;
        }

        $taxes = $this->taxRepository->search((new Criteria($taxIds)), $this->context)->getElements();
        /** @var TaxEntity $tax */
        foreach ($taxes as $tax) {
            if ($tax->getTaxRate() == $taxRate) {
                return $tax->getId();
            }
        }
        throw new \RuntimeException('could not fetch tax id');
    }

    protected function setArticlesToTable(array $articles): void
    {
        if (!count($articles)) {
            return;
        }

        $this->cleanUpArticlesTable($articles);

        $criteria = new Criteria();
        $results = $this->wboArticlesRepository->search($criteria, $this->context);
        $articleIds = [];
        $articleCreatedAt = [];
        foreach ($results as $result){
            $articleIds[$result->getArticleNumber()] = $result->getId();
            $articleCreatedAt[$result->getArticleNumber()] = $result->getCreatedAt()->format('Y-m-d H:i:s');
        }

        foreach ($articles as $article) {
            if ($this->isValidArticle($article)) {
                $id = $articleIds[$article->getArticleNumber()] ?? Uuid::randomHex();
                $createdAt = $articleCreatedAt[$article->getArticleNumber()] ?? date('Y-m-d H:i:s');;
                $data = (new ConvertArticleToTableData())->convert($article, $id, $createdAt);
                $this->wboArticlesRepository->upsert([$data], $this->context);
            }
        }
    }

    protected function cleanUpArticlesTable($articles): void
    {
        $notToDeleteIds = array_map(function($article){
            return $article->getArticleNumber();
        }, $articles);

        $criteria = new Criteria();
        $criteria->addFilter(new NotFilter(NotFilter::CONNECTION_AND, [new EqualsAnyFilter('articleNumber', $notToDeleteIds)]));
        $results = $this->wboArticlesRepository->search($criteria, $this->context);

        $deleteIds = [];
        foreach ($results as $result) {
            $deleteIds[] = ['id' => $result->getId()];
        }
        if (count($deleteIds)) {
            $this->wboArticlesRepository->delete($deleteIds, $this->context);
        }
    }

    protected function updateOutdatedArticleNumbers(array $skipArticleNumbers): void
    {
        if (empty($skipArticleNumbers)) {
            $this->log('something went wrong, there are no articles to skip, please check communication');
            return;
        }

        $outdatedArticleNumbers = [];

        $outdatedWboArticlesCriteria = new Criteria();
        $outdatedWboArticlesCriteria->addFilter(
            new NotFilter(NotFilter::CONNECTION_AND, [
                new EqualsAnyFilter('articleNumber', $skipArticleNumbers)
            ])
        );
        $outdatedWboArticles = $this->wboArticlesRepository->search($outdatedWboArticlesCriteria, $this->context);
        foreach ($outdatedWboArticles as $outdatedWboArticle) {
            $outdatedArticleNumbers[] = $outdatedWboArticle->getArticleNumber();
        }

        $outdatedProductsCriteria = new Criteria();
        $outdatedProductsCriteria->addFilter(
            new EqualsAnyFilter('productNumber', $outdatedArticleNumbers)
        );
        $outdatedProducts = $this->productRepository->search($outdatedProductsCriteria, $this->context);

        $update = [];
        foreach ($outdatedProducts as $outdatedProduct) {
            $this->debug('Going to deactivate ' . $outdatedProduct->getProductNumber());
            $update[] = ['id' => $outdatedProduct->getId(), 'active' => false];
        }

        if (count($update)) {
            $this->productRepository->update($update, $this->context);
        }
    }

    protected function deactivateOutOfStockBundles()
    {
        $this->deactivateOutOfStockBundles->checkBundles();
    }
}
