<?php

namespace Sumedia\Wbo\Service\Wbo\Command\SetArticles;

use Sumedia\Wbo\Service\Wbo\Response\GetArticle\Article;

class FilterCollection
{
    protected array $filters = [];

    public function __construct()
    {
        $arguments = func_get_args();
        foreach ($arguments as $filter) {
            $this->filters[] = $filter;
        }
    }

    public function execute(Article $article, array &$productData): void
    {
        foreach ($this->filters as $filter) {
            $filter->execute($article, $productData);
        }
    }
}
