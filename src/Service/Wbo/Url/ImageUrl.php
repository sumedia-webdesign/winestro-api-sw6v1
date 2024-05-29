<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Url;

use Sumedia\Wbo\Config\WboConfig;

class ImageUrl implements UrlInterface {

    protected string $apiUrl;
    protected string $endpoint = 'bild.php';

    public function __construct(WboConfig $wboConfig)
    {
        $this->apiUrl = $wboConfig->get(WboConfig::API_URL);
    }

    public function getUrlPath(): string
    {
        return $this->apiUrl . '/' . $this->endpoint;
    }

    public function getUrlParams(): array
    {
        return array();
    }
}
