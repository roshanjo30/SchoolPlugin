<?php declare(strict_types=1);

namespace SchoolPlugin\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1785401000CreateSchoolProductPriceTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1785401000;
    }


    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS school_product_price (

                id BINARY(16) NOT NULL,

                school_id BINARY(16) NOT NULL,

                product_id BINARY(16) NOT NULL,

                product_version_id BINARY(16) NOT NULL,

                price DOUBLE NOT NULL,

                active TINYINT(1) NOT NULL DEFAULT 1,

                created_at DATETIME(3) NOT NULL,

                updated_at DATETIME(3),

                PRIMARY KEY (id),

                CONSTRAINT fk_school_product_price_school
                    FOREIGN KEY (school_id)
                    REFERENCES school(id)
                    ON DELETE CASCADE,

                CONSTRAINT fk_school_product_price_product
                    FOREIGN KEY (product_id, product_version_id)
                    REFERENCES product(id, version_id)
                    ON DELETE CASCADE

            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');
    }


    public function updateDestructive(Connection $connection): void
    {
    }
}