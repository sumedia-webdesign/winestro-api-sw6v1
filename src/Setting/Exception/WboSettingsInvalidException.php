<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Setting\Exception;

use Symfony\Component\HttpFoundation\Response;
use Shopware\Core\Framework\ShopwareHttpException;

class WboSettingsInvalidException extends ShopwareHttpException
{
    public function __construct(string $missingSetting)
    {
        parent::__construct(
            'Required setting "{{ missingSetting }}" is missing or invalid',
            ['missingSetting' => $missingSetting]
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getErrorCode(): string
    {
        return 'SUMEDIA_WBOL__REQUIRED_SETTING_INVALID';
    }
}
