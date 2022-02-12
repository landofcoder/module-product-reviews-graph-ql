<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductReviewsGraphQl\Model\DataProvider;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Lof\ProductReviews\Model\ResourceModel\Gallery\CollectionFactory as ReviewGalleryCollectionFactory;
use Lof\ProductReviews\Model\Gallery;
use Lof\ProductReviews\Helper\Data;

/**
 * Provides gallery reviews result
 *
 * The following class prepares the GraphQl endpoints' result for Customer and Product reviews
 */
class GalleryDataProvider
{

    /**
     * @var ReviewGalleryCollectionFactory
     */
    private $galleryCollectionFactory;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @param ReviewGalleryCollectionFactory $galleryCollectionFactory
     * @param Data $helperData
     */
    public function __construct(
        ReviewGalleryCollectionFactory $galleryCollectionFactory,
        Data $helperData
    )
    {
        $this->galleryCollectionFactory = $galleryCollectionFactory;
        $this->helperData = $helperData;
    }

    /**
     * Get gallery reviews result
     *
     * @param int $reviewId
     *
     * @return array|mixed
     */
    public function getData(int $reviewId): array
    {
        $gallery = $this->galleryCollectionFactory->create()
                        ->addFieldToFilter("review_id", $reviewId)
                        ->addFieldToFilter("status", Gallery::STATUS_ENABLED)
                        ->getFirstItem();
        if ($gallery) {
            $images = $this->helperData->getGalleryImages($gallery);
            $gallery->setImages($images);
        }
        return $gallery;
    }
}
