<?php

namespace Zanui\TieredDiscount\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for tiered discount rule
 *
 * @api
 */
interface RuleInterface extends ExtensibleDataInterface
{
    public const RULE_NAME = 'tdrule';
    public const EXTENSION_CODE = 'tdrules';

    /**
     * Constants defined for keys of data array
     */
    public const KEY_SALESRULE_ID = 'salesrule_id';
    public const KEY_SPENT_X = 'spent_x';
    public const KEY_GET_Y = 'get_y';
    public const KEY_SPENT_W = 'spent_w';
    public const KEY_GET_Z = 'get_z';

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
     * @return float|null
     */
    public function getSpentX();

    /**
     * @param float $spentX
     * @return $this
     */
    public function setSpentX($spentX);

    /**
     * @return float|null
     */
    public function getGetY();

    /**
     * @param float $getY
     * @return $this
     */
    public function setGetY($getY);

    /**
     * @return float|null
     */
    public function getSpentW();

    /**
     * @param float $spentW
     * @return $this
     */
    public function setSpentW($spentW);

    /**
     * @return float|null
     */
    public function getGetZ();

    /**
     * @param float $getZ
     * @return $this
     */
    public function setGetZ($getZ);
}
