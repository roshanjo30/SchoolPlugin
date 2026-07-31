<?php declare(strict_types=1);

namespace SchoolPlugin\Core\Content\School;

use Shopware\Core\System\Country\CountryDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;

class SchoolDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'school';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return SchoolEntity::class;
    }

    public function getCollectionClass(): string
    {
        return SchoolCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(
                new Required(),
                new PrimaryKey(),
                new ApiAware()
            ),

            (new StringField('school_name', 'schoolName'))->addFlags(
                new Required(),
                new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING),
                new ApiAware()
            ),

            (new StringField('contact_person', 'contactPerson'))->addFlags(
                new Required(),
                new ApiAware()
            ),

            (new StringField('email', 'email'))->addFlags(
                new Required(),
                new ApiAware()
            ),

            new StringField('phone', 'phone'),

            new StringField('street', 'street'),

            new StringField('zipcode', 'zipcode'),

            new StringField('city', 'city'),

            new FkField('country_id', 'countryId', CountryDefinition::ENTITY_NAME),


            new FkField('logo_media_id', 'logoMediaId', MediaDefinition::ENTITY_NAME),

            new LongTextField('comment', 'comment'),

            (new StringField('status', 'status'))->addFlags(
                new Required(),
                new ApiAware()
            ),

            new CreatedAtField(),

            new UpdatedAtField(),

            new FkField(
                'category_id',
                'categoryId',
                CategoryDefinition::ENTITY_NAME
            ),

            (new FkField(
                'parent_category_id',
                'parentCategoryId',
                CategoryDefinition::ENTITY_NAME
            )),

            new ManyToOneAssociationField(
                'country',
                'country_id',
                CountryDefinition::class,
                'id',
                false
            ),

            new ManyToOneAssociationField(
                'logoMedia',
                'logo_media_id',
                MediaDefinition::class,
                'id',
                false
            ),

            new ManyToOneAssociationField(
                'category',
                'category_id',
                CategoryDefinition::class,
                'id',
                false
            ),

            new ManyToOneAssociationField(
                'parentCategory',
                'parent_category_id',
                CategoryDefinition::class,
                'id'
            ),

        ]);
    }
}