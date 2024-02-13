<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Command\SetArticles;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\Response\GetArticle\Article;

class Taste
{
    /** @var EntityRepository */
    protected $propertyGroupOptionRepository;

    /** @var EntityRepository */
    protected $propertyGroupRepository;

    /** @var WboConfig */
    protected $wboConfig;

    /** @var LoggerInterface */
    protected $logger;

    /** @var Context */
    protected $context;

    public function __construct(
        EntityRepository $propertyGroupOptionRepository,
        EntityRepository $propertyGroupRepository,
        WboConfig $wboConfig,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->propertyGroupOptionRepository = $propertyGroupOptionRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->wboConfig = $wboConfig;
        $this->logger = $logger;
        $this->context = $context;
    }

    public function execute(Article $article, array &$productData): void
    {
        if (!$article->isWine()) {
            return;
        }

        if (!$article->getWineDetails()->getTaste()) {
            return;
        }

        $tasteProperty = $this->getProperty();
        if (!$tasteProperty) {
            $this->logger->debug('Could not set taste property, there is no valid property configured, please configure property id for attribution taste');
            return;
        }

        $tasteOption = $this->getOption($tasteProperty->getId(), $article->getWineDetails()->getTaste());
        if (!$tasteOption) {
            $tasteOption = $this->createOption($tasteProperty->getId(), $article->getWineDetails()->getTaste());
        }
        if (!$tasteOption) {
            return;
        }

        $productData['properties'][] = [
            'id' => $tasteOption->getId(),
            'name' => $article->getWineDetails()->getTaste(),
            'group' => ['id' => $tasteProperty->getId()]
        ];
    }

    protected function getProperty() : ?PropertyGroupEntity
    {
        $propertyId = $this->wboConfig->get(WboConfig::PROPERTY_TASTE_ID);
        if (!$propertyId) {
            return null;
        }

        $collection = $this->propertyGroupRepository->search(new Criteria([$propertyId]), $this->context);
        return !$collection->count() ? null : $collection->first();
    }

    protected function getOption(string $propertyId, string $value): ?PropertyGroupOptionEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $value));

        $collection = $this->propertyGroupOptionRepository
            ->search($criteria, $this->context);
        return !$collection->count() ? null : $collection->first();
    }

    protected function createOption(string $propertyId, string $value): ?PropertyGroupOptionEntity
    {
        $canAddNewOption = $this->wboConfig->get(WboConfig::PROPERTY_TASTE_AUTO_ADD);
        if (!$canAddNewOption) {
            $this->logger->debug('could not create new taste option, automaticly add is disabled');
            return null;
        }

        $id = Uuid::randomHex();
        $this->propertyGroupOptionRepository->create([
            [
                'id' => $id,
                'groupId' => $propertyId,
                'name' => $value,
                'createdAt' => date('Y-m-d H:i:s')
            ]
        ], $this->context);

        $collection = $this->propertyGroupOptionRepository->search(
            new Criteria([$id]),
            $this->context
        );
        if (!$collection->count()) {
            $this->logger->debug('could not create new taste option');
            return null;
        }

        return $collection->first();
    }
}