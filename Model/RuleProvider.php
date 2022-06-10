<?php

namespace Zanui\TieredDiscount\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface as Logger;
use Zanui\TieredDiscount\Api\RuleProviderInterface;
use Zanui\TieredDiscount\Model\ResourceModel\Rule as RuleResource;
use Zanui\TieredDiscount\Api\Data\RuleInterface;
use Zanui\TieredDiscount\Api\Data\RuleCustomMessageInterface;
use Zanui\TieredDiscount\Api\Data\RuleCustomMessageInterfaceFactory;
use Zanui\TieredDiscount\Model\ResourceModel\RuleCustomMessage;


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
     * @var RuleCustomMessage
     */
    private $customMessageResource;

    /**
     * @var RuleCustomMessageInterfaceFactory
     */
    private $customMessageFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array [SalesRule_id => SpecialPromotions_Rule]
     */
    private $storage = [];

    /**
     * @param RuleResource $ruleResource
     * @param RuleFactory $ruleFactory
     * @param RuleCustomMessage $customMessageResource
     * @param RuleCustomMessageInterfaceFactory $customMessageFactory
     * @param Logger $logger
     */
    public function __construct(
        RuleResource $ruleResource,
        RuleFactory $ruleFactory,
        RuleCustomMessage $customMessageResource,
        RuleCustomMessageInterfaceFactory $customMessageFactory,
        Logger $logger
    ) {
        $this->ruleResource = $ruleResource;
        $this->ruleFactory = $ruleFactory;
        $this->customMessageResource = $customMessageResource;
        $this->customMessageFactory = $customMessageFactory;
        $this->logger = $logger;
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

    /**
     * @param int $ruleId
     *
     * @return RuleCustomMessageInterface
     * @throws InputException
     */
    public function getCustomMessageByRuleId($ruleId)
    {
        try {
            $customMessageModel = $this->customMessageFactory->create();
            if (null === $ruleId || 0 === $ruleId) {
                throw new InputException(__('Please enter the rule id!'));
            } else {
                $this->customMessageResource->load($customMessageModel, $ruleId, RuleCustomMessageInterface::RULE_ID);
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            throw new InputException(
                __(
                    'Can\'t get the error message. Error: "%message"',
                    ['message' => $e->getMessage()]
                )
            );
        }

        return $customMessageModel;
    }
}
