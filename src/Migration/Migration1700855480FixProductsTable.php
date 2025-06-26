<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1700855480FixProductsTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1700855480;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('DROP TABLE `wbo_products`');
        $connection->executeStatement("
            CREATE TABLE `wbo_products` (              
              `product_id` binary(16) NOT NULL,
              `wbo_article_id` binary(16) NOT NULL,
              `created_at` datetime NOT NULL,
              `updated_at` datetime DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $connection->executeStatement('ALTER TABLE `wbo_products` ADD PRIMARY KEY(`product_id`)');
        $connection->executeStatement('ALTER TABLE `wbo_products` ADD FOREIGN KEY (`product_id`) REFERENCES `product`(`id`) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
