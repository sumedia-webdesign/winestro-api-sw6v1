<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Request;

use Sumedia\Wbo\Service\Wbo\Response\CheckOrderStatus as CheckOrderStatusResponse;

class CheckOrderStatus extends RequestAbstract
{
    protected $apiAction = 'getAuftragStatus';

    protected $responseClass = CheckOrderStatusResponse::class;

    public function setOrderNumber(string $orderNumber): void
    {
        $this->set('auftragnummer', $orderNumber);
    }
}
