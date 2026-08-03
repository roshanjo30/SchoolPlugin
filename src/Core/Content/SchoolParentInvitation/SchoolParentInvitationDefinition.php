<?php declare(strict_types=1);

namespace SchoolPlugin\Core\Content\SchoolParentInvitation;

use SchoolPlugin\Core\Content\School\SchoolDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\EmailField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;


class SchoolParentInvitationDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'school_parent_invitation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return SchoolParentInvitationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return SchoolParentInvitationCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(
                new Required(),
                new PrimaryKey()
            ),

            (new StringField(
                'parent_name',
                'parentName'
            ))->addFlags(new Required()),

            (new FkField(
                'school_id',
                'schoolId',
                SchoolDefinition::class
            ))->addFlags(new Required()),

            (new EmailField(
                'email',
                'email'
            ))->addFlags(new Required()),

            (new StringField(
                'token',
                'token'
            ))->addFlags(new Required()),

            new ManyToOneAssociationField(
                'school',
                'school_id',
                SchoolDefinition::class,
                'id'
            ),

            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}