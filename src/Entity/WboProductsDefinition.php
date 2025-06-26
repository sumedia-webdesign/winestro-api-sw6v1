<?php

declare(strict_types=1);

namespace Sumedia\Wbo\Entity;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class WboProductsDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'wbo_products';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return WboProductsEntity::class;
    }

    public function getCollectionClass(): string
    {
        return WboProductsCollection::class;
    }

    public function defineFields(): FieldCollection
    {
        $fieldCollection = [];

        $fieldCollection[] = (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new PrimaryKey(), new Required());
        $fieldCollection[] = (new FkField('wbo_article_id', 'wboArticleId', WboArticlesDefinition::class, 'id'))->addFlags(new Required());
        $fieldCollection[] = (new DateTimeField('created_at', 'createdAt'))->addFlags(new Required());
        $fieldCollection[] = (new DateTimeField('updated_at', 'updatedAt'));

        return new FieldCollection($fieldCollection);
    }

}