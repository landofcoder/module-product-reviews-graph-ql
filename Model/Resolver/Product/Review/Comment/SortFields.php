<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductReviewsGraphQl\Model\Resolver\Product\Review\Comment;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Retrieves the sort fields data
 */
class SortFields implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $sortFieldsOptions = [
            ['label' => "id", 'value' => "id"],
            ['label' => "review_id", 'value' => "review_id"],
            ['label' => "parent_id", 'value' => "parent_id"],
            ['label' => "title", 'value' => "title"],
            ['label' => "nickname", 'value' => "nickname"],
            ['label' => "created_at", 'value' => "created_at"],
            ['label' => "updated_at", 'value' => "updated_at"]
        ];

        $data = [
            'default' => "created_at",
            'options' => $sortFieldsOptions,
        ];

        return $data;
    }
}
