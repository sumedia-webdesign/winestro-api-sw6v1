<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

namespace Sumedia\Wbo\Service\Wbo\Response;

use Sumedia\Wbo\Service\Wbo\Response\GetArticle\Article;

class GetArticles extends ResponseAbstract
{
    /**
     * @return Article[]
     */
    public function getArticles(): array
    {
        $articles = [];
        foreach ($this->get('item') as $articleData) {
            $articles[] = new Article($articleData);
        }
        $this->sortArticlesByArticleNumber($articles);
        return $articles;
    }

    protected function sortArticlesByArticleNumber(array $articles): array
    {
        $articleNumbers = array_map(fn ($article) => $article->getArticleNumber(), $articles);
        uasort($articleNumbers, fn($a, $b) => $a > $b);
        $sortedArray = [];
        foreach (array_keys($sortedArray) as $index) {
            $sortedArray[] = $articles[$index];
        }
        return $sortedArray;
    }
}
