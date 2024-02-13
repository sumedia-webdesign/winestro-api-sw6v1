<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Setting;

use Sumedia\Wbo\Setting\Exception\WboSettingsInvalidException;

class SumediaWboSettingStructValidator
{
    /** @throws WboSettingsInvalidException */
    public static function validate(SumediaWboSettingStruct $generalStruct): void
    {
        self::validateCredentials($generalStruct);
    }

    /** @throws WboSettingsInvalidException */
    protected static function validateCredentials(SumediaWboSettingStruct $generalStruct): void
    {
        try {
            $apiUrl = $generalStruct->getApiUrl();
        } catch (\TypeError $error) {
            throw new WboSettingsInvalidException('ApiUrl');
        }

        if ($apiUrl === '') {
            throw new WboSettingsInvalidException('ApiUrl');
        }

        try {
            $clientId = $generalStruct->getClientId();
        } catch (\TypeError $error) {
            throw new WboSettingsInvalidException('ClientId');
        }

        if ($clientId === '') {
            throw new WboSettingsInvalidException('ClientId');
        }

        try {
            $clientSecret = $generalStruct->getClientSecret();
        } catch(\TypeError $error) {
            throw new WboSettingsInvalidException('ClientSecret');
        }

        if ($clientSecret === '') {
            throw new WboSettingsInvalidException('ClientSecret');
        }
    }
}
