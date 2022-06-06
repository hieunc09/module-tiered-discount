<?php

namespace Zanui\TieredDiscount\Model;

use Zanui\TieredDiscount\Api\Data\RuleInterface;

/**
 * Class Rule
 * @package Zanui\TieredDiscount\Model
 */
class Rule extends \Magento\Framework\Model\AbstractModel implements RuleInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init(ResourceModel\Rule::class);
        $this->setIdFieldName('entity_id');
    }

    /**
     * @return int|null
     */
    public function getSalesruleId()
    {
        return $this->_getData(self::KEY_SALESRULE_ID);
    }

    /**
     * @param float $salesruleId
     * @return $this
     */
    public function setSalesruleId($salesruleId)
    {
        $this->setData(self::KEY_SALESRULE_ID, $salesruleId);
        return $this;
    }

    /**
     * @return float|null
     */
    public function getSpentX()
    {
        return $this->_getData(self::KEY_SPENT_X);
    }

    /**
     * @param float $spentX
     * @return $this
     */
    public function setSpentX($spentX)
    {
        $this->setData(self::KEY_SPENT_X, $spentX);
        return $this;
    }

    /**
     * @return float|null
     */
    public function getGetY()
    {
        return $this->_getData(self::KEY_GET_Y);
    }

    /**
     * @param float $getY
     * @return $this
     */
    public function setGetY($getY)
    {
        $this->setData(self::KEY_GET_Y, $getY);
        return $this;
    }

    /**
     * @return float|null
     */
    public function getSpentW()
    {
        return $this->_getData(self::KEY_SPENT_W);
    }

    /**
     * @param float $spentW
     * @return $this
     */
    public function setSpentW($spentW)
    {
        $this->setData(self::KEY_SPENT_W, $spentW);
        return $this;
    }

    /**
     * @return float|null
     */
    public function getGetZ()
    {
        return $this->_getData(self::KEY_GET_Z);
    }

    /**
     * @param float $getZ
     * @return $this
     */
    public function setGetZ($getZ)
    {
        $this->setData(self::KEY_GET_Z, $getZ);
        return $this;
    }
}
