<?php

namespace Zanui\TieredDiscount\Model\SalesRule;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\SalesRule\Api\Data\RuleInterface as SalesRuleInterface;
use Zanui\TieredDiscount\Api\Data\RuleInterface;
use Zanui\TieredDiscount\Api\Data\RuleInterfaceFactory;
use Zanui\TieredDiscount\Model\ResourceModel\Rule;

/**
 * Class SaveHandler
 * @package Zanui\TieredDiscount\Model\SalesRule
 */
class SaveHandler implements ExtensionInterface
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
    private $ruleFactory;

    /**
     * @param Rule $ruleResource
     * @param MetadataPool $metadataPool
     * @param RuleInterfaceFactory $ruleFactory
     */
    public function __construct(
        Rule $ruleResource,
        MetadataPool $metadataPool,
        RuleInterfaceFactory $ruleFactory
    ) {
        $this->ruleResource = $ruleResource;
        $this->metadataPool = $metadataPool;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * @param $entity
     * @param $arguments
     * @return bool|object
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute($entity, $arguments = [])
    {
        $linkField = $this->metadataPool->getMetadata(SalesRuleInterface::class)->getLinkField();
        $attributes = $entity->getExtensionAttributes() ?: [];

        if (isset($attributes[RuleInterface::EXTENSION_CODE])) {
            $ruleLinkId = $entity->getDataByKey($linkField);
            $inputData = $attributes[RuleInterface::EXTENSION_CODE];
            /** @var \Zanui\TieredDiscount\Model\Rule $tieredDiscountRule */
            $tieredDiscountRule = $this->ruleFactory->create();
            $this->ruleResource->load($tieredDiscountRule, $ruleLinkId, RuleInterface::KEY_SALESRULE_ID);

            if ($inputData instanceof RuleInterface) {
                $tieredDiscountRule->addData($inputData->getData());
            } else {
                $tieredDiscountRule->addData($inputData);
            }

            if ($tieredDiscountRule->getSalesruleId() != $ruleLinkId) {
                $tieredDiscountRule->setId(null);
                $tieredDiscountRule->setSalesruleId($ruleLinkId);
            }

            $this->validateRequiredFields($entity, $tieredDiscountRule);

            $this->ruleResource->save($tieredDiscountRule);
        }

        return $entity;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $entity
     * @param \Zanui\TieredDiscount\Model\Rule $tieredDiscountRule
     * @throws LocalizedException
     */
    private function validateRequiredFields($entity, $tieredDiscountRule)
    {
        if (stripos($entity->getSimpleAction(), 'tiered_discount') !== false) {
            $spentX = $tieredDiscountRule->getSpentX();
            $getY = $tieredDiscountRule->getGetY();
            $spentW = $tieredDiscountRule->getSpentW();
            $getZ = $tieredDiscountRule->getGetZ();

            if (!$spentX || !$getY || !$spentW || !$getZ) {
                throw new LocalizedException(__('Please specify Spent X, Get Y, Spent W and Get Z.'));
            }

            if ($spentW <= $spentX) {
                throw new LocalizedException(__('Spent W must be greater than Spent X.'));
            }
        }
    }
}
