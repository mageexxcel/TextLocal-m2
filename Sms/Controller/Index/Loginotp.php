<?php
namespace Excellence\Sms\Controller\Index;
use Magento\Framework\Data\Form\FormKey\Validator as Valid;
class Loginotp extends \Magento\Framework\App\Action\Action
{
    
    protected $helper;
    protected $resultPageFactory;
    protected $formKeyValidator;
    protected $session;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Excellence\Sms\Helper\Data $helper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $session,
        Valid $formKeyValidator

    )
    {
        $this->session = $session;
        $this->formKeyValidator = $formKeyValidator;  
        $this->resultPageFactory = $resultPageFactory;
        $this->helper= $helper;
        return parent::__construct($context);
    }
    public function execute()
    { 
        return $this->resultPageFactory->create(); 
    } 
}
