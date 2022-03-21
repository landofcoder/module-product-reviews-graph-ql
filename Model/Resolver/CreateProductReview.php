<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductReviewsGraphQl\Model\Resolver;

use Lof\ProductReviews\Api\PostProductReviewsInterface;
use Lof\ProductReviews\Api\Data\ReviewInterface;
use Lof\ProductReviews\Api\Data\ReviewInterfaceFactory;
use Lof\ProductReviews\Model\Converter\Review as ReviewConverter;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Review\Helper\Data as ReviewHelper;
use Magento\Review\Model\Review\Config as ReviewsConfig;
use Magento\ReviewGraphQl\Mapper\ReviewDataMapper;
use Magento\ReviewGraphQl\Model\Review\AddReviewToProduct;
use Magento\Store\Api\Data\StoreInterface;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;

/**
 * Create product review resolver
 */
class CreateProductReview implements ResolverInterface
{
    /**
     * @var ReviewHelper
     */
    private $reviewHelper;

    /**
     * @var AddReviewToProduct
     */
    private $addReviewToProduct;

    /**
     * @var ReviewDataMapper
     */
    private $reviewDataMapper;

    /**
     * @var ReviewsConfig
     */
    private $reviewsConfig;

    /**
     * @var PostProductReviewsInterface
     */
    private $repository;

    /**
     * @var ReviewInterfaceFactory
     */
    private $reviewDataFactory;

    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var ReviewConverter
     */
    private $reviewConverter;

    /**
     * @param AddReviewToProduct $addReviewToProduct
     * @param ReviewDataMapper $reviewDataMapper
     * @param ReviewHelper $reviewHelper
     * @param ReviewsConfig $reviewsConfig
     * @param PostProductReviewsInterface $repository
     * @param ReviewInterfaceFactory $reviewDataFactory
     * @param GetCustomer $getCustomer
     * @param ReviewConverter $reviewConverter
     */
    public function __construct(
        AddReviewToProduct $addReviewToProduct,
        ReviewDataMapper $reviewDataMapper,
        ReviewHelper $reviewHelper,
        ReviewsConfig $reviewsConfig,
        PostProductReviewsInterface $repository,
        ReviewInterfaceFactory $reviewDataFactory,
        GetCustomer $getCustomer,
        ReviewConverter $reviewConverter
    ) {

        $this->addReviewToProduct = $addReviewToProduct;
        $this->reviewDataMapper = $reviewDataMapper;
        $this->reviewHelper = $reviewHelper;
        $this->reviewsConfig = $reviewsConfig;
        $this->repository = $repository;
        $this->reviewDataFactory = $reviewDataFactory;
        $this->getCustomer = $getCustomer;
        $this->reviewConverter = $reviewConverter;
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
        if (false === $this->reviewsConfig->isEnabled()) {
            throw new GraphQlAuthorizationException(__('Creating product reviews are not currently available.'));
        }

        $input = $args['input'];
        $customerId = null;

        if (false !== $context->getExtensionAttributes()->getIsCustomer()) {
            $customerId = (int) $context->getUserId();
            $customer = $this->getCustomer->execute($context);
            $input['email'] = $input['email'] ? $input['email'] : $customer->getEmail();
        }

        if (!$customerId && !$this->reviewHelper->getIsGuestAllowToWrite()) {
            throw new GraphQlAuthorizationException(__('Guest customers aren\'t allowed to add product reviews.'));
        }

        $sku = $input['sku'];
        $ratings = $input['ratings'];
        $images = isset($input['images']) ? $input['images'] : [];
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $data = [
            'nickname' => $input['nickname'],
            'title' => $input['summary'],
            'detail' => $input['text'],
            'guest_email' => isset($input['email']) ? $input['email']: '',
            'like_about' => isset($input['advantages']) ? $input['advantages'] : '',
            'not_like_about' => isset($input['disadvantages']) ? $input['disadvantages'] : '',
            'store_id' => $store->getId(),
            'stores' => [ 0, $store->getId() ],
            'images' => []
        ];
        if (!empty($images)) {
            $tmpImages = [];
            foreach ($images as $_image) {
                if (isset($_image["src"]) && !empty($_image["src"])) {
                    $tmpImages[] = [
                        "full_path" => $_image["src"],
                        "resized_path" => $_image["src"]
                    ];
                }
            }
            $data["images"] = $tmpImages;
        }

        $listRatings = $this->convertListRatings($ratings);

        $reviewDataObject = $this->reviewConverter->arrayToDataModel($data);
        /** @var ReviewInterface $review */
        $review = $this->repository->execute($customerId, $sku, $reviewDataObject);

        return ['review' => $review];
    }
}
