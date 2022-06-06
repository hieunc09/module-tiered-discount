<?php

namespace Zanui\TieredDiscount\Model\SalesRule;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\SalesRule\Api\Data\RuleInterface as SalesRuleInterface;
use Zanui\TieredDiscount\Api\Data\RuleInterface;
use Zanui\TieredDiscount\Api\Data\RuleInterfaceFactory;
use Zanui\TieredDiscount\Model\ResourceModel\Rule;

/**
 * Class ReadHandler
 * @package Zanui\TieredDiscount\Model\SalesRule
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var Rule
     */
    private $ruleResource;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var RuleInterfaceFactory
     */
    private $tieredDiscountRuleFactory;

    /**
     * @param RuleInterfaceFactory $tieredDiscountRuleFactory
     * @param Rule $ruleResource
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        RuleInterfaceFactory $tieredDiscountRuleFactory,
        Rule $ruleResource,
        MetadataPool $metadataPool
    ) {
        $this->ruleResource = $ruleResource;
        $this->metadataPool = $metadataPool;
        $this->tieredDiscountRuleFactory = $tieredDiscountRuleFactory;
    }

    /**
     * Fill Sales Rule extension attributes with related Special Promotions Rule
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule $entity
     * @param array $arguments
     *
     * @return \Magento\SalesRule\Model\Rule|\Magento\SalesRule\Model\Data\Rule
     *
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        $linkField = $this->metadataPool->getMetadata(SalesRuleInterface::class)->getLinkField();
        $ruleLinkId = $entity->getDataByKey($linkField);

        if ($ruleLinkId) {
            /** @var array $attributes */
            $attributes = $entity->getExtensionAttributes() ?: [];
            $tieredDiscountRule = $this->tieredDiscountRuleFactory->create();
            $this->ruleResource->load($tieredDiscountRule, $ruleLinkId, RuleInterface::KEY_SALESRULE_ID);
            $attributes[RuleInterface::EXTENSION_CODE] = $tieredDiscountRule;
            $entity->setData(RuleInterface::RULE_NAME, $tieredDiscountRule);
            $entity->setExtensionAttributes($attributes);
        }

        return $entity;
    }
}
