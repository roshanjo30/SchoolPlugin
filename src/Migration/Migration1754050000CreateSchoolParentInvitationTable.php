<?php declare(strict_types=1);

namespace SchoolPlugin\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1754050000CreateSchoolParentInvitationTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1754050000;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `school_parent_invitation` (
    `id` BINARY(16) NOT NULL,
    `school_id` BINARY(16) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,

    PRIMARY KEY (`id`),

    CONSTRAINT `fk_school_parent_invitation_school`
        FOREIGN KEY (`school_id`)
        REFERENCES `school` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    KEY `idx_school_parent_invitation_school_id` (`school_id`),
    KEY `idx_school_parent_invitation_email` (`email`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
SQL
        );
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}