<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1691972169ProductFKUpdate extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1691972169;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("ALTER TABLE `wbo_products` ADD CONSTRAINT `fk_product_id` FOREIGN KEY (`product_id`) REFERENCES `product`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; ");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
