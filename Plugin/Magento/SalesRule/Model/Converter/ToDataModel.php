<?php

namespace Zanui\TieredDiscount\Plugin\Magento\SalesRule\Model\Converter;

/**
 * Class ToDataModel
 * @package Zanui\TieredDiscount\Plugin\Magento\SalesRule\Model\Converter
 */
class ToDataModel
{
    /**
     * @var \Magento\SalesRule\Api\Data\RuleExtensionFactory
     */
    private $extensionFactory;

    /**
     * @param \Magento\SalesRule\Api\Data\RuleExtensionFactory $extensionFactory
     */
    public function __construct(\Magento\SalesRule\Api\Data\RuleExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param \Magento\SalesRule\Model\Converter\ToDataModel $ruleModel
     * @param $dataModel
     * @return mixed
     */
    public function afterToDataModel(\Magento\SalesRule\Model\Converter\ToDataModel $ruleModel, $dataModel)
    {
        $attributes = $dataModel->getExtensionAttributes();

        if (is_array($attributes)) {
            /** @var \Magento\SalesRule\Api\Data\RuleExtensionInterface $attributes */
            $attributes = $this->extensionFactory->create(['data' => $attributes]);
            $dataModel->setExtensionAttributes($attributes);
        }

        return $dataModel;
    }
}
