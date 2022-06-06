<?php

namespace Zanui\TieredDiscount\Api;

/**
 * Interface for tiered discount rule provider
 *
 * @api
 */
interface RuleProviderInterface
{
    /**
     * @param int $ruleId
     *
     * @return \Zanui\TieredDiscount\Model\Rule
     */
    public function getTieredDiscountRuleByRuleId($ruleId);
}
