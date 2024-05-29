<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo;

use Sumedia\Wbo\Config\WboConfig;

class ArticleNumberParser
{
    protected WboConfig $wboConfig;

    public function __construct(
        WboConfig $wboConfig
    ) {
        $this->wboConfig = $wboConfig;
    }

    public function getFormat(): string
    {
        return $this->wboConfig->get(WboConfig::ARTICLE_NUMBER_FORMAT);
    }

    public function isValidArticleNumberFormat(): bool
    {
        $articleNumberFormat = $this->getFormat();
        return false != preg_match('#^\[(.*?)\]\+?\[?(.*?)\]?$#', $articleNumberFormat, $matches);
    }

    public function parseArticleNumber($articleNumber): string
    {
        $articleNumberFormat = $this->getFormat();
        preg_match('#^\[(.*?)\]\+?\[?(.*?)\]?$#', $articleNumberFormat, $matches);

        $spaceCount = substr_count($matches[1], '+');
        $articleNumberParts = explode(' ', (string) $articleNumber);

        return implode(' ', array_slice($articleNumberParts, 0, $spaceCount+1));
    }
}
