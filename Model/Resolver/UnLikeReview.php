<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductReviewsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Review\Model\Review\Config as ReviewsConfig;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Lof\ProductReviews\Helper\Data as AdvancedReviewHelper;
use Lof\ProductReviews\Api\UnLikeRepositoryInterface;

/**
 * Create product un review unlike resolver
 */
class UnLikeReview implements ResolverInterface
{
    /**
     * @var AdvancedReviewHelper
     */
    private $advancedReviewHelper;

    /**
     * @var ReviewsConfig
     */
    private $reviewsConfig;

    /**
     * @var UnLikeRepositoryInterface
     */
    private $repository;

    /**
     * @param ReviewsConfig $reviewsConfig
     * @param AdvancedReviewHelper $advancedReviewHelper
     * @param UnLikeRepositoryInterface $repository
     */
    public function __construct(
        ReviewsConfig $reviewsConfig,
        AdvancedReviewHelper $advancedReviewHelper,
        UnLikeRepositoryInterface $repository
    ) {
        $this->reviewsConfig = $reviewsConfig;
        $this->advancedReviewHelper = $advancedReviewHelper;
        $this->repository = $repository;
    }

    /**
     * Resolve product review ratings
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return array[]|Value|mixed
     *
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (false === $this->reviewsConfig->isEnabled() || false === $this->advancedReviewHelper->isEnabled()) {
            throw new GraphQlAuthorizationException(__('Creating product reviews are not currently available.'));
        }

        $reviewId = $args['review_id'];
        $customerId = null;

        if (empty($reviewId)) {
            throw new GraphQlInputException(__('Value must contain "review_id" property.'));
        }

        if (false !== $context->getExtensionAttributes()->getIsCustomer()) {
            $customerId = (int) $context->getUserId();
        }

        if (!$customerId) {
            throw new GraphQlAuthorizationException(__('Guest customers aren\'t allowed to un-like product review.'));
        }

        $review = $this->repository->execute((int)$customerId, (int)$reviewId);

        return $review ? true : false;
    }
}
