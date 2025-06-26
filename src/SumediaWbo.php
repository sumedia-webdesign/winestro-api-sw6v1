<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo;

// unbelievable =/
require_once(__DIR__ . '/Service/Wbo/Delivery/DeliveryQuantityFetcher.php');

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Sumedia\Wbo\Service\Wbo\Delivery\DeliveryQuantityFetcher;
use Sumedia\Wbo\Setup\Install;
use Sumedia\Wbo\Setup\Uninstall;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SumediaWbo extends Plugin
{
    public function setContainer(ContainerInterface $container = null): void
    {
        parent::setContainer($container);
        DeliveryQuantityFetcher::$container = $container;
    }

    public function install(InstallContext $installContext): void
    {
        /** @var EntityRepository $systemConfigRepository */
        $systemConfigRepository = $this->container->get('system_config.repository');
        /** @var EntityRepository $salesChannelRepository */
        $salesChannelRepository = $this->container->get('sales_channel.repository');
        /** @var EntityRepository $ruleRepository */
        $ruleRepository = $this->container->get('rule.repository');
        /** @var EntityRepository $promotionTranslationRepository */
        $promotionTranslationRepository = $this->container->get('promotion_translation.repository');
        /** @var EntityRepository $promotionRepository */
        $promotionRepository = $this->container->get('promotion.repository');
        /** @var PluginIdProvider $pluginIdProvider */
        $pluginIdProvider = $this->container->get(PluginIdProvider::class);
        /** @var SystemConfigService $systemConfigService */
        $systemConfigService = $this->container->get(SystemConfigService::class);
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        (new Install(
            $systemConfigRepository,
            $salesChannelRepository,
            $ruleRepository,
            $promotionTranslationRepository,
            $promotionRepository,
            $pluginIdProvider,
            $systemConfigService,
            $connection,
            static::class
        ))->install($installContext->getContext());

        parent::install($installContext);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        $context = $uninstallContext->getContext();
        /** @var EntityRepository $salesChannelRepository */
        $salesChannelRepository = $this->container->get('sales_channel.repository');

        if ($uninstallContext->keepUserData()) {
            parent::uninstall($uninstallContext);
            return;
        }

        /** @var EntityRepository $systemConfigRepository */
        $systemConfigRepository = $this->container->get('system_config.repository');
        /** @var EntityRepository $ruleRepository */
        $ruleRepository = $this->container->get('rule.repository');
        /** @var EntityRepository $promotionTranslationRepository */
        $promotionTranslationRepository = $this->container->get('promotion_translation.repository');
        /** @var EntityRepository $promotionRepository */
        $promotionRepository = $this->container->get('promotion.repository');
        /** @var PluginIdProvider $pluginIdProvider */
        $pluginIdProvider = $this->container->get(PluginIdProvider::class);
        /** @var SystemConfigService $systemConfigService */
        $systemConfigService = $this->container->get(SystemConfigService::class);
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        (new Uninstall(
            $systemConfigRepository,
            $salesChannelRepository,
            $ruleRepository,
            $promotionTranslationRepository,
            $promotionRepository,
            $pluginIdProvider,
            $systemConfigService,
            $connection,
            static::class
        ))->uninstall($context);

        parent::uninstall($uninstallContext);
    }

    public function activate(ActivateContext $activateContext): void
    {
        if (version_compare($activateContext->getCurrentShopwareVersion(), '6.4', '>')) {
            return;
        }
        parent::activate($activateContext);
    }

}
