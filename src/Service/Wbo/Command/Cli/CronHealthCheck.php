<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\WinestroAPI\Service\Wbo\Command\Cli;

use Sumedia\WinestroAPI\Config\WboConfig;
use Psr\Log\LoggerInterface;
use Sumedia\WinestroAPI\Service\Wbo\Command\CommandInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CronHealthCheck extends Command
{
    protected static $defaultName = 'wbo:cron-health-check';

    /** @var CommandInterface */
    protected $command;

    public function __construct(CommandInterface $command)
    {
        $this->command = $command;
        $this->setName(self::$defaultName);
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Check the health of the cronjobs');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->command->execute();
        
        return 0;
    }
}