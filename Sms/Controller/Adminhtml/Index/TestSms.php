<?php
namespace Excellence\Sms\Controller\Adminhtml\Index;
class TestSms extends \Magento\Framework\App\Action\Action
{
    protected $_smsFactory;
    protected $resultPageFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Excellence\Sms\Model\SmsFactory $smsFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_smsFactory = $smsFactory;
        return parent::__construct($context);
    }
     
    public function execute()
    {
        $user_number = $this->getRequest()->getPost('number');
        $smsModel= $this->_smsFactory->create();
        $code = $smsModel->getCountryCode();
        $to = $code.$user_number;
        $check=$smsModel->getTestSms($to);
        try {
            if ($check) {
              $this->messageManager->addSuccess( __('Test SMS has been sent successfully. Check your phone.') );  
             } else {
                $this->messageManager->addError( __('Cannot send SMS: Either phone number is not valid or API details are incorrect') );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError( __($e->getMessage()) );
        }
        $result = array('yes' => $check);
        return $this->getResponse()->setBody(json_encode($result));
        
    } 
}
