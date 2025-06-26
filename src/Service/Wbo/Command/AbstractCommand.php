<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Command;

use Psr\Log\LoggerInterface;
use Sumedia\Wbo\Config\WboConfig;
use Symfony\Component\Console\Command\Command;

class AbstractCommand
{
    protected LoggerInterface $errorLogger;
    protected LoggerInterface $debugLogger;
    protected WboConfig $wboConfig;

    public function __construct(LoggerInterface $errorLogger, LoggerInterface $debugLogger, WboConfig $wboConfig)
    {
        $this->errorLogger = $errorLogger;
        $this->debugLogger = $debugLogger;
        $this->wboConfig = $wboConfig;
    }

    public function isActive(): bool
    {
        return
            !empty($this->wboConfig->get(WboConfig::TAX_ID)) &&
            !empty($this->wboConfig->get(WboConfig::API_URL)) &&
            !empty($this->wboConfig->get(WboConfig::CLIENT_ID)) &&
            !empty($this->wboConfig->get(WboConfig::CLIENT_SECRET)) &&
            !empty($this->wboConfig->get(WboConfig::SHOP_ID)) &&
            !empty($this->wboConfig->get(WboConfig::USER_ID)) &&
            !empty($this->wboConfig->get(WboConfig::MEDIA_DIRECTORY));
    }

    public function debug(string $message): void
    {
        if ($this->wboConfig->get(WboConfig::DEBUG_ENABLED)) {
            $this->debugLogger->debug($message);
        }
    }

    public function log(string $message): void
    {
        $this->debugLogger->warning($message);
    }

    public function logException(\Throwable $e): void
    {
        $message =
            get_class($e) . ":\n" .
            $e->getMessage() . "\n in " .
            $e->getFile() . "\n line " .
            $e->getLine() . "\n" . $e->getTraceAsString();
        $this->errorLogger->error($message);
    }

}