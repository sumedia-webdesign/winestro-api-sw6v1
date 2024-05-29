<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Request;

use Sumedia\Wbo\Service\Wbo\Response\GetEinstellungen as GetEinstellungenResponse;

class GetEinstellungen extends RequestAbstract
{
    protected string $apiAction = 'getEinstellungen';
    protected string $responseClass = GetEinstellungenResponse::class;
}
