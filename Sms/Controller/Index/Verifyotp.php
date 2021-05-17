<?php
namespace Excellence\Sms\Controller\Index;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Api\AccountManagementInterface;

class Verifyotp extends \Magento\Framework\App\Action\Action
{
    
    protected $helper;
    protected $resultPageFactory;
    protected $sms;
    protected $session;
    protected $login;
    protected $accountRedirect;
    protected $customerAccountManagement;
    protected $customerModel;
    protected $storeManager;
    protected $messageManager;
    protected $resultRedirectFactory;
    protected $resultJsonFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Excellence\Sms\Helper\Data $helper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Excellence\Sms\Model\Sms $sms,
        \Magento\Customer\Model\Session $session,
        \Excellence\Sms\Model\Login $login,
        AccountManagementInterface $customerAccountManagement,
        AccountRedirect $accountRedirect,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $context->getMessageManager();
        $this->storeManager = $storeManager;
        $this->customerModel = $customerModel;
        $this->login = $login;
        $this->session = $session;
        $this->sms = $sms;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper= $helper;
        $this->accountRedirect = $accountRedirect;
        $this->customerAccountManagement = $customerAccountManagement;
        return parent::__construct($context);
    }
    public function execute()
    {   
        $storeId = $this->storeManager->getDefaultStoreView()->getStoreId();
        $redirectArr = array();
        $resultJson = $this->resultJsonFactory->create();
        $phone = $this->session->getMobile();
        $otp = $this->getRequest()->getPost('otp');
        $uId = $this->session->getUid();
        $code = $this->session->getCode();
        if($otp == $code)
        {
            $response = $this->verifyCustomer($uId,$otp,$phone);
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultJson->setData(['url' => $response]);
        }
        else
        {
            $this->messageManager->addError(__('Invalid OTP.'));
            $redirect_url = $this->storeManager->getStore($storeId)->getUrl('customer/account/login');
            $resultJson->setData(['response' => 'false']);
        }
         
    }
    public function verifyCustomer($uId,$otp,$phone)
    {
        $storeId = $this->storeManager->getDefaultStoreView()->getStoreId();
        try{
            $record = $this->sms->verifySMSCode($otp,$uId);

            if($record == 1)
            {
                $email = $this->login->getEmailByMobile($phone);
                $this->customerModel->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
                $customerData = $this->customerModel->loadByEmail($email);
                $this->session->setCustomerAsLoggedIn($customerData);
                $account_url = $this->storeManager->getStore($storeId)->getUrl('customer/account/');
                $this->messageManager->addSuccess(__('Logged In Successfully.'));
                return $account_url;
            }
            else
            {
                $this->messageManager->addError(__('Invalid OTP.'));
                $login_url = $this->storeManager->getStore($storeId)->getUrl('customer/account/login');
                return false;
            }
        }
        catch(\Exception $e)
        {
            $this->messageManager->addError(__('Either You have Entered Wrong OTP or this no is not associated with any account.'));
            $login_url = $this->storeManager->getStore($storeId)->getUrl('customer/account/login');
            return $login_url;
        }
    }
}
