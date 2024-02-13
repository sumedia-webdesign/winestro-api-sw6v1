<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1665104038AddBundleField extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1665104038;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("ALTER TABLE `wbo_articles` ADD `bundle` JSON NULL AFTER `stock_date`");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
