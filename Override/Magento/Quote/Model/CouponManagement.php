<?php

namespace Zanui\TieredDiscount\Override\Magento\Quote\Model;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\SalesRule\Api\Data\RuleInterface as SalesRuleInterface;
use Magento\SalesRule\Model\Coupon;
use Zanui\TieredDiscount\Api\Data\RuleCustomMessageInterface;
use Zanui\TieredDiscount\Api\Data\RuleCustomMessageInterfaceFactory;
use Zanui\TieredDiscount\Api\Data\RuleInterface;
use Zanui\TieredDiscount\Model\ResourceModel\RuleCustomMessage;

class CouponManagement extends \Magento\Quote\Model\CouponManagement
{
    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var RuleCustomMessage
     */
    private $customMessageResource;

    /**
     * @var RuleCustomMessageInterfaceFactory
     */
    private $customMessageFactory;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Coupon $coupon,
        RuleCustomMessage $customMessageResource,
        RuleCustomMessageInterfaceFactory $customMessageFactory
    )
    {
        $this->coupon = $coupon;
        $this->customMessageResource = $customMessageResource;
        $this->customMessageFactory = $customMessageFactory;
        parent::__construct($quoteRepository);
    }

    /**
     * @inheritDoc
     */
    public function set($cartId, $couponCode)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }
        if (!$quote->getStoreId()) {
            throw new NoSuchEntityException(__('Cart isn\'t assigned to correct store'));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);

        try {
            $quote->setCouponCode($couponCode);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (LocalizedException $e) {
            throw new CouldNotSaveException(__('The coupon code couldn\'t be applied: ' .$e->getMessage()), $e);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __("The coupon code couldn't be applied. Verify the coupon code and try again."),
                $e
            );
        }
        if ($quote->getCouponCode() != $couponCode) {
            $ruleId = $this->coupon->loadByCode($couponCode)->getRuleId();
            $customMessage = $this->customMessageFactory->create();
            $this->customMessageResource->load($customMessage, $ruleId, RuleCustomMessageInterface::RULE_ID);
            if($customMessage->getMessageId()){
                throw new NoSuchEntityException(__($customMessage->getErrorMessage()));
            }else{
                throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again."));
            }
        }
        return true;
    }
}
