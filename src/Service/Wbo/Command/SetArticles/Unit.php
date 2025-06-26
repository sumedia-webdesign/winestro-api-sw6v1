<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Command\SetArticles;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Unit\UnitEntity;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\Response\GetArticle\Article;

class Unit
{
    protected EntityRepository $unitRepository;
    protected WboConfig $wboConfig;
    protected LoggerInterface $logger;
    protected Context $context;

    public function __construct(
        EntityRepository $unitRepository,
        WboConfig $wboConfig,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->unitRepository = $unitRepository;
        $this->wboConfig = $wboConfig;
        $this->logger = $logger;
        $this->context = $context;
    }

    public function execute(Article $article, array &$productData): void
    {
        if (!$article->isWine()) {
            return;
        }

        if (!$article->getLitre()) {
            return;
        }

        switch($article->getUnit()) {
            case 'Flasche':
                $unit = $this->getUnit();
                break;
            default:
                $unit = $this->getKiloUnit();
        }
        if (!$unit) {
            $this->logger->debug('There is no unit configured');
            return;
        }

        $productData['unit'] = [
            'id' => $unit->getId(),
        ];
        if (0 < $article->getLitre()) {
            $productData['purchaseUnit'] = $article->getLitre();
            $productData['referenceUnit'] = 1;
        } else {
            $productData['purchaseUnit'] = $article->getFillingWeight();
            $productData['referenceUnit'] = 1;
        }
    }

    protected function getUnit() : ?UnitEntity
    {
        if (!$this->wboConfig->get(WboConfig::UNIT_ID)) {
            $this->logger->debug('there is no litre unit configured');
            return null;
        }

        $id = $this->wboConfig->get(WboConfig::UNIT_ID);

        $collection = $this->unitRepository->search(new Criteria([$id]), $this->context);
        return !$collection->count() ? null : $collection->first();
    }

    protected function getKiloUnit() : ?UnitEntity
    {
        if (!$this->wboConfig->get(WboConfig::UNIT_KILO_ID)) {
            $this->logger->debug('there is no kilo unit configured');
            return null;
        }

        $id = $this->wboConfig->get(WboConfig::UNIT_KILO_ID);

        $collection = $this->unitRepository->search(new Criteria([$id]), $this->context);
        return !$collection->count() ? null : $collection->first();
    }
}
