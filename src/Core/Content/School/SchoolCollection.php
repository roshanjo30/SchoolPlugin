<?php declare(strict_types=1);

namespace SchoolPlugin\Core\Content\School;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<SchoolEntity>
 */
class SchoolCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return SchoolEntity::class;
    }
}