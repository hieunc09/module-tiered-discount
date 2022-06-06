<?php

namespace Zanui\TieredDiscount\Plugin\Magento\SalesRule\Model\Rule\Action\Discount;

/**
 * Class CalculatorFactory
 * @package Zanui\TieredDiscount\Plugin
 */
class CalculatorFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $subject
     * @param \Closure $proceed
     * @param string $type
     *
     * @return mixed
     */
    public function aroundCreate(
        \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $subject,
        \Closure $proceed,
        $type
    ) {
        if ($type === 'tiered_discount') {
            return $this->_objectManager->create(\Zanui\TieredDiscount\Model\Rule\Action\Discount\TieredDiscount::class);
        } else {
            return $proceed($type);
        }
    }
}
