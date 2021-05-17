<?php
namespace Excellence\Sms\Model\ResourceModel\Sms;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Excellence\Sms\Model\Sms','Excellence\Sms\Model\ResourceModel\Sms');
    }
}
