<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Sumedia\Wbo\Service\Wbo\Command\CronHealthCheck as CronHealthCheckCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;

class CronHealthCheckHandler extends AbstractCronHandler
{
    protected $command;

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
