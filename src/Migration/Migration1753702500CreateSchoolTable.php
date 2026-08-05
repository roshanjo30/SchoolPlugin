<?php declare(strict_types=1);

namespace SchoolPlugin\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1753702500CreateSchoolTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1753702500;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
CREATE TABLE IF NOT EXISTS `school` (
    `id` BINARY(16) NOT NULL,
    `school_name` VARCHAR(255) NOT NULL,
    `contact_person` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(255) NULL,
    `street` VARCHAR(255) NULL,
    `zipcode` VARCHAR(50) NULL,
    `city` VARCHAR(255) NULL,
    `country_id` BINARY(16) NULL,

    `logo_media_id` BINARY(16) NULL,

    `category_id` BINARY(16) NULL,

    `parent_category_id` BINARY(16) NULL,

    `comment` LONGTEXT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'disabled',
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`id`),

    CONSTRAINT `fk.school_plugin_school.country`
        FOREIGN KEY (`country_id`)
        REFERENCES `country` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT `fk.school_plugin_school.logo`
        FOREIGN KEY (`logo_media_id`)
        REFERENCES `media` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}