<?php

declare(strict_types=1);

namespace Sumedia\Wbo\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class WboOrdersEntity extends Entity
{
    use EntityIdTrait;

    /** @var string */
    protected $id;

    /** @var string */
    protected $orderId;

    /** @var string */
    protected $wboOrderNumber;

    public function getId() : string
    {
        return $this->id;
    }

    public function setId(string $id) : void
    {
        $this->id = $id;
    }

    public function getOrderId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): void
    {
        $this->productId = $productId;
    }

    public function getWboArticleId() : string
    {
        return $this->wboArticleId;
    }

    public function setWboArticleId(string $wboArticleId) : void
    {
        $this->wboArticleId = $wboArticleId;
    }
}
