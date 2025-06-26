<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1688147379ChangeShopnotiz extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1688147379;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `wbo_articles` CHANGE `shopnotice` `shopnotice` LONGTEXT CHARACTER SET utf8mb4 
                COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL; 
        ');
        $connection->executeStatement('
            ALTER TABLE `wbo_articles` CHANGE `notice` `notice` LONGTEXT CHARACTER SET utf8mb4 
                COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL; 
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
