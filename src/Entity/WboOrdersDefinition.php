<?php

declare(strict_types=1);

namespace Sumedia\Wbo\Entity;

use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class WboOrdersDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'wbo_orders';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return WboOrdersEntity::class;
    }

    public function getCollectionClass(): string
    {
        return WboOrdersCollection::class;
    }

    public function defineFields(): FieldCollection
    {
        $fieldCollection = [];

        $fieldCollection[] = (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey());
        $fieldCollection[] = (new FkField('order_id', 'orderId', OrderDefinition::class))->addFlags(new Required());
        $fieldCollection[] = (new StringField('wbo_order_number', 'wboOrderNumber'))->addFlags(new Required());
        $fieldCollection[] = (new DateTimeField('created_at', 'createdAt'))->addFlags(new Required());
        $fieldCollection[] = (new DateTimeField('updated_at', 'updatedAt'));

        return new FieldCollection($fieldCollection);
    }

}