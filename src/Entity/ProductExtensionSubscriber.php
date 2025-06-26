<?php declare(strict_types=1);

namespace Sumedia\Wbo\Entity;

use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductExtensionSubscriber implements EventSubscriberInterface
{
    protected EntityRepository $wboArticlesRepository;

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
