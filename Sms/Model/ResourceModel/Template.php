<?php
namespace Excellence\Sms\Model\ResourceModel;
class Template extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('excellence_sms_template','excellence_sms_template_id');
    }
}
