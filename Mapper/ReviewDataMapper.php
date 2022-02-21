<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductReviewsGraphQl\Mapper;

use Magento\Catalog\Model\Product;
use Lof\ProductReviews\Api\Data\ReviewInterface;

/**
 * Converts the review data from review object to an associative array
 */
class ReviewDataMapper
{
    /**
     * @var ReviewCommentDataMapper
     */
    private $reviewCommentDataMapper;

    /**
     * @param ReviewCommentDataMapper $ReviewCommentDataMapper
     */
    public function __construct(ReviewDataMapper $reviewCommentDataMapper)
    {
        $this->reviewCommentDataMapper = $reviewCommentDataMapper;
    }

    /**
     * Mapping the review data
     *
     * @param ReviewInterface $review
     *
     * @return array
     */
    public function map(ReviewInterface $review): array
    {
        $customize = $review->getCustomize();
        $rating_votes = [];
        $images = [];
        $comments = [];

        if ($review->getComments()) {
            foreach ($review->getComments() as $_comment) {
                $comments[] = $this->reviewCommentDataMapper->map($_comment);
            }
        }

        if ($review->getRatings()) {
            foreach ($review->getRatings() as $_rating) {
                $rating_votes[] = [
                    "vote_id" => $_rating->getVoteId(),
                    "option_id" => $_rating->getOptionId(),
                    "rating_id" => $_rating->getRatingId(),
                    "review_id" => $_rating->getReviewId(),
                    "percent" => $_rating->getPercent(),
                    "value" => $_rating->getValue(),
                    "rating_code" => $_rating->getRatingCode()
                ];
            }
        }

        if ($review->getImages()) {
            foreach ($review->getImages() as $_image) {
                $images[] = [
                    "full_path" => $_image->getFullPath(),
                    "resized_path" => $_image->getResizedPath()
                ];
            }
        }

        return [
            'review_id' => $review->getId(),
            'created_at' => $review->getCreatedAt(),
            'answer' => $review->getAnswer(),
            'verified_buyer' => $review->getVerifiedBuyer(),
            'is_recommended' => $review->getIsRecommended(),
            'detail_id' => $customize ? $customize->getReviewCustomizeId() : 0,
            'title' => $review->getTitle(),
            'detail' => $review->getDetail(),
            'nickname' => $review->getNickname(),
            'like_about' => $review->getLikeAbout(),
            'not_like_about' => $review->getNotLikeAbout(),
            'guest_email' => $review->getGuestEmail(),
            'plus_review' => $review->getPlusReview(),
            'minus_review' => $review->getMinusReview(),
            'rating_votes' => $rating_votes,
            'images' => $images,
            'comments' => $comments,
            'model' => $review
        ];
    }
}
