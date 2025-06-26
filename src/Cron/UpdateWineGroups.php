<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class UpdateWineGroups extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'wbo.update_wine_groups';
    }

    public static function getDefaultInterval(): int
    {
        return 3600;
    }
}
