<?php

declare(strict_types=1);

namespace Sumedia\Wbo\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class WboArticlesCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return WboArticlesEntity::class;
    }
}
