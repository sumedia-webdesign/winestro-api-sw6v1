<?php declare(strict_types=1);

namespace Sumedia\Wbo\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1721528862InstallOrdersTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1721528862;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("
            CREATE TABLE `wbo_orders` (
                `id` binary(16) NOT NULL,
                `order_id` binary(16) NOT NULL,
                `wbo_order_number` varchar(255) NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL DEFAULT '0000-01-01 00:00:00'
            ) ENGINE=InnoDB;
        ");
    }
}
