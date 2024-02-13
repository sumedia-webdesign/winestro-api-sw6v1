<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Response;

class CheckOrderStatus extends ResponseAbstract
{
    public function getOrderStatus(): string
    {
        return $this->get('item')[0]['auftrag_status'];
    }

    public function getDeliveryLink(): string
    {
        return $this->get('item')[0]['auftrag_versandlink'];
    }
}
