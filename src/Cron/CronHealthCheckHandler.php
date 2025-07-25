<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Sumedia\Wbo\Service\Wbo\Command\CronHealthCheck as CronHealthCheckCommand;

#[AsMessageHandler(handles: CronHealthCheck::class)]
class CronHealthCheckHandler extends AbstractCronHandler
{
    protected CronHealthCheckCommand $command;

    public function __construct(LoggerInterface $logger, EntityRepository $scheduledTaskRepository, Container $container)
    {
        parent::__construct($logger, $scheduledTaskRepository);
        $this->command = $container->get(CronHealthCheckCommand::class);
    }

    public static function getHandledMessages(): iterable
    {
        return [ CronHealthCheck::class ];
    }

    public function run(): void
    {
        $this->command->execute();
    }
}
