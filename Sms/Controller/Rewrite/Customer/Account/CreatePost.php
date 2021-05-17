<?php
namespace Excellence\Sms\Controller\Rewrite\Customer\Account;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Helper\Address;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Customer\Model\Registration;
use Magento\Framework\Escaper;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;
class CreatePost extends \Magento\Customer\Controller\Account\CreatePost
{
    const STATUS = 'sms/advanced_setting/login_control';
    const ENABLE = 'sms/advanced_setting/module_status';
    protected $_loginFactory;
    protected $accountManagement;
    protected $addressHelper;
    protected $formFactory;
    protected $subscriberFactory;
    protected $regionDataFactory;
    protected $addressDataFactory;
    protected $registration;
    protected $customerDataFactory;
    protected $customerUrl;
    protected $escaper;
    protected $urlModel;
    private $accountRedirect;
    protected $customerRepository;
    protected $customerExtractor;
    protected $_smsFactory;
    protected $messageManager;
    protected $session;
    protected $customerSession;
    protected $scopeConfig;
    public function __construct(
        \Excellence\Sms\Model\LoginFactory $loginFactory,
        \Excellence\Sms\Model\SmsFactory $smsFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Context $context,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManagement,
        Address $addressHelper,
        UrlFactory $urlFactory,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        RegionInterfaceFactory $regionDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        CustomerInterfaceFactory $customerDataFactory,
        CustomerUrl $customerUrl,
        Registration $registration,
        Escaper $escaper,
        CustomerExtractor $customerExtractor,
        DataObjectHelper $dataObjectHelper,
        AccountRedirect $accountRedirect,
    )
    {
        $this->_loginFactory = $loginFactory;
        $this->_smsFactory = $smsFactory;
        $this->resultPageFactory = $resultPageFactory;  
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->messageManager = $context->getMessageManager(); 
        $this->session = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->accountManagement = $accountManagement;
        $this->addressHelper = $addressHelper;
        $this->formFactory = $formFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->customerUrl = $customerUrl;
        $this->registration = $registration;
        $this->escaper = $escaper;
        $this->customerExtractor = $customerExtractor;
        $this->urlModel = $urlFactory->create();
        $this->dataObjectHelper = $dataObjectHelper;
        $this->accountRedirect = $accountRedirect;

        return parent::__construct($context,$customerSession,
        $scopeConfig,$storeManager,$customerRepository,
        $accountManagement,$addressHelper,$urlFactory,
        $formFactory,$subscriberFactory,$regionDataFactory,
        $addressDataFactory,$customerDataFactory,$customerUrl,
        $registration,$escaper,$customerExtractor,$dataObjectHelper,$accountRedirect
      
        );
    }
    protected function extractAddress()
    {
        if (!$this->getRequest()->getPost('create_address')) {
            return null;
        }

        $addressForm = $this->formFactory->create('customer_address', 'customer_register_address');
        $allowedAttributes = $addressForm->getAllowedAttributes();

        $addressData = [];

        $regionDataObject = $this->regionDataFactory->create();
        foreach ($allowedAttributes as $attribute){
            $attributeCode = $attribute->getAttributeCode();
            $value = $this->getRequest()->getParam($attributeCode);
            if ($value === null) {
                continue;
            }
            switch ($attributeCode) {
                case 'region_id':
                    $regionDataObject->setRegionId($value);
                    break;
                case 'region':
                    $regionDataObject->setRegion($value);
                    break;
                default:
                    $addressData[$attributeCode] = $value;
            }
        }
        $addressDataObject = $this->addressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $addressData,
            '\Magento\Customer\Api\Data\AddressInterface'
        );
        $addressDataObject->setRegion($regionDataObject);

