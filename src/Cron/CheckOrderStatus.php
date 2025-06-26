<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class CheckOrderStatus extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'wbo.check_order_status';
    }

    public static function getDefaultInterval(): int
    {
        return 360;
    }
}
