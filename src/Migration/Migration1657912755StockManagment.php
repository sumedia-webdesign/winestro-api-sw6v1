<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1657912755StockManagment extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1657912755;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("ALTER TABLE `wbo_articles` DROP `stock`");
        $connection->executeStatement("
            ALTER TABLE `wbo_articles` 
            ADD COLUMN `stock` INT NULL AFTER `imported_at`
        ");
        $connection->executeStatement("
            ALTER TABLE `wbo_articles` 
            ADD COLUMN `stock_date` DATETIME NULL AFTER `stock`
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
