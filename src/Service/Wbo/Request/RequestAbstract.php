<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Request;

use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\Url\ShopUrl;
use Sumedia\Wbo\Service\Wbo\Url\UrlInterface;

abstract class RequestAbstract implements RequestInterface
{
    protected string $apiUrl;
    protected string $apiAction;
    protected string $responseClass;
    protected string $urlClass = ShopUrl::class;
    protected array $data = [];
    protected WboConfig $wboConfig;
    protected UrlInterface $url;

    public function __construct(WboConfig $wboConfig, string $apiAction = null, string $responseClass = null, string $urlClass = null)
    {
        $this->wboConfig = $wboConfig;
        $this->apiAction = $apiAction ?: $this->apiAction;
        $this->responseClass = $responseClass ?: $this->responseClass;
        $this->urlClass = $urlClass ?: $this->urlClass;
    }

    public function getApiAction(): string
    {
        return $this->apiAction;
    }

    public function getResponseClass(): string
    {
        return $this->responseClass;
    }

    public function getUrl(): UrlInterface
    {
        if(!isset($this->url)) {
            $this->url = new $this->urlClass($this->wboConfig);
        }
        return $this->url;
    }

    public function getUrlParams(): array
    {
        return array(
            'apiACTION' => $this->apiAction
        );
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /** @return mixed */
    public function get($key)
    {
        return $this->data[$key] ?? null;
    }

    /** @param mixed $value */
    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }
}
