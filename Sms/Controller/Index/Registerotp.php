<?php
namespace Excellence\Sms\Controller\Index;
class Registerotp extends \Magento\Framework\App\Action\Action
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
        $requestData = array();
        $model = $this->loginFactory->create();
        $resultJson =$this->resultJsonFactory->create();
        $smsModel = $this->_smsFactory->create();
        $code = $smsModel->getCountryCode();
        $post = $this->getRequest()->getPost('post');
        parse_str($post,$requestData);
        $password = $requestData['password'];
        $confirm = $requestData['password_confirmation'];
        if($password != $confirm){
            $this->messageManager->addError(__('Your password and confirm password doesn\'t match.'));
            return $resultJson->setData(['yes' => 0]);
        }
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
    
        $isValid = $this->validate($requestData);

        if($isValid == 1)
        {
            try{
                $isSend = $smsModel->sendRegistrationOtp($to, $uId);
                $this->messageManager->addSuccess(__('SMS has been sent to your mobile no. Please enter the 4 digit code (OTP) to verify'));
                return $resultJson->setData(['yes' => 1]);
            }
            catch(\Exception $e){
                $this->messageManager->addError(__('Cannot send SMS to '.$to.' . Please check the number or try another one.'));
                return $resultJson->setData(['yes' => 0]);
            } 
        }
        else
        {
            return $resultJson->setData(['yes' => 0]);
        }  
    }
    public function validate($data)
    {
        
        $resultJson = $this->resultJsonFactory->create();
        if(array_key_exists("email",$data)){
            if(empty($data['email']) || !(filter_var($data['email'], FILTER_VALIDATE_EMAIL))){
               $this->messageManager->addError(__('Please Enter valid Email.'));
                $error = 0;
                return $error;
            }
        }
        if(array_key_exists("firstname",$data)){
            if(empty($data['firstname'])){
                $this->messageManager->addError(__('Please Enter Your firstname.'));
                $error = 0;
                return $error;
            }
        }
        if(array_key_exists("lastname",$data)){
            if(empty($data['lastname'])){
                $this->messageManager->addError(__('Please Enter Your lasttname.'));
                $error = 0;
                return $error;
            }
        }
        if(array_key_exists("mobile",$data)){
            if(empty($data['mobile'])){
                $this->messageManager->addError(__('Please Enter your valid mobile no'));
                $error = 0;
                return $error;
            }
        }
        if(array_key_exists("password",$data)){
            if(empty($data['password'])){
                $this->messageManager->addError(__('Please Enter your password.'));
                $error = 0;
                return $error;
            }
        }
        if(array_key_exists("password_confirmation",$data)){
            if(empty($data['password_confirmation'])){
                $this->messageManager->addError(__('Please confirm your password.'));
                $error = 0;
                return $error;
            }
        }
        return 1;
    } 
}