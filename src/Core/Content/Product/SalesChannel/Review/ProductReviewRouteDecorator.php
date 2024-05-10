<?php declare(strict_types=1);

namespace Wbm\Core\Content\Product\SalesChannel\Review;

use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewEntity;
use Shopware\Core\Content\Product\SalesChannel\Review\AbstractProductReviewRoute;
use Shopware\Core\Content\Product\SalesChannel\Review\ProductReviewRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Wbm\Core\Content\Product\Aggregate\ProductReviewVote\ProductReviewVoteEntity;

#[Route(defaults: ['_routeScope' => ['store-api']])]
class ProductReviewRouteDecorator extends AbstractProductReviewRoute
{
    protected EntityRepository $productReviewVoteRepository;

    private AbstractProductReviewRoute $decorated;

    public function __construct(EntityRepository $productReviewVoteRepository, AbstractProductReviewRoute $productDecoratorRoute)
    {
        $this->productReviewVoteRepository = $productReviewVoteRepository;
        $this->decorated = $productDecoratorRoute;
    }

    public function getDecorated(): AbstractProductReviewRoute
    {
        return $this->decorated;
    }

    #[Route(path: '/store-api/product/{productId}/reviews', name: 'store-api.product-review.list', methods: ['POST'], defaults: ['_entity' => 'product_review'])]
    public function load(string $productId, Request $request, SalesChannelContext $context, Criteria $criteria): ProductReviewRouteResponse
    {
        // We must call this function when using the decorator approach
        $response = $this->decorated->load($productId, $request, $context, $criteria);
        $this->getUserVote($response, $context);

        return $response;
    }

    private function getUserVote(ProductReviewRouteResponse $response, SalesChannelContext $context)
    {
        if ($context->getCustomerId() === null) {
            return;
        }

        /**
         * @var EntitySearchResult
         */
        $routeEntitySearchResult = $response->getObject();

        $reviewIds = $routeEntitySearchResult->getEntities()->getIds();
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter("customerId", $context->getCustomerId() ?? ""));
        $criteria->addFilter(new EqualsAnyFilter("productReviewId", $reviewIds));

        $entitySearchResult = $this->productReviewVoteRepository->search($criteria, $context->getContext());
        $routeEntitySearchResult->getEntities()->map(function (ProductReviewEntity $productReviewEntity) use (&$entitySearchResult) {
            /**
             * @var ArrayEntity
             */
            $voteExtension = $productReviewEntity->getExtension("votes");
            /**
             * @var ?ProductReviewVoteEntity
             */
            $userVote = $entitySearchResult->filterByProperty("productReviewId", $productReviewEntity->getId())->first();
            if ($userVote !== null && $voteExtension !== null) {
                $voteExtension->set("user", $userVote->getPositiveReview());
            }
            return $productReviewEntity;
        });
    }
}