<?php

namespace Zanui\TieredDiscount\Model;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\SalesRule\Api\Data\RuleExtensionFactory;
use Zanui\TieredDiscount\Api\Data\RuleInterface;

/**
 * Class RuleResolver
 * @package Zanui\TieredDiscount\Model
 */
class RuleResolver
{
    /**
     * @var RuleExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var MetadataPool
     */
    private $metadata;

    /**
     * @var RuleProvider
     */
    private $ruleProvider;

    /**
     * @param RuleExtensionFactory $extensionFactory
     * @param MetadataPool $metadata
     * @param RuleProvider $ruleProvider
     */
    public function __construct(
        RuleExtensionFactory $extensionFactory,
        MetadataPool $metadata,
        RuleProvider $ruleProvider
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->metadata = $metadata;
        $this->ruleProvider = $ruleProvider;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $salesRule
     * @return mixed
     * @throws \Exception
     */
    public function getTieredDiscountRule($salesRule)
    {
        if (!$salesRule->hasData(RuleInterface::RULE_NAME)) {
            $extensionAttributes = $salesRule->getExtensionAttributes();
            if (!$extensionAttributes) {
                $extensionAttributes = $this->extensionFactory->create();
            }

            if (!$extensionAttributes->getTdrules()) {
                $tieredDiscountRule = $this->ruleProvider->getTieredDiscountRuleByRuleId($this->getLinkId($salesRule));
                $extensionAttributes->setTdrules($tieredDiscountRule);
            }
            $salesRule->setExtensionAttributes($extensionAttributes);
            $salesRule->setData(RuleInterface::RULE_NAME, $extensionAttributes->getTdrules());
        }

        return $salesRule->getDataByKey(RuleInterface::RULE_NAME);
    }

    /**
     * @param \Magento\Rule\Model\AbstractModel $rule
     *
     * @return int
     *
     * @throws \Exception
     */
    public function getLinkId(\Magento\Rule\Model\AbstractModel $rule)
    {
        return $rule->getDataByKey($this->getLinkField());
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getLinkField()
    {
        return $this->metadata->getMetadata(\Magento\SalesRule\Api\Data\RuleInterface::class)->getLinkField();
    }
}
