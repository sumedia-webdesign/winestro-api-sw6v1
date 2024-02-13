<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Setting\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Sumedia\Wbo\Setting\SumediaWboSettingStruct;
use Sumedia\Wbo\Setting\SumediaWboSettingStructValidator;

class SettingsService implements SettingsServiceInterface
{
    public const SYSTEM_CONFIG_DOMAIN = 'SumediaWbo.settings.';

    /** @var SystemConfigService */
    private $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public function getSettings(?string $salesChannelId = null, bool $inherited = true): SumediaWboSettingStruct
    {
        $values = $this->systemConfigService->getDomain(
            self::SYSTEM_CONFIG_DOMAIN,
            $salesChannelId,
            $inherited
        );

        $propertyValuePairs = [];

        /** @var string $key */
        foreach ($values as $key => $value) {
            $property = (string) \mb_substr($key, \mb_strlen(self::SYSTEM_CONFIG_DOMAIN));
            if ($property === '') {
                continue;
            }
            $propertyValuePairs[$property] = $value;
        }

        $settingsEntity = new SumediaWboSettingStruct();
        $settingsEntity->assign($propertyValuePairs);
        if ($inherited) {
            SumediaWboSettingStructValidator::validate($settingsEntity);
        }

        return $settingsEntity;
    }

    public function updateSettings(array $settings, ?string $salesChannelId = null): void
    {
        foreach ($settings as $key => $value) {
            $this->systemConfigService->set(
                self::SYSTEM_CONFIG_DOMAIN . $key,
                $value,
                $salesChannelId
            );
        }
    }
}
