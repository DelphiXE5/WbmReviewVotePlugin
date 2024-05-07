<?php declare(strict_types=1);

namespace Wbm\Core\Content\Product\Aggregate\ProductReviewVote;

use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

#[Package('inventory')]
class ProductReviewVoteDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'product_review_vote';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string {
        return ProductReviewVoteEntity::class;
    }

    public function getCollectionClass(): string {
        return ProductReviewVoteCollection::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return ProductReviewDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('product_review_id', 'productReviewId', ProductReviewDefinition::class))->addFlags(new ApiAware(), new Required()),
            new FkField('customer_id', 'customerId', CustomerDefinition::class),
            (new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new BoolField('positiv_review', 'positivReview'))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('productReview', 'product_review_id', ProductReviewDefinition::class, 'id', false)),
            (new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class, 'id', false)),
            new ManyToOneAssociationField('salesChannel', 'sales_channel_id', SalesChannelDefinition::class, 'id', false),
        ]);
    }
}
