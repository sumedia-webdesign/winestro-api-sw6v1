<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1674206996NewFields extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1674206996;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("ALTER TABLE `wbo_articles` ADD `no_litre_price` TINYINT(1) NULL AFTER `is_free_shipping`");
        $connection->executeStatement("ALTER TABLE `wbo_articles` ADD `type_id` INT NULL AFTER `type`");
        $connection->executeStatement("ALTER TABLE `wbo_articles` ADD `color` VARCHAR(64) NULL AFTER `type_id`");
        $connection->executeStatement("ALTER TABLE `wbo_articles` ADD `country` VARCHAR(128) NULL AFTER `color`");
        $connection->executeStatement("ALTER TABLE `wbo_articles` ADD `region` VARCHAR(128) NULL AFTER `country`");
        $connection->executeStatement("ALTER TABLE `wbo_articles` ADD `stock_warning` INT NULL AFTER `region`");
        $connection->executeStatement("ALTER TABLE `wbo_articles` ADD `unit_id` INT NULL AFTER `category`");
        $connection->executeStatement("ALTER TABLE `wbo_articles` ADD `unit` VARCHAR(64) NULL AFTER `unit_id`");
        $connection->executeStatement("ALTER TABLE `wbo_articles` ADD `unit_quantity` INT NULL AFTER `unit`");
        $connection->executeStatement("ALTER TABLE `wbo_articles` ADD `ean` INT NULL AFTER `unit_quantity`");
        $connection->executeStatement("ALTER TABLE `wbo_articles` CHANGE `is_storable` `is_storable` VARCHAR(64) NULL DEFAULT NULL");
        $connection->executeStatement("ALTER TABLE `wbo_articles` CHANGE `drinking_temperature` `drinking_temperature` VARCHAR(64) NULL DEFAULT NULL");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
