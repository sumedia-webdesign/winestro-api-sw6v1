<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Request;

use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\Response\GetBestand as GetBestandResponse;

class GetBestand extends RequestAbstract
{
    protected $apiAction = 'getBestand';
    protected $responseClass = GetBestandResponse::class;

    public function __construct(
        WboConfig $wboConfig,
        string $apiAction = null,
        string $responseClass = null,
        string $urlClass = null
    ) {
        parent::__construct($wboConfig, $apiAction, $responseClass, $urlClass);
        $this->set('reservierung', 'true');
    }

    public function setArticleNr(string $articleNr): void
    {
        $this->set('artikelnr', $articleNr);
    }
}
