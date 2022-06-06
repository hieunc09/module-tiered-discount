<?php

namespace Zanui\TieredDiscount\Plugin\Magento\SalesRule\Model\Rule\Metadata;

use Magento\SalesRule\Model\Rule\Metadata\ValueProvider as SalesRuleValueProvider;

/**
 * Class ValueProvider
 * @package Zanui\TieredDiscount\Plugin\Magento\SalesRule\Model\Rule\Metadata
 */
class ValueProvider
{
    /**
     * @param SalesRuleValueProvider $subject
     * @param $result
     * @return mixed
     */
    public function afterGetMetadataValues(
        SalesRuleValueProvider $subject,
        $result
    ) {
        $actions = &$result['actions']['children']['simple_action']['arguments']['data']['config']['options'];

        $actions = array_merge($actions, [
            [
                'value' => 'tiered_discount',
                'label' => __('Spent $X, get $Y discount for the whole cart; Spent $W, get Z% discount for the whole cart')
            ]
        ]);

        return $result;
    }
}
