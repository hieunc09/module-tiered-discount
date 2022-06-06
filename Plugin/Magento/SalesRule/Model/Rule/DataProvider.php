<?php

namespace Zanui\TieredDiscount\Plugin\Magento\SalesRule\Model\Rule;

use Zanui\TieredDiscount\Api\Data\RuleInterface;

/**
 * Class DataProvider
 * @package Zanui\TieredDiscount\Plugin\SalesRule\Model
 */
class DataProvider
{
    /**
     * @param \Magento\SalesRule\Model\Rule\DataProvider $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetData(\Magento\SalesRule\Model\Rule\DataProvider $subject, $result)
    {
        if (is_array($result)) {
            foreach ($result as &$item) {
                if (isset($item[RuleInterface::EXTENSION_ATTRIBUTES_KEY][RuleInterface::EXTENSION_CODE])
                    && $item[RuleInterface::EXTENSION_ATTRIBUTES_KEY][RuleInterface::EXTENSION_CODE] instanceof
                    RuleInterface
                ) {
                    $item[RuleInterface::EXTENSION_ATTRIBUTES_KEY][RuleInterface::EXTENSION_CODE] =
                        $item[RuleInterface::EXTENSION_ATTRIBUTES_KEY][RuleInterface::EXTENSION_CODE]->toArray();
                }
            }
        }

        return $result;
    }
}
