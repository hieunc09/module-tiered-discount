<?php

namespace Zanui\TieredDiscount\Override\Magento\SalesRule\Model;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;

/**
 * Class Validator
 * @package Zanui\TieredDiscount\Override\Magento\SalesRule\Model
 */
class Validator extends \Magento\SalesRule\Model\Validator
{
    /**
     * Calculate quote totals for each rule and save results
     *
     * @param mixed $items
     * @param Address $address
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Zend_Validate_Exception
     * @throws \Zend_Db_Select_Exception
     */
    public function initTotals($items, Address $address)
    {
        if (!$items) {
            return $this;
        }

        /** @var \Magento\SalesRule\Model\Rule $rule */
        foreach ($this->getRules($address) as $rule) {
            if ((\Magento\SalesRule\Model\Rule::CART_FIXED_ACTION !== $rule->getSimpleAction() && 'tiered_discount' !== $rule->getSimpleAction())
                || !$this->validatorUtility->canProcessRule($rule, $address)
            ) {
                continue;
            }
            $ruleTotalItemsPrice = 0;
            $ruleTotalBaseItemsPrice = 0;
            $ruleTotalItemsDiscountAmount = 0;
            $ruleTotalBaseItemsDiscountAmount = 0;
            $validItemsCount = 0;

            foreach ($items as $item) {
                if (!$this->isValidItemForRule($item, $rule)
                    || ($item->getChildren() && $item->isChildrenCalculated())
                    || $item->getNoDiscount()
                ) {
                    continue;
                }
                $qty = $this->validatorUtility->getItemQty($item, $rule);
                $ruleTotalItemsPrice += $this->getItemPrice($item) * $qty;
                $ruleTotalBaseItemsPrice += $this->getItemBasePrice($item) * $qty;
                $ruleTotalItemsDiscountAmount += $item->getDiscountAmount();
                $ruleTotalBaseItemsDiscountAmount += $item->getBaseDiscountAmount();
                $validItemsCount++;
            }

            $this->_rulesItemTotals[$rule->getId()] = [
                'items_price' => $ruleTotalItemsPrice,
                'items_discount_amount' => $ruleTotalItemsDiscountAmount,
                'base_items_price' => $ruleTotalBaseItemsPrice,
                'base_items_discount_amount' => $ruleTotalBaseItemsDiscountAmount,
                'items_count' => $validItemsCount,
            ];
        }

        return $this;
    }

    /**
     * Determine if quote item is valid for a given sales rule
     *
     * @param AbstractItem $item
     * @param Rule $rule
     * @return bool
     * @throws \Zend_Validate_Exception
     */
    private function isValidItemForRule(AbstractItem $item, Rule $rule)
    {
        if (!$rule->getActions()->validate($item)) {
            return false;
        }
        if (!$this->canApplyDiscount($item)) {
            return false;
        }
        return true;
    }
}
