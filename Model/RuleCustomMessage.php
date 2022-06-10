<?php

namespace Zanui\TieredDiscount\Model;

use Zanui\TieredDiscount\Api\Data\RuleCustomMessageInterface;

/**
 * Class RuleCustomMessage
 * @package Zanui\TieredDiscount\Model
 */
class RuleCustomMessage extends \Magento\Framework\Model\AbstractModel implements RuleCustomMessageInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init(ResourceModel\RuleCustomMessage::class);
        $this->setIdFieldName('message_id');
    }

    /**
     * @return int|null
     */
    public function getSalesruleId()
    {
        return $this->_getData(self::RULE_ID);
    }

    /**
     * @param int $salesruleId
     * @return $this
     */
    public function setSalesruleId($salesruleId)
    {
        return $this->setData(self::RULE_ID, $salesruleId);
    }

    /**
     * @return int|null
     */
    public function getMessageId()
    {
        return $this->_getData(self::MESSAGE_ID);
    }

    /**
     * @param int $messageId
     * @return $this
     */
    public function setMessageId($messageId)
    {
        return $this->setData(self::MESSAGE_ID, $messageId);
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_getData(self::ERROR_MESSAGE);
    }

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function setErrorMessage($errorMessage)
    {
        return $this->setData(self::ERROR_MESSAGE, $errorMessage);
    }
}
