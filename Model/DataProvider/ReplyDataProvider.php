<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductReviewsGraphQl\Model\DataProvider;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Lof\ProductReviews\Model\ResourceModel\ReviewReply\CollectionFactory as ReviewReplyCollectionFactory;
use Lof\ProductReviews\Model\ReviewReply;

/**
 * Provides reply reviews result
 *
 * The following class prepares the GraphQl endpoints' result for Customer and Product reviews
 */
class ReplyDataProvider
{
    const DEFAULT_PAGE_SIZE = 10;

    /**
     * @var ReviewReplyCollectionFactory
     */
    private $replyCollectionFactory;

    /**
     * @param ReviewReplyCollectionFactory $replyCollectionFactory
     */
    public function __construct(
        ReviewReplyCollectionFactory $replyCollectionFactory
    )
    {
        $this->replyCollectionFactory = $replyCollectionFactory;
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
        $customReviewCollection = $this->replyCollectionFactory->create()
                        ->addFieldToFilter("review_id", $reviewId)
                        ->addFieldToFilter("status", ReviewReply::STATUS_ENABLED)
                        ->addOrder("created_at", "DESC")
                        ->setPageSize(self::DEFAULT_PAGE_SIZE);

        if ($customReviewCollection->getPageSize()) {
            $maxPages = ceil($customReviewCollection->getSize() / $customReviewCollection->getPageSize());
        } else {
            $maxPages = 0;
        }
        return [
                'total_count' => $customReviewCollection->getSize(),
                'items' =>  $customReviewCollection->getItems(),
                'page_info' => [
                    'page_size' => self::DEFAULT_PAGE_SIZE,
                    'current_page' => 1,
                    'total_pages' => $maxPages
                ],
                'sort_fields' => [
                    "created_at" => "DESC"
                ]
        ];
    }
}
