<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1670032804AddFillingWeight extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1670032804;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("ALTER TABLE `wbo_articles` ADD `filling_weight` DECIMAL(13,4) NULL AFTER `kiloprice`");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
