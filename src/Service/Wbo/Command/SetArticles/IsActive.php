<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Command\SetArticles;

use Psr\Log\LoggerInterface;
use Sumedia\Wbo\Service\Wbo\Response\GetArticle\Article;

class IsActive
{
    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function execute(Article $article, array &$productData)
    {
        $active = true;

        $hasName = !empty($article->getName());
        $hasPrice = !empty($article->getPrice());
        $hasTaxPercent = !empty($article->getTaxPercent());
        $hasImage = !empty($article->getArticleImages()->getImage(1));

        if (!$hasName) {
            $this->logger->debug($article->getArticleNumber() . " has no name");
            $active = false;
        }

        if (!$hasPrice) {
            $this->logger->debug($article->getArticleNumber() . " has no price");
            $active = false;
        }

        if (!$hasTaxPercent) {
            $this->logger->debug($article->getArticleNumber() . " has no tax percent");
            $active = false;
        }

        if (!$hasImage) {
            $this->logger->debug($article->getArticleNumber() . " has no image");
            $active = false;
        }

        $isWine = $article->isWine();
        if ($isWine) {
            $hasAlcohol = !empty($article->getWineDetails()->getAlcohol());
            $hasLitre = !empty($article->getLitre());

            if (!$hasAlcohol) {
                $this->logger->debug($article->getArticleNumber() . " has no alcohol");
                $active = false;
            }

            if (!$hasLitre) {
                $this->logger->debug($article->getArticleNumber() . " has no litre");
                $active = false;
            }
        }

        $productData['active'] = $active;
    }

}
