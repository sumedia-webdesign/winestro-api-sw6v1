<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;
use Sumedia\Wbo\Service\Wbo\Command\SetWineGroups;

class UpdateWineGroupsHandler extends AbstractCronHandler
{
    /** @var SetWineGroups */
    protected $command;

    public function __construct(LoggerInterface $logger, EntityRepository $scheduledTaskRepository, Container $container)
    {
        parent::__construct($logger, $scheduledTaskRepository);
        $this->command = $container->get(SetWineGroups::class);
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
