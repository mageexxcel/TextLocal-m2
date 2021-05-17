<?php
namespace Excellence\Sms\Model\ResourceModel\Template;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Excellence\Sms\Model\Template','Excellence\Sms\Model\ResourceModel\Template');
    }
}