        $addressDataObject->setIsDefaultBilling(
            $this->getRequest()->getParam('default_billing', false)
        )->setIsDefaultShipping(
            $this->getRequest()->getParam('default_shipping', false)
        );
        return $addressDataObject;
    }
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->session->isLoggedIn() || !$this->registration->isAllowed()){
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        $status=$this->scopeConfig->getValue(self::STATUS,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enable=$this->scopeConfig->getValue(self::ENABLE,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(!$status)
        {
            return parent::execute();
        }
        elseif (!$enable) {
            return parent::execute();
        }
        if (!$this->getRequest()->isPost()){
            $url = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
            $resultRedirect->setUrl($this->_redirect->error($url));
            return $resultRedirect;
        }

        $this->session->regenerateId();
        $model_sms=$this->_smsFactory->create();
        $model = $this->_loginFactory->create();
        $data = $this->getRequest()->getPost();
        $mobile = $this->getRequest()->getParam('mobile');
        $email  = $this->getRequest()->getParam('email');
        $emailData = $model->getEmailValue($email);
        $mobileData = $model->getMobileNoValue($mobile);
        $data = $model->getSmsLoginData($mobile);
        $enabled = $model_sms->isPhoneLoginEnabled();
        if($enabled)
        {
            
            if (!empty($emailData)) 
            {
                   return false;
            } 
            else 
            {
                try 
                {
                    $address = $this->extractAddress();
                    $addresses = $address === null ? [] : [$address];

                    $customer = $this->customerExtractor->extract('customer_account_create', $this->_request);
                    $customer->setAddresses($addresses);

                    $password = $this->getRequest()->getParam('password');
                    $confirmation = $this->getRequest()->getParam('password_confirmation');
                    $redirectUrl = $this->session->getBeforeAuthUrl();

                    $this->checkPasswordConfirmation($password, $confirmation);

                    $customer = $this->accountManagement
                        ->createAccount($customer, $password, $redirectUrl);

                    if ($this->getRequest()->getParam('is_subscribed', false)) {
                        $this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
                    }
                 
                    $this->_eventManager->dispatch(
                        'customer_register_success',
                        ['account_controller' => $this, 'customer' => $customer]
                    );
                    $model->setMobileNo($mobile);
                    $model->setEmail($email);
                    $model->setUserGroup('registered');
                    $model->setCustomerId($customer->getId());
                    $model->setRegisterFlag(1);
                    $model->save();
                    $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
                    if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED){
                        $email = $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());
                        // @codingStandardsIgnoreStart
                        $this->messageManager->addSuccess(
                            __(
                                'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
                                $email
                            )
                        );
                        // @codingStandardsIgnoreEnd
                        $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
                        $resultRedirect->setUrl($this->_redirect->success($url));
                    } 
                    else 
                    {
                        $this->session->setCustomerDataAsLoggedIn($customer);
                        $this->messageManager->addSuccess($this->getSuccessMessage());
                        $requestedRedirect = $this->accountRedirect->getRedirect();
                        
                        if (!$this->scopeConfig->getValue('customer/startup/redirect_dashboard') && $requestedRedirect){
                            $resultRedirect->setUrl($this->_redirect->success($requestedRedirect));
                            // $this->accountRedirect->clearRedirectCookie();
                            return $resultRedirect;
                        }
                        $resultRedirect = $this->accountRedirect->getRedirect();
                    }
                    return $resultRedirect;
                }
                catch (StateException $e)
                {
                    $url = $this->urlModel->getUrl('customer/account/forgotpassword');
                    // @codingStandardsIgnoreStart
                    $message = __(
                        'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
                        $url
                    );
                    // @codingStandardsIgnoreEnd
                    $this->messageManager->addError($message);
                } 
                catch (InputException $e)
                {
                    $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
                    foreach ($e->getErrors() as $error) {
                        $this->messageManager->addError($this->escaper->escapeHtml($error->getMessage()));
                    }
                } 
                catch (LocalizedException $e)
                {
                    $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
                } 
                catch (\Exception $e)
                {
                    $this->messageManager->addException($e, __($e->getMessage()));
                }

                $this->session->setCustomerFormData($this->getRequest()->getPostValue());
                $defaultUrl = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
                $resultRedirect->setUrl($this->_redirect->error($defaultUrl));
                return $resultRedirect;
            }
        } 
        else
        {

            $this->messageManager->addError(__('Please Enable Phone Login From Admin First!'));
        }    
    }
    protected function checkPasswordConfirmation($password, $confirmation)
    {
        if($password != $confirmation)
        {
            throw new InputException(__('Please make sure your passwords match.'));
        }
    }
    protected function getSuccessMessage()
    {
        if ($this->addressHelper->isVatValidationEnabled())
        {
            if ($this->addressHelper->getTaxCalculationAddressType() == Address::TYPE_SHIPPING) 
            {
            // @codingStandardsIgnoreStart
                $message = __(
                'If you are a registered VAT customer, please <a href="%1">click here</a> to enter your shipping address for proper VAT calculation.',
                $this->urlModel->getUrl('customer/address/edit')
                );
            } 
           else 
            {
            // @codingStandardsIgnoreStart
                $message = __(
                    'If you are a registered VAT customer, please <a href="%1">click here</a> to enter your billing address for proper VAT calculation.',
                    $this->urlModel->getUrl('customer/address/edit')
                );
            // @codingStandardsIgnoreEnd
            }
        } 
        else 
        {
            $message = __('Thank you for registering with %1.', $this->storeManager->getStore()->getFrontendName());
        }
        return $message;
    }

}