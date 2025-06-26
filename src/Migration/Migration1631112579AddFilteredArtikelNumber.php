<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1631112579AddFilteredArtikelNumber extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1631112579;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('ALTER TABLE `wbo_articles` 
            ADD `article_number_format` VARCHAR(128) NOT NULL AFTER `article_number`, 
            ADD `product_number` VARCHAR(64) NOT NULL AFTER `article_number_format`
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
