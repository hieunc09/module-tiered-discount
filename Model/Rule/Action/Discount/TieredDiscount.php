<?php

namespace Zanui\TieredDiscount\Model\Rule\Action\Discount;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\SalesRule\Helper\CartFixedDiscount;
use Magento\SalesRule\Model\DeltaPriceRound;
use Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Validator;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Zanui\TieredDiscount\Api\Data\RuleInterface;
use Zanui\TieredDiscount\Model\RuleResolver;

/**
 * Class TieredDiscount
 * @package Zanui\TieredDiscount\Model\Rule\Action\Discount
 */
class TieredDiscount extends AbstractDiscount
{
    /**
     * @var RuleResolver
     */
    protected $ruleResolver;
    /**
     * Store information about addresses which cart fixed rule applied for
     *
     * @var int[]
     */
    protected $_cartFixedRuleUsedForAddress = [];

    /**
     * @var DeltaPriceRound
     */
    private $deltaPriceRound;

    /**
     * @var CartFixedDiscount
     */
    private $cartFixedDiscountHelper;

    /**
     * @var string
     */
    private static $discountType = 'CartFixed';

    /**
     * @param Validator $validator
     * @param DataFactory $discountDataFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param DeltaPriceRound $deltaPriceRound
     * @param RuleResolver $ruleResolver
     * @param CartFixedDiscount|null $cartFixedDiscount
     */
    public function __construct(
        Validator $validator,
        DataFactory $discountDataFactory,
        PriceCurrencyInterface $priceCurrency,
        DeltaPriceRound $deltaPriceRound,
        RuleResolver $ruleResolver,
        ?CartFixedDiscount $cartFixedDiscount = null
    ) {
        parent::__construct($validator, $discountDataFactory, $priceCurrency);
        $this->deltaPriceRound = $deltaPriceRound;
        $this->ruleResolver = $ruleResolver;
        $this->cartFixedDiscountHelper = $cartFixedDiscount ?:
            ObjectManager::getInstance()->get(CartFixedDiscount::class);
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

        $ruleTotals = $this->validator->getRuleItemTotalsInfo($rule->getId());
        $baseRuleTotals = $ruleTotals['base_items_price'] ?? 0.0;

        if ($baseRuleTotals < $spentX) {
            return $discountData;
        } elseif ($baseRuleTotals > $spentX && $baseRuleTotals < $spentW) {
            $discountData = $this->_calculate($rule, $item, $qty, $getY, $ruleTotals);
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
     * @param $ruleTotals
     * @return Data
     */
    protected function _calculate($rule, $item, $qty, $getY, $ruleTotals)
    {
        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();

        $baseRuleTotals = $ruleTotals['base_items_price'] ?? 0.0;
        $baseRuleTotalsDiscount = $ruleTotals['base_items_discount_amount'] ?? 0.0;
        $ruleItemsCount = $ruleTotals['items_count'] ?? 0;

        $address = $item->getAddress();
        $quote = $item->getQuote();
        $shippingMethod = $address->getShippingMethod();
        $isAppliedToShipping = (int)$rule->getApplyToShipping();
        $ruleDiscount = (float)$getY;

        $isMultiShipping = $this->cartFixedDiscountHelper->checkMultiShippingQuote($quote);
        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);
        $baseItemDiscountAmount = (float)$item->getBaseDiscountAmount();

        $cartRules = $quote->getCartFixedRules();
        if (!isset($cartRules[$rule->getId()])) {
            $cartRules[$rule->getId()] = $getY;
        }
        $availableDiscountAmount = (float)$cartRules[$rule->getId()];
        $discountType = self::$discountType . $rule->getId();

        if ($availableDiscountAmount > 0) {
            $store = $quote->getStore();
            $baseRuleTotals = $shippingMethod ?
                $this->cartFixedDiscountHelper
                    ->getBaseRuleTotals(
                        $isAppliedToShipping,
                        $quote,
                        $isMultiShipping,
                        $address,
                        $baseRuleTotals
                    ) : $baseRuleTotals;
            if ($isAppliedToShipping) {
                $baseDiscountAmount = $this->cartFixedDiscountHelper
                    ->getDiscountAmount(
                        $ruleDiscount,
                        $qty,
                        $baseItemPrice,
                        $baseRuleTotals,
                        $discountType
                    );
            } else {
                $baseDiscountAmount = $this->cartFixedDiscountHelper
                    ->getDiscountedAmountProportionally(
                        $ruleDiscount,
                        $qty,
                        $baseItemPrice,
                        $baseItemDiscountAmount,
                        $baseRuleTotals - $baseRuleTotalsDiscount,
                        $discountType
                    );
            }
            $discountAmount = $this->priceCurrency->convert($baseDiscountAmount, $store);
            $baseDiscountAmount = min($baseItemPrice * $qty, $baseDiscountAmount);
            if ($ruleItemsCount <= 1) {
                $this->deltaPriceRound->reset($discountType);
            } else {
                $this->validator->decrementRuleItemTotalsCount($rule->getId());
            }

            $baseDiscountAmount = $this->priceCurrency->roundPrice($baseDiscountAmount);

            $availableDiscountAmount = $this->cartFixedDiscountHelper
                ->getAvailableDiscountAmount(
                    $rule,
                    $quote,
                    $isMultiShipping,
                    $cartRules,
                    $baseDiscountAmount,
                    $availableDiscountAmount
                );

            $cartRules[$rule->getId()] = $availableDiscountAmount;
            if ($isAppliedToShipping &&
                $isMultiShipping &&
                $ruleTotals['items_count'] <= 1) {
                $estimatedShippingAmount = (float)$address->getBaseShippingInclTax();
                $shippingDiscountAmount = $this->cartFixedDiscountHelper->
                getShippingDiscountAmount(
                    $rule,
                    $estimatedShippingAmount,
                    $baseRuleTotals
                );
                $cartRules[$rule->getId()] -= $shippingDiscountAmount;
                if ($cartRules[$rule->getId()] < 0.0) {
                    $baseDiscountAmount += $cartRules[$rule->getId()];
                    $discountAmount += $cartRules[$rule->getId()];
                }
            }
            if ($availableDiscountAmount <= 0) {
                $this->deltaPriceRound->reset($discountType);
            }

            $discountData->setAmount($this->priceCurrency->roundPrice(min($itemPrice * $qty, $discountAmount)));
            $discountData->setBaseAmount($baseDiscountAmount);
            $discountData->setOriginalAmount(min($itemOriginalPrice * $qty, $discountAmount));
            $discountData->setBaseOriginalAmount($this->priceCurrency->roundPrice($baseItemOriginalPrice));
        }
        $quote->setCartFixedRules($cartRules);

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
}
