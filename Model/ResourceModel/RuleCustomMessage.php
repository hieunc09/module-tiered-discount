<?php

namespace Zanui\TieredDiscount\Model\ResourceModel;

/**
 * Class RuleCustomMessage
 * @package Zanui\TieredDiscount\Model\ResourceModel
 */
class RuleCustomMessage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'salesrule_message';

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'message_id');
    }
}

