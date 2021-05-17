<?php
namespace Excellence\Sms\Block;
class Otp extends \Magento\Framework\View\Element\Template
{   
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
    )
    { 
        parent::__construct($context);
    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }
    public function getSmsUrl($phone)
    {

        return $this->_urlBuilder->getUrl("excellence_sms/index/testsms/", array('phone' => $phone));
    }
    public function getPostActionUrl()
    {
        return $this->_urlBuilder->getUrl("excellence_sms/index/loginotp/");
    }
    public function getProcessUrl(){
        return $this->_urlBuilder->getUrl("excellence_sms/index/processotp/");
    }
    public function getOtpVerifyUrl(){
        return $this->_urlBuilder->getUrl("excellence_sms/index/verifyotp/");
    }
    public function getRegistrationPostUrl(){
        return $this->_urlBuilder->getUrl("excellence_sms/customer/account/createpost/");
    }
}