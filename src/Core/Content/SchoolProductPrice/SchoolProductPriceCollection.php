<?php declare(strict_types=1);

namespace SchoolPlugin\Core\Content\SchoolProductPrice;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class SchoolProductPriceCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return SchoolProductPriceEntity::class;
    }
}