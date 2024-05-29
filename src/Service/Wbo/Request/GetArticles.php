<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Request;

use Sumedia\Wbo\Service\Wbo\Response\GetArticles as GetArticlesResponse;

class GetArticles extends RequestAbstract
{
    protected string $apiAction = 'getArtikel';
    protected string $responseClass = GetArticlesResponse::class;
}
