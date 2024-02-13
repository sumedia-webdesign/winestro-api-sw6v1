<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1700069352FixEANRow extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1700069352;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("ALTER TABLE `wbo_articles` CHANGE `ean` `ean` VARCHAR(255) NULL DEFAULT NULL");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
