<?php declare(strict_types=1);

namespace Wbm\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\TermsAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Content\Product\ProductEvents;

class ProductReviewsVoteSubscriber implements EventSubscriberInterface
{

    private EntityRepository $productReviewsVoteRepository;

    public function __construct(EntityRepository $productReviewsVoteRepository)
    {
        $this->productReviewsVoteRepository = $productReviewsVoteRepository;
    }
    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_REVIEW_LOADED => 'onProductReviewLoaded'
        ];
    }

    public function onProductReviewLoaded(EntityLoadedEvent $event)
    {
        $reviewIds = array_unique(array_column($event->getEntities(), 'id'));
        $votes = $this->productReviewsVoteRepository->search($this->createCriteria($reviewIds), $event->getContext());
        foreach ($event->getEntities() as $reviewEntity) {
            // retrieving the review bucket for the current review
            $reviewBucketList = array_filter($votes->getAggregations()->get('review')->getBuckets(), fn($bucket) => $bucket->getKey() == $reviewEntity->getId());
            // If there are no votes, return and set both values to 0 
            if (count($reviewBucketList) == 0) {
                $reviewEntity->addExtension('votes', new ArrayEntity(['positive' => 0, 'negative' => 0]));
                return;
            }
            $reviewBucket = array_pop($reviewBucketList);

            // retrieving the positive and negative buckets for the current review
            $positive = 0;
            $negative = 0;
            $positiveBucket = array_filter($reviewBucket->getResult()->getBuckets(), fn($bucket) => $bucket->getKey() == 1);
            $negativeBucket = array_filter($reviewBucket->getResult()->getBuckets(), fn($bucket) => $bucket->getKey() == 0);
            if (count($positiveBucket) > 0) {
                $positive = array_pop($positiveBucket)->getCount();
            }
            if (count($negativeBucket) > 0) {
                $negative = array_pop($negativeBucket)->getCount();
            }

            // adding the votes to the review entity
            $reviewEntity->addExtension('votes', new ArrayEntity(['positive' => $positive, 'negative' => $negative]));
        }
    }

    /**
     * @param string[] $reviewIds
     */
    private function createCriteria(array $reviewIds): Criteria
    {
        $criteria = new Criteria();
        $criteria->setLimit(0);
        $criteria->addFilter(new EqualsAnyFilter('productReviewId', $reviewIds));
        // Using terms aggregation to remove the data transfer load between database and application (only the entity count matters)
        $criteria->addAggregation(new TermsAggregation('review', 'productReviewId', aggregation: new TermsAggregation('voteType', 'positiveReview')));
        return $criteria;
    }

}