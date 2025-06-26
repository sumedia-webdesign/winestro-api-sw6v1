<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1676021365BottleField extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1676021365;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("ALTER TABLE `wbo_articles` ADD `bottles` DECIMAL(13,4) NULL AFTER `is_wine`");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
