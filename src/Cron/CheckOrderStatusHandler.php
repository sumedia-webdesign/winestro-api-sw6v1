<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\DependencyInjection\Container;
use Sumedia\Wbo\Service\Wbo\Command\CheckOrderStatus;

class CheckOrderStatusHandler extends AbstractCronHandler
{
    /** @var CheckOrderStatus */
    protected $command;

    public function __construct(LoggerInterface $logger, EntityRepository $scheduledTaskRepository, Container $container)
    {
        parent::__construct($logger, $scheduledTaskRepository);
        $this->command = $container->get(CheckOrderStatus::class);
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
