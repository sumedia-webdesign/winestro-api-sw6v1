<?php declare(strict_types=1);

namespace Sumedia\Wbo\Cron;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

abstract class AbstractCronHandler extends ScheduledTaskHandler
{
    protected $errorLogger;

    public function __construct(LoggerInterface $errorLogger, EntityRepository $scheduledTaskRepository)
    {
        parent::__construct($scheduledTaskRepository, $errorLogger);
        $this->errorLogger = $errorLogger;
    }

    public function run(): void
    {
        set_error_handler([$this, 'handleError']);

        try {
            parent::run();
        } catch(\Throwable $e) {
            $this->logException($e);
        } finally {
            restore_error_handler();
        }
    }

    public function handleError($code, $message, $file, $line)
    {
        $exception = new \ErrorException($message, $code, E_ERROR, $file, $line);
        $this->logException($exception);
        return true;
    }

    public function logException(\Throwable $e): void
    {
        $message =
            $e->getMessage() . "\n in " .
            $e->getFile() . "\n line " .
            $e->getLine() . "\n" . $e->getTraceAsString();
        $this->errorLogger->error($message);
    }
}
