<?php

namespace Zanui\TieredDiscount\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for custom message
 *
 * @api
 */
interface RuleCustomMessageInterface extends ExtensibleDataInterface
{
    public const RULE_ID = 'rule_id';
    public const MESSAGE_ID = 'message_id';
    public const ERROR_MESSAGE = 'error_message';

    /**
     * @return int|null
     */
    public function getSalesruleId();

    /**
     * @param int $salesruleId
     * @return $this
     */
    public function setSalesruleId($salesruleId);

    /**
     * @return int|null
     */
    public function getMessageId();

    /**
     * @param int $messageId
     * @return $this
     */
    public function setMessageId($messageId);

    /**
     * @return string
     */
    public function getErrorMessage();

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function setErrorMessage($errorMessage);

}
