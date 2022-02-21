<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductReviewsGraphQl\Model\Resolver\Product\Review;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Search\Model\Query;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier\Filter;
use Lof\ProductReviews\Api\ReviewRepositoryInterface;
use Lof\ProductReviewsGraphQl\Mapper\ReviewCommentDataMapper;
use Lof\ProductReviews\Helper\Data as AdvancedReviewHelper;

class Comments implements ResolverInterface
{

    /**
     * @var string
     */
    private const SPECIAL_CHARACTERS = '-+~/\\<>\'":*$#@()!,.?`=%&^';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ReviewRepositoryInterface
     */
    private $repository;

    /**
     * @var ReviewCommentDataMapper
     */
    private $reviewCommentDataMapper;

    /**
     * @var AdvancedReviewHelper
     */
    private $advancedReviewHelper;

    /**
     * @param ReviewRepositoryInterface $repository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param ReviewCommentDataMapper $reviewCommentDataMapper
     * @param AdvancedReviewHelper $advancedReviewHelper
     */
    public function __construct(
        ReviewRepositoryInterface $repository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ScopeConfigInterface $scopeConfig,
        ReviewCommentDataMapper $reviewCommentDataMapper,
        AdvancedReviewHelper $advancedReviewHelper
    ) {
        $this->repository = $repository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->reviewCommentDataMapper = $reviewCommentDataMapper;
        $this->advancedReviewHelper = $advancedReviewHelper;
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
        if (false === $this->reviewsConfig->isEnabled() || false === $this->advancedReviewHelper->isEnabled()) {
            throw new GraphQlAuthorizationException(__('get product review comments are not currently available.'));
        }

        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        if ($args['review_id'] < 1) {
            throw new GraphQlInputException(__('review_id value must be greater than 0.'));
        }

        $reviewId = (int)$args['review_id'];
        $sortFields = isset($args["sort"]) ? $args["sort"] : [];
        if ($sortFields) {
            $tmpSort = [];
            foreach ($sortFields as $field => $condition) {
                $field = $this->mappingCommentField($field);
                $tmpSort[$field] = $condition;
            }
            $args["sort"] = $tmpSort;
        }
        $store = $context->getExtensionAttributes()->getStore();
        $args[Filter::ARGUMENT_NAME] = $this->formatMatchFilters($args['filters'], $store);

        $searchCriteria = $this->searchCriteriaBuilder->build('productReviewReplies', $args);
        $searchCriteria->setCurrentPage($args['currentPage']);
        $searchCriteria->setPageSize($args['pageSize']);

        $searchResult = $this->repository->getListReply($reviewId, $searchCriteria);
        $totalPages = $args['pageSize'] ? ((int)ceil($searchResult->getTotalCount() / $args['pageSize'])) : 0;
        $items = [];

        foreach( $searchResult->getItems() as $_item ) {
            $items[] = $this->reviewCommentDataMapper->map($_item);
        }

        return [
            'total_count' => $searchResult->getTotalCount(),
            'items'       => $items,
            'page_info' => [
                'page_size' => $args['pageSize'],
                'current_page' => $args['currentPage'],
                'total_pages' => $totalPages
            ],
            'sort_fields' => $sortFields
        ];
    }

    /**
     * Format match filters to behave like fuzzy match
     *
     * @param array $filters
     * @param StoreInterface $store
     * @return array
     * @throws InputException
     */
    private function formatMatchFilters(array $filters, StoreInterface $store): array
    {
        $minQueryLength = $this->scopeConfig->getValue(
            Query::XML_PATH_MIN_QUERY_LENGTH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $availableMatchFilters = ["order_id", "store_id"];
        foreach ($filters as $filter => $condition) {
            $filter = $this->mappingCommentField($filter);
            $conditionType = current(array_keys($condition));
            $tmpminQueryLength = $minQueryLength;
            if (in_array($filter, $availableMatchFilters)) {
                $tmpminQueryLength = 1;
            }
            if ($conditionType === 'match') {
                $searchValue = trim(str_replace(self::SPECIAL_CHARACTERS, '', $condition[$conditionType]));
                $matchLength = strlen($searchValue);
                if ($matchLength < $tmpminQueryLength) {
                    throw new InputException(__('Invalid match filter. Minimum length is %1.', $tmpminQueryLength));
                }
                unset($filters[$filter]['match']);
                if ($filter == "store_id") {
                    $searchValue = (int)$searchValue;
                }
                if ($filter == "vendor_id") {
                    $searchValue = (int)$searchValue;
                }
                if ($filter == "customer_id") {
                    $searchValue = (int)$searchValue;
                }
                $filters[$filter]['like'] = '%' . $searchValue . '%';
            }
        }
        return $filters;
    }

    /**
     * mapping comment field
     *
     * @param string $filterField
     * @return string
     */
    protected function mappingCommentField($filterField)
    {
        $mappingData = [
            "id" => "reply_id",
            "parent_id" => "parent_reply_id",
            "review_id" => "review_id",
            "title" => "reply_title",
            "message" => "reply_comment",
            "nickname" => "user_name",
            "email" => "email_address",
            "created_at" => "created_at",
            "updated_at" => "updated_at"
        ];

        return isset($mappingData[$filterField]) ? $mappingData[$filterField] : $filterField;
    }
}
