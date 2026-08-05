<?php declare(strict_types=1);

namespace SchoolPlugin\Core\Content\SchoolProductPrice;

use SchoolPlugin\Core\Content\School\SchoolDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;


class SchoolProductPriceDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'school_product_price';


    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return SchoolProductPriceEntity::class;
    }


    public function getCollectionClass(): string
    {
        return SchoolProductPriceCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([

            (new IdField(
                'id',
                'id'
            ))->addFlags(
                new PrimaryKey(),
                new Required()
            ),

            (new FkField(
                'school_id',
                'schoolId',
                SchoolDefinition::class
            ))->addFlags(
                new Required()
            ),


            (new FkField(
                'product_id',
                'productId',
                ProductDefinition::class
            ))->addFlags(
                new Required()
            ),

            new ReferenceVersionField(
                ProductDefinition::class,
                'product_version_id'
            ),

            (new FloatField(
                'price',
                'price'
            ))->addFlags(
                new Required()
            ),

            (new BoolField(
                'active',
                'active'
            ))->addFlags(
                new Required()
            ),

            new CreatedAtField(),

            new UpdatedAtField(),

            new ManyToOneAssociationField(
                'school',
                'school_id',
                SchoolDefinition::class,
                'id',
                false
            ),

            new ManyToOneAssociationField(
                'product',
                'product_id',
                ProductDefinition::class,
                'id',
                false
            ),

        ]);
    }
}