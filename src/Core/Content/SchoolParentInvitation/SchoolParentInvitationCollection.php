<?php declare(strict_types=1);

namespace SchoolPlugin\Core\Content\SchoolParentInvitation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<SchoolParentInvitationEntity>
 */
class SchoolParentInvitationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return SchoolParentInvitationEntity::class;
    }
}