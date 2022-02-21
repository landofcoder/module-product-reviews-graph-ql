<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductReviewsGraphQl\Model\DataProvider;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Lof\ProductReviewsGraphQl\Mapper\ReviewDataMapper;
use Lof\ProductReviews\Api\Data\ReviewDataInterface;

/**
 * Provides aggregated reviews result
 *
 * The following class prepares the GraphQl endpoints' result for Customer and Product reviews
 */
class AggregatedReviewsDataProvider
{
    /**
     * @var ReviewDataMapper
     */
    private $reviewDataMapper;

    /**
     * @param ReviewDataMapper $reviewDataMapper
     */
    public function __construct(ReviewDataMapper $reviewDataMapper)
    {
        $this->reviewDataMapper = $reviewDataMapper;
    }

    /**
     * Get reviews result
     *
     * @param ReviewDataInterface $reviewsCollection
     *
     * @return array
     */
    public function getData($reviewsCollection): array
    {
        $pageSize = $reviewsCollection->getPageSize();
        $size = $reviewsCollection->getTotalRecords();
        $currentPage = $reviewsCollection->getCurPage();

        if ($pageSize) {
            $maxPages = ceil($size / $pageSize);
        } else {
            $maxPages = 0;
        }

        if ($currentPage > $maxPages && $size > 0) {
            $currentPage = new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the number of pages available.',
                    [$maxPages]
                )
            );
        }

        $items = [];
        foreach ($reviewsCollection->getItems() as $reviewItem) {
            $items[] = $this->reviewDataMapper->map($reviewItem);
        }
        $detailedSummary = $reviewsCollection->getDetailedSummary();
        $detailed = [
            "one" => $detailedSummary ? $detailedSummary->getOne() : 0,
            "two" => $detailedSummary ? $detailedSummary->getTwo() : 0,
            "three" => $detailedSummary ? $detailedSummary->getThree() : 0,
            "four" => $detailedSummary ? $detailedSummary->getFour() : 0,
            "five" => $detailedSummary ? $detailedSummary->getFive() : 0
        ];

        return [
            'totalRecords' => $size,
            'ratingSummary' => $reviewsCollection->getRatingSummary(),
            'ratingSummaryValue' => $reviewsCollection->getRatingSummaryValue(),
            'recomendedPercent' => $reviewsCollection->getRecomendedPercent(),
            'totalRecordsFiltered' => $reviewsCollection->getTotalFound(),
            'detailedSummary' => $detailed,
            'items' => $items,
            'page_info' => [
                'page_size' => $pageSize,
                'current_page' => $currentPage,
                'total_pages' => $maxPages
            ],
            'model' => $reviewsCollection
        ];
    }
}
