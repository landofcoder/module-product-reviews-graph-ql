<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductReviewsGraphQl\Model\Resolver\Product\Review\Reply;

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
            ['label' => "reply_id", 'value' => "reply_id"],
            ['label' => "review_id", 'value' => "review_id"],
            ['label' => "reply_title", 'value' => "reply_title"],
            ['label' => "user_name", 'value' => "user_name"],
            ['label' => "parent_reply_id", 'value' => "parent_reply_id"],
            ['label' => "created_at", 'value' => "created_at"]
        ];

        $data = [
            'default' => "created_at",
            'options' => $sortFieldsOptions,
        ];

        return $data;
    }
}
