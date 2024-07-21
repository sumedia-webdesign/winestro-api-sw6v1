<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1720901207UpdateDefaultUpdatedAt extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1720901207;
    }

    public function update(Connection $connection): void
    {
        /** create compatibility with newer sql mode */
        $connection->executeStatement("ALTER TABLE wbo_wine_groups MODIFY COLUMN updated_at datetime NULL DEFAULT '0000-01-01 00:00:00'");
        $connection->executeStatement("ALTER TABLE wbo_articles MODIFY COLUMN updated_at datetime NULL DEFAULT '0000-01-01 00:00:00'");
        $connection->executeStatement("ALTER TABLE wbo_products MODIFY COLUMN updated_at datetime NULL DEFAULT '0000-01-01 00:00:00'");
    }

    public function updateDestructive(Connection $connection): void
    {
        parent::updateDestructive($connection);
    }
}
