<?php

declare(strict_types=1);

namespace Sumedia\Wbo\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class WboWineGroupsDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'wbo_wine_groups';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return WboWineGroupsEntity::class;
    }

    public function getCollectionClass(): string
    {
        return WboWineGroupsCollection::class;
    }

    public function defineFields(): FieldCollection
    {
        $fieldCollection = [];

        $fieldCollection[] = (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey());
        $fieldCollection[] = (new IntField('group_id', 'groupId'))->addFlags(new Required());
        $fieldCollection[] = (new StringField('name', 'name'))->addFlags(new Required());
        $fieldCollection[] = (new LongTextField('description', 'description'));
        $fieldCollection[] = (new DateTimeField('created_at', 'created_at'))->addFlags(new Required());
        $fieldCollection[] = (new DateTimeField('updated_at', 'updated_at'));

        return new FieldCollection($fieldCollection);
    }

}