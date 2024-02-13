<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Psr\Log\LoggerInterface;
use Sumedia\Wbo\Service\Wbo\Command\SetArticles;
use Symfony\Component\DependencyInjection\Container;

class UpdateArticlesHandler extends AbstractCronHandler
{
    /** @var SetArticles */
    protected $command;

    public function __construct(LoggerInterface $logger, EntityRepository $scheduledTaskRepository, Container $container)
    {
        parent::__construct($logger, $scheduledTaskRepository);
        $this->command = $container->get(SetArticles::class);
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
