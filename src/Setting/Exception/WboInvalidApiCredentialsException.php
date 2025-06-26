<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Setting\Exception;

use Symfony\Component\HttpFoundation\Response;
use Shopware\Core\Framework\ShopwareHttpException;

class WboInvalidApiCredentialsException extends ShopwareHttpException
{
    public function __construct()
    {
        parent::__construct('Provided API credentials are invalid');
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    public function getErrorCode(): string
    {
        return 'SUMEDIA_WBO__INVALID_API_CREDENTIALS';
    }
}
