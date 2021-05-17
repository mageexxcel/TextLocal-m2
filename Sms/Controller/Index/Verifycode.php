<?php
namespace Excellence\Sms\Controller\Index;
class Verifycode extends \Magento\Framework\App\Action\Action
{
  protected $_smsFactory;
  protected $resultPageFactory;
  protected $resultJsonFactory;
  protected $session;
  public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Excellence\Sms\Model\SmsFactory $smsFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $session
  )
  {
    $this->session = $session;
    $this->resultJsonFactory = $resultJsonFactory;
    $this->resultPageFactory = $resultPageFactory;
    $this->_smsFactory = $smsFactory;
    return parent::__construct($context);
  }
  public function execute()
  {
    $resultJson = $this->resultJsonFactory->create();
    $smsModel = $this->_smsFactory->create();
    $code = $smsModel->getCountryCode();
    $post = $this->getRequest()->getPost('post');

    parse_str($post, $data);
    $number = $this->getRequest()->getPost('code');
    $uId = $this->getRequest()->getPost('userId');
    $yes = $smsModel->verifySMSCode($number, $uId);
    if($yes)
    {
      if(!($this->validate($data))){
        return $resultJson->setData(['yes' => 0]);
      }
      $this->messageManager->addSuccess(__('Your Mobile No has been verified successfully.'));
      return $resultJson->setData(['yes' => 1]);
    }
    else
    {
      $this->messageManager->addError(__('You have entered invalid OTP. Please Try again.'));
      return $resultJson->setData(['yes' => 0]);
    }
  } 
  public function validate($data)
  {
    $resultJson = $this->resultJsonFactory->create();
    if(array_key_exists("email",$data)){
        if(empty($data['email']) || !(filter_var($data['email'], FILTER_VALIDATE_EMAIL))){
            $this->messageManager->addError(__('Please Enter valid Email.'));
            return 0;
        }
    }
    if(array_key_exists("firstname",$data)){
      if(empty($data['firstname'])){
        $this->messageManager->addError(__('Please Enter Your firstname.'));
        return 0;
      }
    }
    if(array_key_exists("lastname",$data)){
      if(empty($data['lastname'])){
        $this->messageManager->addError(__('Please Enter Your lasttname.'));
        return 0;
      }
    }
    if(array_key_exists("mobile",$data)){
      if(empty($data['mobile'])){
        $this->messageManager->addError(__('Please Enter your valid mobile no'));
        return 0;
      }
    }
    if(array_key_exists("password",$data)){
      if(empty($data['password']) || $data['password'] != $data['password_confirmation']){
        $this->messageManager->addError(__('Please enter password and confirm it.'));
        return 0;
      }
    }
    if(array_key_exists("password_confirmation",$data)){
      if(empty($data['password_confirmation']) || $data['password'] != $data['password_confirmation']){
        $this->messageManager->addError(__('Please confirm your password.'));
        return 0;
      }
    }
    return 1;
  }
}