<?php declare(strict_types=1);

namespace Wbm\Core\Content\Product\Aggregate\ProductReviewVote;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class ProductReviewVoteEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $productReviewId;

    /**
     * @var string|null
     */
    protected $customerId;

    /**
     * @var string
     */
    protected $salesChannelId;

    /**
     * @var bool|null
     */
    protected $positivReview;

    /**
     * @var ProductReviewEntity|null
     */
    protected $productReview;

    /**
     * @var CustomerEntity|null
     */
    protected $customer;

    /**
     * @var SalesChannelEntity|null
     */
    protected $salesChannel;

    /**
     * @var \DateTimeInterface
     */
    protected $createdAt;

    /**
     * @var \DateTimeInterface|null
     */
    protected $updatedAt;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getProductReviewId(): string
    {
        return $this->productReviewId;
    }

    public function setProductReviewId(string $productReviewId): void
    {
        $this->productReviewId = $productReviewId;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function setCustomerId(?string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    public function getPositivReview(): ?bool
    {
        return $this->positivReview;
    }

    public function setPositivReview(?bool $positivReview): void
    {
        $this->positivReview = $positivReview;
    }

    public function getProductReview(): ?ProductReviewEntity
    {
        return $this->productReview;
    }

    public function setProductReview(?ProductReviewEntity $productReview): void
    {
        $this->productReview = $productReview;
    }

    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

    public function getSalesChannel(): ?SalesChannelEntity
    {
        return $this->salesChannel;
    }

    public function setSalesChannel(?SalesChannelEntity $salesChannel): void
    {
        $this->salesChannel = $salesChannel;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}