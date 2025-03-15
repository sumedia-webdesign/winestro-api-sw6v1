<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1741767979ExtendV21Details extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1741767979;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('ALTER TABLE `wbo_articles` ADD `e_label_link` VARCHAR(512) DEFAULT NULL AFTER `ean`;');
        $connection->executeStatement('ALTER TABLE `wbo_articles` ADD `e_label_extern` VARCHAR(512) DEFAULT NULL AFTER `e_label_link`;');
        $connection->executeStatement('ALTER TABLE `wbo_articles` ADD `best_before_date` VARCHAR(32) DEFAULT NULL AFTER `e_label_extern`;');
        $connection->executeStatement('ALTER TABLE `wbo_articles` ADD `fat` DECIMAL(13,4) DEFAULT NULL AFTER `best_before_date`;');
        $connection->executeStatement('ALTER TABLE `wbo_articles` ADD `unsaturated_fats` DECIMAL(13,4) DEFAULT NULL AFTER `fat`;');
        $connection->executeStatement('ALTER TABLE `wbo_articles` ADD `carbonhydrates` DECIMAL(13,4) DEFAULT NULL AFTER `unsaturated_fats`;');
        $connection->executeStatement('ALTER TABLE `wbo_articles` ADD `salt` DECIMAL(13,4) DEFAULT NULL AFTER `carbonhydrates`;');
        $connection->executeStatement('ALTER TABLE `wbo_articles` ADD `fiber` DECIMAL(13,4) DEFAULT NULL AFTER `salt`;');
        $connection->executeStatement('ALTER TABLE `wbo_articles` ADD `vitamins` VARCHAR(256) DEFAULT NULL AFTER `fiber`;');
        $connection->executeStatement('ALTER TABLE `wbo_articles` ADD `free_sulfit_acid` DECIMAL(13,4) DEFAULT NULL AFTER `vitamins`;');
        $connection->executeStatement('ALTER TABLE `wbo_articles` ADD `sulfit_acid` DECIMAL(13,4) DEFAULT NULL AFTER `free_sulfit_acid`;');
        $connection->executeStatement('ALTER TABLE `wbo_articles` ADD `histamines` VARCHAR(256) DEFAULT NULL AFTER `sulfit_acid`;');
        $connection->executeStatement('ALTER TABLE `wbo_articles` ADD `glycerin` VARCHAR(256) DEFAULT NULL AFTER `histamines`;');
        $connection->executeStatement('ALTER TABLE `wbo_articles` ADD `label_text` VARCHAR(512) DEFAULT NULL AFTER `glycerin`;');
        $connection->executeStatement('ALTER TABLE `wbo_orders` ADD UNIQUE(`id`, `order_id`);');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
