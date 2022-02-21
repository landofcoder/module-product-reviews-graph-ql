<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductReviewsGraphQl\Model\Resolver\Product;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Lof\ProductReviewsGraphQl\Model\DataProvider\AggregatedReviewsDataProvider;
use Lof\ProductReviews\Api\GetProductReviewsInterface;
use Lof\ProductReviews\Helper\Data;

class Reviews implements ResolverInterface
{
    /**
     * @var AggregatedReviewsDataProvider
     */
    private $aggregatedReviewsDataProvider;

    /**
     * @var GetProductReviewsInterface
     */
    private $repository;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @param GetProductReviewsInterface $repository
     * @param AggregatedReviewsDataProvider $aggregatedReviewsDataProvider
     * @param Data $helperData
     */
    public function __construct(
        GetProductReviewsInterface $repository,
        AggregatedReviewsDataProvider $aggregatedReviewsDataProvider,
        Data $helperData
    ) {
        $this->repository = $repository;
        $this->aggregatedReviewsDataProvider = $aggregatedReviewsDataProvider;
        $this->helperData = $helperData;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (false === $this->helperData->isEnabled()) {
            return ['items' => []];
        }

        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('Value must contain "model" property.'));
        }

        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }

        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        /** @var Product $product */
        $product = $value['model'];
        $sortBy = isset($args["sortBy"]) ? $args["sortBy"] : "default";
        $searchKeyword = isset($args["search"]) ? $args["search"] : "";
        //$store = $context->getExtensionAttributes()->getStore();

        $reviewsData = $this->repository->execute(
            $product->getSku(),
            $searchKeyword,
            (int)$args["pageSize"],
            (int)$args["currentPage"], $sortBy
        );

        return $this->aggregatedReviewsDataProvider->getData($reviewsData);
    }

}
