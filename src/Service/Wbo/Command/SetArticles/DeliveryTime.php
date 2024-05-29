<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Command\SetArticles;

use Sumedia\Wbo\Service\Wbo\Response\GetArticle\Article;
use Sumedia\Wbo\Config\WboConfig;

class DeliveryTime
{
    protected WboConfig $wboConfig;

    public function __construct(
        WboConfig $wboConfig
    ) {
        $this->wboConfig = $wboConfig;
    }

    public function execute(Article $article, array &$productData)
    {
        if (!$this->wboConfig->get('deliveryTimeId')) {
            return;
        }

        $productData['deliveryTime'] = ['id' => $this->wboConfig->get('deliveryTimeId')];
    }
}
