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
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Review\Model\Review\Config as ReviewsConfig;
use Magento\ReviewGraphQl\Mapper\ReviewDataMapper;
use Lof\ProductReviews\Helper\Data as AdvancedReviewHelper;
use Lof\ProductReviews\Api\RatingRepositoryInterface;

/**
 * get available product ratings types
 */
class GetRatings implements ResolverInterface
{
    /**
     * @var AdvancedReviewHelper
     */
    private $advancedReviewHelper;

    /**
     * @var ReviewDataMapper
     */
    private $reviewDataMapper;

    /**
     * @var ReviewsConfig
     */
    private $reviewsConfig;

    /**
     * @var RatingRepositoryInterface
     */
    private $repository;

    /**
     * @param ReviewsConfig $reviewsConfig
     * @param AdvancedReviewHelper $advancedReviewHelper
     * @param RatingRepositoryInterface $repository
     */
    public function __construct(
        ReviewsConfig $reviewsConfig,
        AdvancedReviewHelper $advancedReviewHelper,
        RatingRepositoryInterface $repository
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

        $store = $context->getExtensionAttributes()->getStore();
        $ratings = $this->repository->getList((int)$store->getId());

        return [
            "ratings" => $ratings
        ];
    }
}
