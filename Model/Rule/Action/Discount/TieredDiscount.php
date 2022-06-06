<?php

namespace Zanui\TieredDiscount\Model\Rule\Action\Discount;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Zanui\TieredDiscount\Api\Data\RuleInterface;

/**
 * Class TieredDiscount
 * @package Zanui\TieredDiscount\Model\Rule\Action\Discount
 */
class TieredDiscount extends AbstractDiscount
{
    /**
     * @var \Zanui\TieredDiscount\Model\RuleResolver
     */
    protected $ruleResolver;

    /**
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Zanui\TieredDiscount\Model\RuleResolver $ruleResolver
     */
    public function __construct(
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Zanui\TieredDiscount\Model\RuleResolver $ruleResolver
    ) {
        parent::__construct($validator, $discountDataFactory, $priceCurrency);
        $this->ruleResolver = $ruleResolver;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return Data
     * @throws \Exception
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        /** @var RuleInterface $tieredDiscountRule */
        $tieredDiscountRule = $this->ruleResolver->getTieredDiscountRule($rule);
        if (!$tieredDiscountRule instanceof RuleInterface) {
            return $discountData;
        }

        $spentX = $tieredDiscountRule->getSpentX();
        $getY = $tieredDiscountRule->getGetY();
        $spentW = $tieredDiscountRule->getSpentW();
        $getZ = $tieredDiscountRule->getGetZ();
        if (!$spentX || !$getY || !$spentW || !$getZ) {
            return $discountData;
        }

        $allItems = [];
        foreach ($item->getAddress()->getAllVisibleItems() as $item) {
            $qty = $item->getQty();
            for ($i = 0; $i < $qty; $i++) {
                $allItems[] = $item;
            }
        }

        $itemsCount = count($allItems);
        $baseSum = $this->_getBaseSumOfItems($allItems);
        if ($baseSum < $spentX) {
            return $discountData;
        } elseif ($baseSum > $spentX && $baseSum < $spentW) {
            $discountData = $this->_calculate($rule, $item, $qty, $getY, $itemsCount);
        } else {
            $rulePercent = min(100, $getZ);
            $discountData = $this->_calculatePercent($rule, $item, $qty, $rulePercent);
        }

        return $discountData;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @param $getY
     * @param $itemsCount
     * @return Data
     */
    protected function _calculate($rule, $item, $qty, $getY, $itemsCount)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        $baseDiscountAmount = (float) $getY;
        $discountAmount = $this->priceCurrency->convert($baseDiscountAmount, $item->getQuote()->getStore());
        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);

        $discountAmountMin = min($itemPrice * $qty, $discountAmount * $qty);
        $baseDiscountAmountMin = min($baseItemPrice * $qty, $baseDiscountAmount * $qty);

        $discountData->setAmount($discountAmountMin);
        $discountData->setBaseAmount($baseDiscountAmountMin);

        return $discountData;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @param float $rulePercent
     * @return Data
     */
    protected function _calculatePercent($rule, $item, $qty, $rulePercent)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);

        $_rulePct = $rulePercent / 100;
        $discountData->setAmount(($qty * $itemPrice - $item->getDiscountAmount()) * $_rulePct);
        $discountData->setBaseAmount(($qty * $baseItemPrice - $item->getBaseDiscountAmount()) * $_rulePct);
        $discountData->setOriginalAmount(($qty * $itemOriginalPrice - $item->getDiscountAmount()) * $_rulePct);
        $discountData->setBaseOriginalAmount(
            ($qty * $baseItemOriginalPrice - $item->getBaseDiscountAmount()) * $_rulePct
        );

        if (!$rule->getDiscountQty() || $rule->getDiscountQty() > $qty) {
            $discountPercent = min(100, $item->getDiscountPercent() + $rulePercent);
            $item->setDiscountPercent($discountPercent);
        }

        return $discountData;
    }

    /**
     * @param AbstractItem[] $allItems
     * @return float|int
     */
    protected function _getBaseSumOfItems(array $allItems)
    {
        $baseSum = 0;
        foreach ($allItems as $allItem) {
            $baseSum += $this->validator->getItemBasePrice($allItem);
        }

        return $baseSum;
    }
}
