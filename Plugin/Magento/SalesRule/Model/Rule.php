<?php

namespace Zanui\TieredDiscount\Plugin\Magento\SalesRule\Model;

use Zanui\TieredDiscount\Api\Data\RuleInterface;

/**
 * Class Rule
 * @package Zanui\TieredDiscount\Plugin\Magento\SalesRule\Model
 */
class Rule
{
    /**
     * @var \Zanui\TieredDiscount\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @param \Zanui\TieredDiscount\Model\RuleFactory $ruleFactory
     */
    public function __construct(
        \Zanui\TieredDiscount\Model\RuleFactory $ruleFactory
    ) {
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $subject
     * @param \Magento\SalesRule\Model\Rule $salesRule
     *
     * @return \Magento\SalesRule\Model\Rule
     */
    public function afterLoadPost(\Magento\SalesRule\Model\Rule $subject, $salesRule)
    {
        /** @var array $attributes */
        $attributes = $salesRule->getExtensionAttributes() ?: [];

        if (!isset($attributes[RuleInterface::EXTENSION_CODE])
            || !is_array($attributes[RuleInterface::EXTENSION_CODE])
        ) {
            return $salesRule;
        }

        /** @var RuleInterface $tieredDiscountRule */
        $tieredDiscountRule = $this->ruleFactory->create();
        $tieredDiscountRule->addData($attributes[RuleInterface::EXTENSION_CODE]);

        $attributes[RuleInterface::EXTENSION_CODE] = $tieredDiscountRule;
        $subject->setExtensionAttributes($attributes);

        return $salesRule;
    }
}
