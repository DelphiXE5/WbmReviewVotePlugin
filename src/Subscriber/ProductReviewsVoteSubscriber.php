<?php declare(strict_types=1);

namespace Wbm\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Content\Product\ProductEvents;

class ProductReviewsVoteSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        // Return the events to listen to as array like this:  <event to listen to> => <method to execute>
        return [
            ProductEvents::PRODUCT_REVIEW_LOADED => 'onProductReviewLoaded'
        ];
    }

    public function onProductReviewLoaded(EntityLoadedEvent $event)
    {
        dd($event);
        // Do something
        // E.g. work with the loaded entities: $event->getEntities()
    }
}