<?php

namespace Zanui\TieredDiscount\Model;

use Zanui\TieredDiscount\Api\RuleProviderInterface;
use Zanui\TieredDiscount\Model\ResourceModel\Rule as RuleResource;
use Zanui\TieredDiscount\Api\Data\RuleInterface;

class RuleProvider implements RuleProviderInterface
{
    /**
     * @var RuleResource
     */
    private $ruleResource;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var array [SalesRule_id => SpecialPromotions_Rule]
     */
    private $storage = [];

    public function __construct(
        RuleResource $ruleResource,
        RuleFactory $ruleFactory
    ) {
        $this->ruleResource = $ruleResource;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * @param int $ruleId
     * @return \Zanui\TieredDiscount\Model\Rule
     */
    public function getTieredDiscountRuleByRuleId($ruleId)
    {
        if (!isset($this->storage[$ruleId])) {
            $rule = $this->ruleFactory->create();
            $this->ruleResource->load($rule, $ruleId, RuleInterface::KEY_SALESRULE_ID);
            $this->storage[$ruleId] = $rule;
        }

        return $this->storage[$ruleId];
    }
}
