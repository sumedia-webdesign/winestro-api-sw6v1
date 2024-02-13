<?php declare(strict_types=1);

namespace Sumedia\Wbo\Core\Checkout\Cart\Delivery\Struct;

require_once(__DIR__ . '/../../../../../Service/Wbo/Delivery/DeliveryQuantityFetcher.php');

use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryPosition;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryPositionCollection as DeliveryPositionCollectionAlias;
use Sumedia\Wbo\Service\Delivery\DeliveryQuantityFetcher;

class DeliveryPositionCollection extends DeliveryPositionCollectionAlias
{
    public function getQuantity(): float
    {
        $deliveryQuantityFetcher = DeliveryQuantityFetcher::getInstance();
        $quantities = $this->map(function (DeliveryPosition $position) use ($deliveryQuantityFetcher) {
            return $deliveryQuantityFetcher->getQuantity($position);
        });
        return array_sum($quantities);
    }
}
