<?php
namespace Excellence\Sms\Controller\Rewrite\Customer\Account; 
use Zend\Validator;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator as Valid;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\App\Config\ScopeConfigInterface;
class LoginPost extends \Magento\Customer\Controller\Account\LoginPost
{
    const STATUS = 'sms/advanced_setting/login_control';
    protected $session;
    protected $customerAccountManagement;
    protected $resultPageFactory;
    protected $accountRedirect;
    protected $messageManager;
    protected $loginFactory;
    protected $formKeyValidator;
    private $scopeConfig;

    public function __construct(
           Context $context,
           Session $customerSession,
           \Magento\Framework\View\Result\PageFactory $resultPageFactory,
           \Excellence\Sms\Model\LoginFactory $loginFactory,
           Valid $formKeyValidator,
           CustomerUrl $customerHelperData,
           AccountManagementInterface $customerAccountManagement,
           AccountRedirect $accountRedirect
    )

    {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->formKeyValidator = $formKeyValidator;  
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->customerSession=$customerSession;
        $this->messageManager = $context->getMessageManager();
        $this->loginFactory = $loginFactory;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->accountRedirect = $accountRedirect;
        return parent::__construct($context,$customerSession,$customerAccountManagement,
                                   $customerHelperData,$formKeyValidator,$accountRedirect
        );
    }
     
    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Framework\App\Config\ScopeConfigInterface'
            );
        } else {
            return $this->scopeConfig;
        }
    }
    public function execute()
    { 
       
        $status = $this->getScopeConfig()->getValue(self::STATUS,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    
        if($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) 
        {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        if ($this->getRequest()->isPost()) 
        {
            $login = $this->getRequest()->getPost('login');
            $email=(string)$login['username'];
            $validator = new \Zend\Validator\EmailAddress();
            if (!$validator->isValid($email)) 
            {                
                $model=$this->loginFactory->create();
                $login['username']=$model->getEmailByMobile($login['username']);
            }
            if (!empty($login['username']) && !empty($login['password'])) 
            {  
                try 
                {

                    $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                    $this->session->setCustomerDataAsLoggedIn($customer);
                    $this->session->regenerateId();
                    $redirectUrl = $this->accountRedirect->getRedirect();
                    if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                        //$this->accountRedirect->clearRedirectCookie();
                        $resultRedirect = $this->resultRedirectFactory->create();
                        // URL is checked to be internal in $this->_redirect->success()
                        $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
                        return $resultRedirect;
                    }
                } 
                catch (EmailNotConfirmedException $e)
                {
                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $message = __(
                        'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                } 
                catch (Exception $e) 
                {
                     // PA DSS violation: this exception log can disclose customer password
                    $this->messageManager->addError(
                        __('An unspecified error occurred. Please contact us for assistance.')
                    );
                }catch (AuthenticationException $e) {
                    $message = __('Invalid login or password.');
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('Invalid login or password.'));
                }
            } 
            else 
            {
                $this->messageManager->addError(__('A login and a password are required. Please Check the Login Credentials.'));
        
            }   
        }
        return $this->accountRedirect->getRedirect();
    }
}