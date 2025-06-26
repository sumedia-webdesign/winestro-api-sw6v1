<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1660420106Manufacturer extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1660420106;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("
            ALTER TABLE `wbo_articles` 
            ADD COLUMN `manufacturer` VARCHAR(255) NULL AFTER `big_image_4`
        ");
        $connection->executeStatement("
            ALTER TABLE `wbo_articles` 
            ADD COLUMN `category` VARCHAR(255) NULL AFTER `big_image_4`
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
