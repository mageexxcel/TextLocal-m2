<?php
namespace Excellence\Sms\Block;
class Mystep extends \Magento\Framework\View\Element\Template
{   
    protected $_smsFactory;
    protected $session;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Excellence\Sms\Model\Sms $smsFactory,
        \Magento\Checkout\Model\SessionFactory $session,
        \Excellence\Sms\Helper\Data $coreHelper
    )
    {
        $this->_smsFactory = $smsFactory;
         $this->session = $session;
        parent::__construct($context);
    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }
    public function checkPhoneLoginStatus()
    {
        //$model=$this->smsFactory->create();
       $status=$this->_smsFactory->isPhoneLoginEnabled();
        if($status){
            return true;
        }else {
            return false;
        };       
    } 
    public function getBilling(){
       $checkout = $this->session->create()->getQuote(); 
       $billAddress = $checkout->getBillingAddress();
       $phone= $billAddress->getTelephone();
       return $phone;
    }
}