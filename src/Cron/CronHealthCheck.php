<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class CronHealthCheck extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'wbo.cron_health_check';
    }

    public static function getDefaultInterval(): int
    {
        return 3600;
    }
}
