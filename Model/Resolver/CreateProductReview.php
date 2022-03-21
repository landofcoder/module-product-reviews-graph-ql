<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductReviewsGraphQl\Model\Resolver;

use Lof\ProductReviews\Api\PostProductReviewsInterface;
use Lof\ProductReviews\Api\Data\ReviewInterface;
use Lof\ProductReviews\Model\Converter\RatingVote as RatingConverter;
use Lof\ProductReviews\Model\Converter\Review as ReviewConverter;
use Lof\ProductReviewsGraphQl\Mapper\ReviewDataMapper;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Review\Helper\Data as ReviewHelper;
use Magento\Review\Model\Review\Config as ReviewsConfig;
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
     * @var ReviewsConfig
     */
    private $reviewsConfig;

    /**
     * @var PostProductReviewsInterface
     */
    private $repository;

    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var ReviewConverter
     */
    private $reviewConverter;

    /**
     * @var RatingConverter
     */
    private $ratingConverter;

    /**
     * @var ReviewDataMapper
     */
    private $reviewDataMapper;

    /**
     * @param ReviewHelper $reviewHelper
     * @param ReviewsConfig $reviewsConfig
     * @param PostProductReviewsInterface $repository
     * @param GetCustomer $getCustomer
     * @param ReviewConverter $reviewConverter
     * @param RatingConverter $ratingConverter
     * @param ReviewDataMapper $reviewDataMapper
     */
    public function __construct(
        ReviewHelper $reviewHelper,
        ReviewsConfig $reviewsConfig,
        PostProductReviewsInterface $repository,
        GetCustomer $getCustomer,
        ReviewConverter $reviewConverter,
        RatingConverter $ratingConverter,
        ReviewDataMapper $reviewDataMapper
    ) {
        $this->reviewHelper = $reviewHelper;
        $this->reviewsConfig = $reviewsConfig;
        $this->repository = $repository;
        $this->getCustomer = $getCustomer;
        $this->reviewConverter = $reviewConverter;
        $this->ratingConverter = $ratingConverter;
        $this->reviewDataMapper = $reviewDataMapper;
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
            'ratings' => $ratings,
            'images' => []
        ];
        $data = $this->mappingGallery($images, $data);
        $reviewDataObject = $this->reviewConverter->arrayToDataModel($data);
        //$listRatings = $this->mappingRatings($ratings);
        //$reviewDataObject->setRatings($listRatings);
        /** @var ReviewInterface $review */
        $review = $this->repository->execute($customerId, $sku, $reviewDataObject);

        return ['review' => $this->reviewDataMapper->map($review)];
    }

    /**
     * mapping gallery images
     *
     * @param mixed|array $images
     * @param mixed|array $data
     * @return mixed|array
     */
    protected function mappingGallery($images, $data)
    {
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
        return $data;
    }

    /**
     * mapping review ratings
     *
     * @param mixed|array $ratings
     * @return \Lof\ProductReviews\Api\Data\RatingVoteInterface[]
     */
    protected function mappingRatings($ratings)
    {
        $listRatings = [];
        foreach ($ratings as $rating) {
            $listRatings[] = $this->ratingConverter->arrayToDataModel($rating);
        }
        $listRatings;
    }
}
