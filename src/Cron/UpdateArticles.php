<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class UpdateArticles extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'wbo.update_articles';
    }

    public static function getDefaultInterval(): int
    {
        return 720;
    }
}
