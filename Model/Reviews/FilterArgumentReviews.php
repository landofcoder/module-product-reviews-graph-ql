<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\ProductReviewsGraphQl\Model\Reviews;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\ConfigInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\FieldEntityAttributesInterface;

/**
 * Class FilterArgumentReviews
 * @package Lof\ProductReviewsGraphQl\Model\Reviews
 */
class FilterArgumentReviews implements FieldEntityAttributesInterface
{
    /** @var ConfigInterface */
    private $config;

    /**
     * FilterArgumentReviews constructor.
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getEntityAttributes(): array
    {
        $fields = [];
        /** @var Field $field */
        foreach ($this->config->getConfigElement('ReviewFilterInput')->getFields() as $field) {
            $fieldName = $field->getName();
            if ($fieldName == "review_id") {
                $fieldName = "detail.review_id";
            }
            $fields[$field->getName()] = [
                'type' => 'String',
                'fieldName' => $fieldName,
            ];
        }
        return $fields;
    }
}
