<?php

namespace Zanui\TieredDiscount\Model\ResourceModel;

/**
 * Class Rule
 * @package Zanui\TieredDiscount\Model\ResourceModel
 */
class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'tiered_discount_rule';

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }
}
