<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Setting\Service;

use Sumedia\Wbo\Setting\SumediaWboSettingStruct;

interface SettingsServiceInterface
{
    /** @throws \Sumedia\Wbo\Setting\Exception\WboSettingsInvalidException */
    public function getSettings(?string $salesChannelId = null, bool $inherited = true): SumediaWboSettingStruct;
    public function updateSettings(array $settings, ?string $salesChannelId = null): void;
}
