<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductReviewsGraphQl\Mapper;

use Magento\Catalog\Model\Product;
use Magento\Review\Model\Review;
use Lof\ProductReviews\Api\Data\ReplyInterface;

/**
 * Converts the review data from review object to an associative array
 */
class ReviewCommentDataMapper
{
    /**
     * Mapping the review comment data
     *
     * @param ReplyInterface $reply
     *
     * @return array
     */
    public function map(ReplyInterface $reply): array
    {
        return [
            'id' => $reply->getReplyId(),
            'review_id' => $reply->getReviewId(),
            'parent_id' => $reply->getParentReplyId(),
            'message' => $reply->getReplyComment(),
            'nickname' => $reply->getUserName(),
            'email' => $reply->getEmailAddress(),
            'status' => $reply->getStatus(),
            'created_at' => $reply->getCreatedAt(),
            'updated_at' => $reply->getUpdatedAt(),
            'model' => $reply
        ];
    }
}
