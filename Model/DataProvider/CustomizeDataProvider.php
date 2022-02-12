<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductReviewsGraphQl\Model\DataProvider;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Lof\ProductReviews\Model\ResourceModel\CustomReview\CollectionFactory as CustomReviewCollectionFactory;
use Lof\ProductReviews\Model\CustomReview;

/**
 * Provides customize reviews result
 *
 * The following class prepares the GraphQl endpoints' result for Customer and Product reviews
 */
class CustomizeDataProvider
{

    /**
     * @var CustomReviewCollectionFactory
     */
    private $customizeCollectionFactory;

    /**
     * @param CustomReviewCollectionFactory $customizeCollectionFactory
     */
    public function __construct(
        CustomReviewCollectionFactory $customizeCollectionFactory
    )
    {
        $this->customizeCollectionFactory = $customizeCollectionFactory;
    }

    /**
     * Get customize review Data result
     *
     * @param int $reviewId
     *
     * @return array|mixed
     */
    public function getData(int $reviewId): array
    {
        $customReview = $this->customizeCollectionFactory->create()
                        ->addFieldToFilter("review_id", $reviewId)
                        ->getFirstItem();

        return $customReview;
    }
}
