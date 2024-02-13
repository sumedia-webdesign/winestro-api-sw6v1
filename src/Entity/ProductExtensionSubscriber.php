<?php declare(strict_types=1);

namespace Sumedia\Wbo\Entity;

use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEvents;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\PrefixFilter;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zend\EventManager\FilterChain;

class ProductExtensionSubscriber implements EventSubscriberInterface
{
    /** @var EntityRepository */
    protected $wboArticlesRepository;

    public function __construct(EntityRepository $wboArticlesRepository)
    {
        $this->wboArticlesRepository = $wboArticlesRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_LOADED_EVENT => 'onProductsLoaded'
        ];
    }

    public function onProductsLoaded(EntityLoadedEvent $event): void
    {
        foreach ($event->getEntities() as $entity) {

            $wboEntity = $this->wboArticlesRepository->search(
                (new Criteria())->addFilter(new EqualsFilter('productNumber', $entity->getProductNumber()))
                , Context::createDefaultContext())->first();
            if (!$wboEntity) {
                continue;
            }

            $entity->addExtension('wbo_article', $wboEntity);
        }
    }
}
