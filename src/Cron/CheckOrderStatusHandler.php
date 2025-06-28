<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\DependencyInjection\Container;
use Sumedia\Wbo\Service\Wbo\Command\CheckOrderStatus as CheckOrderStatusCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: CheckOrderStatus::class)]
class CheckOrderStatusHandler extends AbstractCronHandler
{
    protected CheckOrderStatusCommand $command;
    public function __construct(LoggerInterface $logger, EntityRepository $scheduledTaskRepository, Container $container)
    {
        parent::__construct($logger, $scheduledTaskRepository);
        $this->command = $container->get(CheckOrderStatusCommand::class);
    }

    public static function getHandledMessages(): iterable
    {
        return [ CheckOrderStatus::class ];
    }

    public function run(): void
    {
        $this->command->execute();
    }
}
