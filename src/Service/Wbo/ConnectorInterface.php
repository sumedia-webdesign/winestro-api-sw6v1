<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Service\Wbo;

use Sumedia\Wbo\Service\Wbo\Request\RequestInterface;
use Sumedia\Wbo\Service\Wbo\Response\ResponseInterface;

interface ConnectorInterface
{
    public function execute(RequestInterface $request): ResponseInterface;
}