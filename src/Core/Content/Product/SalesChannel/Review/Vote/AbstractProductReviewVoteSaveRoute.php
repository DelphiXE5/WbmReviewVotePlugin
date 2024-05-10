<?php declare(strict_types=1);

namespace Wbm\Core\Content\Product\SalesChannel\Review\Vote;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\NoContentResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

#[Package('inventory')]
abstract class AbstractProductReviewVoteSaveRoute
{
    abstract public function getDecorated(): AbstractProductReviewVoteSaveRoute;

    abstract public function save(string $reviewId, RequestDataBag $data, SalesChannelContext $context): NoContentResponse;
}
