<?php

namespace Excellence\Sms\Block;

class Verify extends \Magento\Framework\View\Element\Template

{  
   protected $session;
   protected $coreHelper;
   public function __construct(
       \Magento\Framework\View\Element\Template\Context $context,
       \Magento\Checkout\Model\SessionFactory $session,
       \Excellence\Sms\Helper\Data $coreHelper
   )
   { 
       $this->coreHelper = $coreHelper;
       $this->session = $session;
       parent::__construct($context);
   }
   protected function _prepareLayout()
   {
       parent::_prepareLayout();
   }
   public function getPayment($checkout)
   {
       $paymentMethod = $checkout->getPayment()->getMethodInstance()->getCode();
       $Selected_payment = $this->coreHelper->getActivePayment();
       if (in_array($paymentMethod, $Selected_payment))
       {
           return 1;
       }
       else
       {
           return 0;
       }
   }
   public function getBilling(){
      return $this->session->create()->getQuote(); 
   }
}

