<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1677564929AddRelationProd extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1677564929;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("
            CREATE TABLE `wbo_products` (
              `id` binary(16) NOT NULL,
              `product_id` binary(16) NOT NULL,
              `wbo_article_id` binary(16) NOT NULL,
              `created_at` datetime NOT NULL,
              `updated_at` datetime DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $connection->executeStatement("ALTER TABLE `wbo_products`
          ADD PRIMARY KEY (`id`),
          ADD KEY `wbo_products_product_id_to_product_id` (`product_id`);");
        $connection->executeStatement("ALTER TABLE `wbo_products`
            ADD CONSTRAINT `wbo_products_product_id_to_product_id` FOREIGN KEY (`product_id`) 
                REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
