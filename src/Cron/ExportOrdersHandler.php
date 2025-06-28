<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Sumedia\Wbo\Service\Wbo\Command\ExportOrders as ExportOrdersCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: ExportOrders::class)]
class ExportOrdersHandler extends AbstractCronHandler
{
    protected ExportOrdersCommand $command;

    public function __construct(LoggerInterface $logger, EntityRepository $scheduledTaskRepository, Container $container)
    {
        parent::__construct($logger, $scheduledTaskRepository);
        $this->command = $container->get(ExportOrdersCommand::class);
    }

    public static function getHandledMessages(): iterable
    {
        return [ ExportOrders::class ];
    }

    public function run(): void
    {
        $this->command->execute();
    }
}
