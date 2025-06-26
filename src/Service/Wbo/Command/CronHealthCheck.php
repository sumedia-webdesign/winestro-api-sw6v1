<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Service\Wbo\Command;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskDefinition;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskEntity;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\ConnectorInterface;

class CronHealthCheck extends AbstractCommand implements CommandInterface
{
    protected WboConfig $config;
    protected ConnectorInterface $connector;
    protected Context $context;
    protected EntityRepository $scheduledTaskRepository;

    public function __construct(
        LoggerInterface $debugLogger,
        LoggerInterface $errorLogger,
        WboConfig $wboConfig,
        Context $context,
        EntityRepository $scheduledTaskRepository
    ) {
        parent::__construct($errorLogger, $debugLogger, $wboConfig);
        $this->context = $context;
        $this->scheduledTaskRepository = $scheduledTaskRepository;
    }

    public function execute(): void
    {
        $this->debug('wbo: check cron health');

        if (!$this->isActive()) {
            $this->debug('wbo is not active');
            return;
        }

        try {
            $tasks = $this->getScheduledTasks();
            $this->checkHealth($tasks);
        } catch(\Throwable $e) {
            $this->logException($e);
        }
    }

    protected function getScheduledTasks() : EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('name', [
            'wbo.check_order_status',
            'wbo.cron_health_check',
            'wbo.export_orders',
            'wbo.update_articles',
            'wbo.update_wine_groups'
        ]));
        return $this->scheduledTaskRepository->search($criteria, $this->context);
    }

    protected function checkHealth(EntitySearchResult $tasks): void
    {
        /** @var ScheduledTaskEntity $task */
        foreach ($tasks as $task) {
            if ($task->getStatus() == ScheduledTaskDefinition::STATUS_FAILED) {
                $this->scheduledTaskRepository->update([[
                    'id' => $task->getId(),
                    'status' => ScheduledTaskDefinition::STATUS_QUEUED
                ]], $this->context);
            }
        }
    }
}
