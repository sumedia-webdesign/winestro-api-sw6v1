<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Command\SetArticles;

use Sumedia\Wbo\Service\Wbo\Response\GetArticle\Article;

class FreeShipping
{
    public function execute(Article $article, array &$productData)
    {
        $productData['shippingFree'] = $article->isFreeShipping() ? true : false;
    }
}
