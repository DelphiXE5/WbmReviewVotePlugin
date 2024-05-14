<?php declare(strict_types=1);

namespace Wbm\Storefront\Controller\Product;


use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Wbm\Core\Content\Product\SalesChannel\Review\Vote\AbstractProductReviewVoteSaveRoute;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class ProductReviewVoteController extends StorefrontController
{
    public function __construct(
        private readonly EntityRepository $productReviewsVoteRepository,
        private readonly AbstractProductReviewVoteSaveRoute $productReviewSaveVoteRoute,
    ) {
    }

    #[Route(path: '/product/review/{reviewId}/rating', name: 'frontend.detail.review.vote.save', defaults: ['XmlHttpRequest' => true, '_loginRequired' => true], methods: ['POST'])]
    public function saveReviewVote(string $reviewId, RequestDataBag $data, SalesChannelContext $context): Response
    {
        $forwardParams = json_decode($data->get('forwardParameters'), true);
        $productId = array_key_exists('productId', $forwardParams) ? $forwardParams['productId'] : '';

        try {
            $this->productReviewSaveVoteRoute->save($reviewId, $data, $context);
        } catch (ConstraintViolationException $formViolations) {
            return $this->forwardToRoute('frontend.product.reviews', [
                'productId' => $productId,
                'success' => -1,
                'formViolations' => $formViolations,
                'data' => $data,
            ], ['productId' => $productId]);
        }

        $forwardParams = [
            'productId' => $productId,
            'success' => 1,
            'data' => $data,
            'parentId' => $data->get('parentId'),
        ];

        return $this->forwardToRoute('frontend.product.reviews', $forwardParams, ['productId' => $productId]);
    }
}