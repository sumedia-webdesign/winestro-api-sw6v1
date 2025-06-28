<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Psr\Log\LoggerInterface;
use Sumedia\Wbo\Service\Wbo\Command\SetArticles as SetArticlesCommand;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: UpdateArticles::class)]
class UpdateArticlesHandler extends AbstractCronHandler
{
    protected SetArticlesCommand $command;

    public function __construct(LoggerInterface $logger, EntityRepository $scheduledTaskRepository, Container $container)
    {
        parent::__construct($logger, $scheduledTaskRepository);
        $this->command = $container->get(SetArticlesCommand::class);
    }

    public static function getHandledMessages(): iterable
    {
        return [ UpdateArticles::class ];
    }

    public function run(): void
    {
        $this->command->execute();
    }
}
