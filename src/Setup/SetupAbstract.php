<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Setup;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;
use Shopware\Core\System\SystemConfig\SystemConfigService;

abstract class SetupAbstract
{
    /**
     * @var EntityRepository
     */
    protected $systemConfigRepository;

    /**
     * @var EntityRepository
     */
    protected $salesChannelRepository;

    /** @var EntityRepository */
    protected $ruleRepository;

    /** @var EntityRepository */
    protected $promotionTranslationRepository;

    /** @var EntityRepository */
    protected $promotionRepository;

    /**
     * @var PluginIdProvider
     */
    protected $pluginIdProvider;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var SystemConfigService
     */
    protected $systemConfig;

    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(
        EntityRepository $systemConfigRepository,
        EntityRepository $salesChannelRepository,
        EntityRepository $ruleRepository,
        EntityRepository $promotionTranslationRepository,
        EntityRepository $promotionRepository,
        PluginIdProvider $pluginIdProvider,
        SystemConfigService $systemConfig,
        Connection $connection,
        string $className
    ) {
        $this->systemConfigRepository = $systemConfigRepository;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->ruleRepository = $ruleRepository;
        $this->promotionTranslationRepository = $promotionTranslationRepository;
        $this->promotionRepository = $promotionRepository;
        $this->pluginIdProvider = $pluginIdProvider;
        $this->className = $className;
        $this->systemConfig = $systemConfig;
        $this->connection = $connection;
    }
}