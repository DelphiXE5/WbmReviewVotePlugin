<?php declare(strict_types=1);

namespace Wbm\Storefront\Controller;


use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductReviewVoteController
{
    #[Route(path: '/product/review/{reviewId}/rating', name: 'frontend.detail.review.vote.save', defaults: ['XmlHttpRequest' => true, '_loginRequired' => true], methods: ['POST'])]
    public function saveReviewVote(string $reviewId, RequestDataBag $data, SalesChannelContext $context): Response
    {
        $this->checkReviewsActive($context);

        try {
            $this->productReviewSaveRoute->save($reviewId, $data, $context);
        } catch (ConstraintViolationException $formViolations) {
            return $this->forwardToRoute('frontend.product.reviews', [
                'productId' => $reviewId,
                'success' => -1,
                'formViolations' => $formViolations,
                'data' => $data,
            ], ['productId' => $reviewId]);
        }

        $forwardParams = [
            'productId' => $reviewId,
            'success' => 1,
            'data' => $data,
            'parentId' => $data->get('parentId'),
        ];

        if ($data->has('id')) {
            $forwardParams['success'] = 2;
        }

        return $this->forwardToRoute('frontend.product.reviews', $forwardParams, ['productId' => $reviewId]);
    }
}