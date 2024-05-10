<?php declare(strict_types=1);

namespace Wbm\Core\Content\Product\Aggregate\ProductReviewVote;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @package core
 * @method void                add(ArrayEntity $entity)
 * @method void                set(string $key, ArrayEntity $entity)
 * @method ArrayEntity[]    getIterator()
 * @method ArrayEntity[]    getElements()
 * @method ArrayEntity|null get(string $key)
 * @method ArrayEntity|null first()
 * @method ArrayEntity|null last()
 */
class ProductReviewVoteCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductReviewVoteEntity::class;
    }
}