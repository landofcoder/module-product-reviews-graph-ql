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
use Magento\Review\Helper\Data as ReviewHelper;
use Magento\Review\Model\Review\Config as ReviewsConfig;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Lof\ProductReviewsGraphQl\Mapper\ReviewCommentDataMapper;
use Lof\ProductReviews\Helper\Data as AdvancedReviewHelper;
use Lof\ProductReviews\Api\ReviewRepositoryInterface;
use Lof\ProductReviews\Api\Data\ReplyInterfaceFactory;
use Lof\ProductReviews\Api\Data\ReplyInterface;

/**
 * Create product review reply resolver
 */
class CreateProductReviewComment implements ResolverInterface
{
    /**
     * @var ReviewHelper
     */
    private $reviewHelper;

    /**
     * @var ReplyInterfaceFactory
     */
    private $dataReplyFactory;

    /**
     * @var AdvancedReviewHelper
     */
    private $advancedReviewHelper;

    /**
     * @var ReviewRepositoryInterface
     */
    private $repository;

    /**
     * @var ReviewCommentDataMapper
     */
    private $reviewCommentDataMapper;

    /**
     * @var ReviewsConfig
     */
    private $reviewsConfig;

    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @param ReviewRepositoryInterface $repository
     * @param ReviewCommentDataMapper $reviewCommentDataMapper
     * @param ReviewHelper $reviewHelper
     * @param ReviewsConfig $reviewsConfig
     * @param AdvancedReviewHelper $advancedReviewHelper
     * @param ReplyInterfaceFactory $dataReplyFactory
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        ReviewRepositoryInterface $repository,
        ReviewCommentDataMapper $reviewCommentDataMapper,
        ReviewHelper $reviewHelper,
        ReviewsConfig $reviewsConfig,
        AdvancedReviewHelper $advancedReviewHelper,
        ReplyInterfaceFactory $dataReplyFactory,
        GetCustomer $getCustomer
    ) {

        $this->repository = $repository;
        $this->reviewCommentDataMapper = $reviewCommentDataMapper;
        $this->reviewHelper = $reviewHelper;
        $this->reviewsConfig = $reviewsConfig;
        $this->advancedReviewHelper = $advancedReviewHelper;
        $this->dataReplyFactory = $dataReplyFactory;
        $this->getCustomer = $getCustomer;
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

        $input = $args['input'];
        $customerId = null;

        if (empty($input) || empty($input['review_id']) || empty($input['message'])) {
            throw new GraphQlInputException(__('Value must contain "input", input.review_id, input.message property.'));
        }

        if (false !== $context->getExtensionAttributes()->getIsCustomer()) {
            $customerId = (int) $context->getUserId();
        }

        if (!$customerId && !$this->reviewHelper->getIsGuestAllowToWrite()) {
            throw new GraphQlAuthorizationException(__('Guest customers aren\'t allowed to add product reviews.'));
        }

        $replyData = $this->mappingReplyData($input, $customerId);

        if ($customerId) {
            $customer = $this->getCustomer->execute($context);
            $replyData->setEmailAddress($customer->getEmail());
            $replyDataResponse = $this->repository->replyByCustomer($customerId, $replyData);
        } else {
            $replyDataResponse = $this->repository->replyByGuest($replyData);
        }

        return ['comment' => $this->reviewCommentDataMapper->map($replyDataResponse)];
    }

    /**
     * mapping reply data
     *
     * @param mixed $args
     * @return ReplyInterface
     */
    protected function mappingReplyData($args): ReplyInterface
    {
        $replyData = $this->dataReplyFactory->create();
        $replyData->setReviewId(isset($args["review_id"]) ? (int)$args["review_id"] : 0);
        $replyData->setParentReplyId(isset($args["parent_id"]) ? (int)$args["parent_id"] : 0);
        $replyData->setReplyTitle(isset($args["title"]) ? $args["title"] : "");
        $replyData->setReplyComment(isset($args["message"]) ? $args["message"] : "");
        $replyData->setUserName(isset($args["nickname"]) ? $args["nickname"] : "");
        $replyData->setWebsite(isset($args["website"]) ? $args["website"] : "");
        $replyData->setEmailAddress(isset($args["email"]) ? $args["email"] : "");

        return $replyData;
    }
}
