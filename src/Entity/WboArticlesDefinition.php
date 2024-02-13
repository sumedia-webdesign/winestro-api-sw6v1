<?php

declare(strict_types=1);

namespace Sumedia\Wbo\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowEmptyString;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class WboArticlesDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'wbo_articles';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return WboArticlesEntity::class;
    }

    public function getCollectionClass(): string
    {
        return WboArticlesCollection::class;
    }

    public function defineFields(): FieldCollection
    {
        $fieldCollection = [];

        $fieldCollection[] = (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey());

        $fieldCollection[] = (new StringField('article_number', 'articleNumber', 64))->addFlags(new Required());
        $fieldCollection[] = (new StringField('article_number_format', 'articleNumberFormat', 128))->addFlags(new Required())->addFlags(new AllowEmptyString());
        $fieldCollection[] = (new StringField('product_number', 'productNumber', 64))->addFlags(new Required());
        $fieldCollection[] = (new StringField('name', 'name'))->addFlags(new Required());
        $fieldCollection[] = (new LongTextField('description', 'description'))->addFlags(new AllowHtml());
        $fieldCollection[] = (new StringField('type', 'type'));
        $fieldCollection[] = (new IntField('type_id', 'typeId'));
        $fieldCollection[] = (new StringField('color', 'color'));
        $fieldCollection[] = (new StringField('country', 'country'));
        $fieldCollection[] = (new StringField('region', 'region'));
        $fieldCollection[] = (new IntField('stock_warning', 'stockWarning'));
        $fieldCollection[] = (new FloatField('weight', 'weight'));
        $fieldCollection[] = (new FloatField('price', 'price'))->addFlags(new Required());
        $fieldCollection[] = (new FloatField('tax_percent', 'taxPercent'))->addFlags(new Required());
        $fieldCollection[] = (new BoolField('is_free_shipping', 'isFreeShipping'));
        $fieldCollection[] = (new BoolField('no_litre_price', 'noLitrePrice'));
        $fieldCollection[] = (new LongTextField('notice', 'notice'));
        $fieldCollection[] = (new FkField('group_id', 'groupId', WboWineGroupsDefinition::class, 'id'));
        $fieldCollection[] = (new BoolField('is_wine', 'isWine'));
        $fieldCollection[] = (new FloatField('bottles', 'bottles'));
        $fieldCollection[] = (new LongTextField('shopnotice', 'shopnotice'))->addFlags(new AllowHtml());
        $fieldCollection[] = (new FloatField('kiloprice', 'kiloprice'));
        $fieldCollection[] = (new FloatField('filling_weight', 'fillingWeight'));
        $fieldCollection[] = (new StringField('waregroup', 'waregroup'));

        $fieldCollection[] = (new StringField('allergens', 'allergens'));
        $fieldCollection[] = (new StringField('apnr', 'apnr'));
        $fieldCollection[] = (new StringField('awards', 'awars'));
        $fieldCollection[] = (new FloatField('caloric_value', 'caloricValue'));
        $fieldCollection[] = (new StringField('cultivation', 'cultivation'));
        $fieldCollection[] = (new StringField('location', 'location'));
        $fieldCollection[] = (new StringField('development', 'development'));
        $fieldCollection[] = (new StringField('drinking_temperature', 'drinkingTemperature'));
        $fieldCollection[] = (new LongTextField('expertise', 'expertise'));
        $fieldCollection[] = (new StringField('grounds', 'grounds'));
        $fieldCollection[] = (new BoolField('has_sulfite', 'hasSulfite'));
        $fieldCollection[] = (new BoolField('is_drunken', 'isDrunken'));
        $fieldCollection[] = (new StringField('is_storable', 'isStorable'));
        $fieldCollection[] = (new StringField('kind', 'kind'));
        $fieldCollection[] = (new FloatField('alcohol', 'alcohol'));
        $fieldCollection[] = (new FloatField('litre', 'litre'));
        $fieldCollection[] = (new FloatField('litre_price', 'litrePrice'));
        $fieldCollection[] = (new StringField('nuances', 'nuances'));
        $fieldCollection[] = (new FloatField('protein', 'protein'));
        $fieldCollection[] = (new StringField('quality', 'quality'));
        $fieldCollection[] = (new FloatField('sugar', 'sugar'));
        $fieldCollection[] = (new StringField('taste', 'taste'));
        $fieldCollection[] = (new StringField('year', 'year'));
        $fieldCollection[] = (new FloatField('acid', 'acid'));

        $fieldCollection[] = (new StringField('image_1', 'image1'));
        $fieldCollection[] = (new StringField('image_2', 'image2'));
        $fieldCollection[] = (new StringField('image_3', 'image3'));
        $fieldCollection[] = (new StringField('image_4', 'image4'));
        $fieldCollection[] = (new StringField('big_image_1', 'bigImage1'));
        $fieldCollection[] = (new StringField('big_image_2', 'bigImage2'));
        $fieldCollection[] = (new StringField('big_image_3', 'bigImage3'));
        $fieldCollection[] = (new StringField('big_image_4', 'bigImage4'));

        $fieldCollection[] = (new StringField('category', 'category'));
        $fieldCollection[] = (new StringField('manufacturer', 'manufacturer'));
        $fieldCollection[] = (new IntField('unit_id', 'unitId'));
        $fieldCollection[] = (new StringField('unit', 'unit'));
        $fieldCollection[] = (new IntField('unit_quantity', 'unitQuantity'));
        $fieldCollection[] = (new StringField('ean', 'ean'));


        $fieldCollection[] = (new DateTimeField('created_at', 'createdAt'))->addFlags(new Required());
        $fieldCollection[] = (new DateTimeField('updated_at', 'updatedAt'));
        $fieldCollection[] = (new DateTimeField('imported_at', 'importedAt'));

        $fieldCollection[] = (new IntField('stock', 'stock'));
        $fieldCollection[] = (new DateTimeField('stock_date', 'stockDate'));

        $fieldCollection[] = (new JsonField('bundle', 'bundle'));

        return new FieldCollection($fieldCollection);
    }

}