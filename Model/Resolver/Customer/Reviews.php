<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductReviewsGraphQl\Model\Resolver\Customer;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Lof\ProductReviewsGraphQl\Mapper\ReviewDataMapper;
use Lof\ProductReviews\Api\ReviewRepositoryInterface;
use Lof\ProductReviews\Helper\Data;

class Reviews implements ResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ReviewRepositoryInterface
     */
    private $repository;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var ReviewDataMapper
     */
    private $reviewDataMapper;

    /**
     * construct
     *
     * @param ReviewRepositoryInterface $repositoryInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Data $helperData
     * @param ReviewDataMapper $reviewDataMapper
     */
    public function __construct(
        ReviewRepositoryInterface $repositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Data $helperData,
        ReviewDataMapper $reviewDataMapper
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->repository = $repositoryInterface;
        $this->helperData = $helperData;
        $this->reviewDataMapper = $reviewDataMapper;
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
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if (false === $this->helperData->isEnabled()) {
            return ['items' => []];
        }

        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }

        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        $customerId = $context->getUserId();
        $searchCriteria = $this->searchCriteriaBuilder->build( 'myAdvreviews', $args );
        $searchCriteria->setCurrentPage( $args['currentPage'] );
        $searchCriteria->setPageSize( $args['pageSize'] );
        $searchResult = $this->repository->getMyList($customerId, $searchCriteria);
        $totalPages = $args['pageSize'] ? ((int)ceil($searchResult->getTotalCount() / $args['pageSize'])) : 0;

        $items = [];

        foreach ($searchResult->getItems() as $reviewItem) {
            $items[] = $this->reviewDataMapper->map($reviewItem);
        }
        return [
            'total_count' => $searchResult->getTotalCount(),
            'items'       => $items,
            'page_info' => [
                'page_size' => $args['pageSize'],
                'current_page' => $args['currentPage'],
                'total_pages' => $totalPages
            ],
            'model' => $searchResult
        ];
    }
}
