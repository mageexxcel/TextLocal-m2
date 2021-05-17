<?php
namespace Excellence\Sms\Model\ResourceModel;
class Login extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sms_login','sms_id');
    }
}
