<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo;

use Sumedia\Wbo\Config\WboConfig;

class Credentials
{
    /** @var string */
    protected $userId;

    /** @var string */
    protected $shopId;

    /** @var string */
    protected $clientId;

    /** @var string */
    protected $clientSecret;

    public function __construct(WboConfig $wboConfig)
    {
        $this->userId = $wboConfig->get(WboConfig::USER_ID);
        $this->shopId = $wboConfig->get(WboConfig::SHOP_ID);
        $this->clientId = $wboConfig->get(WboConfig::CLIENT_ID);
        $this->clientSecret = $wboConfig->get(WboConfig::CLIENT_SECRET);
    }

    public function getUrlParams()
    {
        $params = array(
            'UID' => $this->userId,
            'apiUSER' => $this->clientId,
            'apiCODE' => $this->clientSecret,
            'apiShopID' => $this->shopId
        );
        return $params;
    }
}
