<?php
namespace Excellence\Sms\Model\ResourceModel;
class Sms extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sms_verification','sms_id');
    }
}
