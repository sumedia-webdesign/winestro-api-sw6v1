<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Response\GetArticle;

class ArticleImages
{
    protected $images = [];

    protected $bigImages = [];

    public function __construct(array $articleData)
    {
        foreach(range(1,4) AS $i) {
            if (1 == $i) {
                $this->images[$i] = is_array($articleData['artikel_bild']) ? '' : $articleData['artikel_bild'];
                $this->bigImages[$i] = is_array($articleData['artikel_bild_big']) ? '' : $articleData['artikel_bild_big'];
            } else {
                $this->images[$i] = is_array($articleData['artikel_bild_' . $i]) ? '' : $articleData['artikel_bild_'.$i];
                $this->bigImages[$i] = is_array($articleData['artikel_bild_big_' . $i]) ? '' : $articleData['artikel_bild_big_'.$i];
            }
        }
    }

    public function getImage(int $index): ?string
    {
        if (isset($this->images[$index]) && $this->isImage($this->images[$index])) {
            return $this->images[$index];
        }
        return null;
    }

    public function getBigImage(int $index): ?string
    {
        if (isset($this->bigImages[$index]) && $this->isImage($this->bigImages[$index])) {
            return $this->bigImages[$index];
        }
        return null;
    }

    protected function isImage(string $image): bool
    {
        $extension = substr($image,strrpos($image,'.')+1);
        if (!$extension) return false;
        return in_array(strtolower($extension),['jpg','gif','png','tiff']);
    }
}
