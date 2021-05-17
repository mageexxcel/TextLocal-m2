<?php
namespace Excellence\Sms\Model;
class Verification extends \Magento\Framework\Model\AbstractModel implements VerificationInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'excellence_sms_verification';

    public function __construct(
        \Magento\Framework\Model\Context $context, 
        \Magento\Framework\Registry $registry,
        \Excellence\Sms\Model\ResourceModel\Verification $resource, 
        \Excellence\Sms\Model\ResourceModel\Verification\Collection $resourceCollection, 
        \Magento\Sales\Model\OrderFactory $OrderFactory,
        array $data = []
    ) 
    {
        $this->OrderFactory = $OrderFactory;
        parent::__construct(
            $context, $registry, $resource, $resourceCollection, $data
        );
    }

    protected function _construct()
    {
        $this->_init('Excellence\Sms\Model\ResourceModel\Verification');
    }
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
    public function setVerificationData($orderId,$verification_mobile) {
        $this->setOrderId($orderId);
        $this->setMobileNo($verification_mobile);
        $this->save();
    }
    public function getMobileByOrderId($orderId) {
        $data = $this->getCollection()->addFieldToFilter('order_id',$orderId)->getFirstItem();
        $value = $data->getMobileNo();
        return $value;
    }
}
