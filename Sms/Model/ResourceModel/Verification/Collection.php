<?php
namespace Excellence\Sms\Model\ResourceModel\Verification;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Excellence\Sms\Model\Verification','Excellence\Sms\Model\ResourceModel\Verification');
    }
}
