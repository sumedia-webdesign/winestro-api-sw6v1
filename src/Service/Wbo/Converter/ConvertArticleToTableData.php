<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Converter;

use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\Response\ExportOrders as ExportOrdersResponse;
use Sumedia\Wbo\Service\Wbo\Response\GetArticle\Article;

class ConvertArticleToTableData
{
    public function convert(Article $article, string $id, string $createdAt): array
    {
        $details = $article->getWineDetails();
        $images = $article->getArticleImages();

        $data = [
            'id' => $id,
            'articleNumber' => $article->getArticleNumber(),
            'articleNumberFormat' => $article->getArticleNumberFormat(),
            'productNumber' => $article->getProductNumber(),
            'name' => $article->getName(),
            'description' => $article->getDescription(),
            'type' => $article->getType(),
            'typeId' => $article->getTypeId(),
            'color' => $article->getColor(),
            'country' => $article->getCountry(),
            'region' => $article->getRegion(),
            'stockWarning' => $article->getStockWarning(),
            'weight' => $article->getWeight(),
            'price' => $article->getPrice(),
            'taxPercent' => $article->getTaxPercent(),
            'isFreeShipping' => $article->isFreeShipping(),
            'litre' => $article->getLitre(),
            'litrePrice' => $article->getLitrePrice(),
            'noLitrePrice' => $article->isNoLitrePrice(),
            'notice' => $article->getNotice(),
            'shopnotice' => $article->getShopnotice(),
            'groupId' => $groupNameToId[$article->getGroup()] ?? null,
            'kiloprice' => $article->getKiloprice(),
            'fillingWeight' => $article->getFillingWeight(),
            'isWine' => $article->isWine(),
            'waregroup' => implode(';', $article->getWareGroups()),
            'bottles' => $article->getBottles(),

            'image1' => $images->getImage(1),
            'image2' => $images->getImage(2),
            'image3' => $images->getImage(3),
            'image4' => $images->getImage(4),
            'bigImage1' => $images->getBigImage(1),
            'bigImage2' => $images->getBigImage(2),
            'bigImage3' => $images->getBigImage(3),
            'bigImage4' => $images->getBigImage(4),

            'category' => $article->getCategory(),
            'manufacturer' => $article->getManufacturer(),
            'unitId' => $article->getUnitId(),
            'unit' => $article->getUnit(),
            'unitQuantity' => $article->getUnitQuantity(),
            'ean' => $article->getEan(),

            'createdAt' => $createdAt,
            'updatedAt' => date('d-m-Y H:i:s'),

            'stock' => $article->getStock(),
            'stockDate' => $article->getStockDate(),

            'bundle' => $article->getBundle(),

            'apnr' => $details->getApnr(),
            'year' => $details->getYear(),
            'kind' => $details->getKind(),
            'quality' => $details->getQuality(),
            'taste' => $details->getTaste(),
            'nuances' => implode(',', $details->getNuances()),
            'awards' => $details->getAwards(),
            'cultivation' => $details->getCultivation(),
            'location' => $details->getLocation(),
            'development' => $details->getDevelopment(),
            'grounds' => $details->getGrounds(),
            'hasSulfite' => $details->hasSulfite(),
            'allergens' => implode(',', $details->getAllergens()),
            'sugar' => $details->getSugar(),
            'acid' => $details->getAcid(),
            'alcohol' => $details->getAlcohol(),
            'protein' => $details->getProtein(),
            'caloricValue' => $details->getCaloricValue(),
            'expertise' => $details->getExpertise(),
            'isDrunken' => $details->isDrunken(),
            'drinkingTemperature' => $details->getDrinkingTemperature(),
            'isStorable' => $details->isStorable(),
        ];

        return $data;
    }
}