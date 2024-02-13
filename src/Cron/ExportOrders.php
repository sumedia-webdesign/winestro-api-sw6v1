<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class ExportOrders extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'wbo.export_orders';
    }

    public static function getDefaultInterval(): int
    {
        return 360;
    }
}
