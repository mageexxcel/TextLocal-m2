<?php
namespace Excellence\Sms\Controller\Index;
class Sendsms extends \Magento\Framework\App\Action\Action
{
    protected $_smsFactory;
    protected $resultPageFactory;
    protected $resultJsonFactory;
    protected $session;
    protected $loginFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Excellence\Sms\Model\SmsFactory $smsFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Excellence\Sms\Model\LoginFactory $loginFactory,
        \Magento\Customer\Model\Session $session
    )
    {
        $this->loginFactory = $loginFactory;
        $this->session = $session;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_smsFactory = $smsFactory;
        return parent::__construct($context);
    }
    public function execute()
    {
        $model = $this->loginFactory->create();
        $resultJson =$this->resultJsonFactory->create();
        $smsModel = $this->_smsFactory->create();
        $code = $smsModel->getCountryCode();
        $to = $this->getRequest()->getPost('number');
        $uId = $this->getRequest()->getPost('userId');
        if(!empty($this->getRequest()->getPost('email'))){
            $email = $this->getRequest()->getPost('email');
            $emailData = $model->getEmailValue($email);
            $data = $model->getSmsLoginData($to);
            $mobileData = $model->getMobileNoValue($to);
            if (!empty($mobileData) && !empty($data)) 
            {
                $this->messageManager->addError( __('There is already an account with this Phone Number.') );
                return $resultJson->setData(['yes' => 0]);
            }
            else{
                if(!empty($emailData)){
                    $this->messageManager->addError( __('There is already an account with this Email Address.') );
                    return $resultJson->setData(['yes' => 0]);
                }
            }
        }
        
        $to = $code.$to;
        try{
            $isSend = $smsModel->sendVerificationSMS($to, $uId);
            if($isSend)
            {
                $this->messageManager->addSuccess(__('SMS has been sent to your mobile no. Please enter the 4 digit code (OTP) to verify'));
                return $resultJson->setData(['yes' => 1]);
            }
            else
            {
                $this->messageManager->addError(__('The 4-digit OTP you\'ve entered is invalid. Please try again.'));
                return $resultJson->setData(['yes' => $isSend]);
            }
            return $resultJson->setData(['yes' => 1]);
        }
        catch(\Exception $e)
        {
            $this->messageManager->addError(__('Please Enter Valid Mobile No !'));
            return $resultJson->setData(['yes' => 0]);
        }
    } 
}