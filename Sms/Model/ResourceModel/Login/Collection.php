<?php
namespace Excellence\Sms\Model\ResourceModel\Login;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Excellence\Sms\Model\Login','Excellence\Sms\Model\ResourceModel\Login');
    }
}
