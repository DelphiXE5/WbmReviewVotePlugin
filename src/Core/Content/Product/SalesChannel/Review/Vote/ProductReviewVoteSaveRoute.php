<?php declare(strict_types=1);

namespace Wbm\Core\Content\Product\SalesChannel\Review\Vote;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\Exception\ReviewNotActiveExeption;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use Shopware\Core\Framework\DataAbstractionLayer\Validation\EntityNotExists;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\NoContentResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\IsFalse;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Wbm\Core\Content\Product\Aggregate\ProductReviewVote\ProductReviewVoteEntity;
use Wbm\Core\Content\Product\SalesChannel\Review\Vote\AbstractProductReviewVoteSaveRoute;

#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('inventory')]
class ProductReviewVoteSaveRoute extends AbstractProductReviewVoteSaveRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $productReviewVoteRepository,
        private readonly EntityRepository $productReviewRepository,
        private readonly DataValidator $validator,
        private readonly SystemConfigService $config
    ) {
    }

    public function getDecorated(): AbstractProductReviewVoteSaveRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/product/review/{reviewId}/vote', name: 'store-api.product-review.save', methods: ['POST'], defaults: ['_loginRequired' => true])]
    public function save(string $reviewId, RequestDataBag $data, SalesChannelContext $context): NoContentResponse
    {
        $this->checkReviewsActive($context);

        /** @var CustomerEntity $customer */
        $customer = $context->getCustomer();

        $salesChannelId = $context->getSalesChannel()->getId();

        $customerId = $customer->getId();

        if ($data->get('positiveReview') != null) {
            $data->set('positiveReview', boolval($data->get('positiveReview')));
        }
        $data->set('customerId', $customerId);
        $data->set('productReviewId', $reviewId);

        $existingEntity = $this->checkReviewVoteExists($salesChannelId, $customerId, $reviewId, $context);

        if ($existingEntity) {
            $data->set('id', $existingEntity->getId());
        }

        $this->validate($data, $context);
        if (!$this->checkUserPermisson($data, $context)) {
            return new NoContentResponse();
        }

        $review = [
            'customerId' => $data->get('customerId'),
            'salesChannelId' => $salesChannelId,
            'productReviewId' => $data->get('productReviewId'),
            'positiveReview' => $data->get('positiveReview'),
        ];

        if ($data->get('id')) {
            $review['id'] = $data->get('id');
        }

        $this->productReviewVoteRepository->upsert([$review], $context->getContext());

        return new NoContentResponse();
    }

    private function checkUserPermisson(DataBag $data, SalesChannelContext $context): bool {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $data->get('productReviewId')));
        $criteria->addFilter(new EqualsFilter('customerId', $data->get('customerId')));

        return $this->productReviewRepository->search($criteria, $context->getContext())->getEntities()->first() == null;
    }

    private function validate(DataBag $data, SalesChannelContext $context): void
    {
        $definition = new DataValidationDefinition('review.create_vote');

        $definition->add('productReviewId', new NotBlank());

        $definition->add('positiveReview', new AtLeastOneOf([new Blank(), new IsTrue(), new IsFalse()]));

        if ($data->get('id')) {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('id', $data->get('id')));

            $definition->add('id', new EntityExists([
                'entity' => 'product_review_vote',
                'context' => $context->getContext(),
                'criteria' => $criteria,
            ]));
        } else {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('customerId', $data->get('customerId')));
            $criteria->addFilter(new EqualsFilter('salesChannelId', $data->get('salesChannelId')));
            $criteria->addFilter(new EqualsFilter('productReviewId', $data->get('productReviewId')));

            $definition->add('productReviewId', new EntityNotExists([
                'entity' => 'product_review_vote',
                'context' => $context->getContext(),
                'criteria' => $criteria,
            ]));
        }

        $this->validator->validate($data->all(), $definition);

        $violations = $this->validator->getViolations($data->all(), $definition);

        if (!$violations->count()) {
            return;
        }

        throw new ConstraintViolationException($violations, $data->all());
    }

    /**
     * @throws ReviewNotActiveExeption
     */
    private function checkReviewsActive(SalesChannelContext $context): void
    {
        $showReview = $this->config->get('core.listing.showReview', $context->getSalesChannel()->getId());

        if (!$showReview) {
            throw new ReviewNotActiveExeption();
        }
    }

    private function checkReviewVoteExists(string $salesChannelId, string $customerId, string $reviewId, SalesChannelContext $context): ?ProductReviewVoteEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
        $criteria->addFilter(new EqualsFilter('customerId', $customerId));
        $criteria->addFilter(new EqualsFilter('productReviewId', $reviewId));

        return $this->productReviewVoteRepository->search($criteria, $context->getContext())->getEntities()->first();
    }
}
