<?php

namespace Zanui\TieredDiscount\Model\SalesRule;

use Magento\Framework\App\RequestInterface;
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
     * @var RequestInterface
     */
    private $request;

    /**
     * @param Rule $ruleResource
     * @param MetadataPool $metadataPool
     * @param RuleInterfaceFactory $ruleFactory
     * @param RequestInterface $request
     */
    public function __construct(
        Rule $ruleResource,
        MetadataPool $metadataPool,
        RuleInterfaceFactory $ruleFactory,
        RequestInterface $request
    ) {
        $this->ruleResource = $ruleResource;
        $this->metadataPool = $metadataPool;
        $this->ruleFactory = $ruleFactory;
        $this->request = $request;
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
            if (!$tieredDiscountRule->getSpentX() || !$tieredDiscountRule->getGetY()
                || !$tieredDiscountRule->getSpentW() || !$tieredDiscountRule->getGetZ()) {
                throw new LocalizedException(__('Please specify Spent X, Get Y, Spent W and Get Z.'));
            }
        }
    }
}
