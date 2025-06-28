<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;
use Sumedia\Wbo\Service\Wbo\Command\SetWineGroups as SetWineGroupsCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: UpdateWineGroups::class)]
class UpdateWineGroupsHandler extends AbstractCronHandler
{
    protected SetWineGroupsCommand $command;

    public function __construct(LoggerInterface $logger, EntityRepository $scheduledTaskRepository, Container $container)
    {
        parent::__construct($logger, $scheduledTaskRepository);
        $this->command = $container->get(SetWineGroupsCommand::class);
    }

    public static function getHandledMessages(): iterable
    {
        return [ UpdateWineGroups::class ];
    }

    public function run(): void
    {
        $this->command->execute();
    }
}
