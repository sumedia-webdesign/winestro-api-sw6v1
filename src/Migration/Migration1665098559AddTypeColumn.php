<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1665098559AddTypeColumn extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1665098559;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("ALTER TABLE `wbo_articles` ADD `type` VARCHAR(128) NULL AFTER `description`");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
