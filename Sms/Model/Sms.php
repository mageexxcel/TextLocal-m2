<?php
namespace Excellence\Sms\Model;

class Sms extends \Magento\Framework\Model\AbstractModel implements SmsInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'excellence_sms_sms';
    const MODULE_STATUS = 'sms/advanced_setting/module_status';
    const COUNTRY_CODE = 'sms/sms_settings/country_code';
    const ORDER_VERIFICATION = 'sms/sms_template_settings/order_verification_sms';
    const CUSTOMER_NOTIFICATION = 'sms/advanced_setting/order_update_customer';
    const ADMIN_NOTIFICATION = 'sms/advanced_setting/comment_notification_customer';
    // const ADMIN_NOTIFICATION='sms/advanced_setting/order_update_admin';
    const ORDER_COMM_CUSTOMER = 'sms/advanced_setting/comment_notification_customer';
    const PHONE_LOGIN = 'sms/advanced_setting/login_control';
    const ADMIN_NO = 'sms/admin_sms_settings/admin_no';
    const CUSTOMER_ORDER_TEMP = 'sms/sms_template_settings/order_confirmation_sms';
    const CUSTOMER_ORDER_UPDATE = 'sms/sms_template_settings/order_update_notification';
    const CUSTOMER_ORDER_ONHOLD = 'sms/sms_template_settings/order_onhold_notification';
    const CUSTOMER_ORDER_INVOICED = 'sms/sms_template_settings/order_invoiced_notification';
    const CUSTOMER_ORDER_SHIPPED = 'sms/sms_template_settings/order_shipped_notification';
    const CUSTOMER_ORDER_REFUNDED = 'sms/sms_template_settings/order_refunded_notification';
    const ADMIN_ORDER_CONFIRM = 'sms/sms_template_settings/order_confirmation_admin';
    const ORDER_UPDATE_ADMIN = 'sms/sms_template_settings/order_update_admin';
    const ORDER_COMMENT_CUSTOMER = 'sms/sms_template_settings/order_comments_customer';
    const REGISTER_OTP = 'sms/sms_template_settings/registration_sms';
    const LOGIN_SMS = 'sms/sms_template_settings/login_sms';
    protected $scopeConfig;
    protected $helper;
    protected $moduleManager;
    protected $twilioFactory;
    protected $_validator;
    protected $_storeManager;
    protected $_countryCode;
    protected $_templateFactory;
    protected $plivoFactory;
    protected function _construct()
    {
        $this->_init('Excellence\Sms\Model\ResourceModel\Sms');
    }
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Excellence\Sms\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $validator,
        \Excellence\Sms\Model\Adminhtml\System\Config\Source\Countrycode $countryCode,
        \Excellence\Sms\Model\TemplateFactory $templateFactory
    ) {

        $this->_templateFactory = $templateFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_moduleManager = $moduleManager;
        $this->helper = $helper;
        $this->_validator = $validator;
        $this->_storeManager = $storeManager;
        $this->_countryCode = $countryCode;
        parent::__construct($context, $registry);
    }
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
    public function getSmsProvider()
    {
        $moduleList = $this->helper->getModule();
       
        foreach ($moduleList as $moduleName => $module) {

            if ($moduleName == 'Excellence_Twilio') {
                $moduleOutput = $this->_moduleManager->isOutputEnabled($moduleName);
                $moduleActive = $this->_moduleManager->isEnabled($moduleName);
                if ($moduleActive && $moduleOutput) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $instance = $objectManager->create('Excellence\Twilio\Model\Twilio');
                    return $instance;
                    break;
                }
            } elseif ($moduleName == 'Excellence_Plivo') {
                $moduleOutput = $this->_moduleManager->isOutputEnabled($moduleName);
                $moduleActive = $this->_moduleManager->isEnabled($moduleName);
                if ($moduleActive && $moduleOutput) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $instance = $objectManager->create('Excellence\Plivo\Model\Plivo');
                    return $instance;
                    break;
                }
            } elseif ($moduleName == 'Excellence_Textlocal') {
                $moduleOutput = $this->_moduleManager->isOutputEnabled($moduleName);
                $moduleActive = $this->_moduleManager->isEnabled($moduleName);
                if ($moduleActive && $moduleOutput) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $instance = $objectManager->create('Excellence\Textlocal\Model\Textlocal');
                    return $instance;
                    break;
                }
            } elseif ($moduleName == 'Excellence_Bulksms') {
                $moduleOutput = $this->_moduleManager->isOutputEnabled($moduleName);
                $moduleActive = $this->_moduleManager->isEnabled($moduleName);
                if ($moduleActive && $moduleOutput) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $instance = $objectManager->create('Excellence\Bulksms\Model\Bulksms');
                    return $instance;
                    break;
                }
            } elseif ($moduleName == 'Excellence_MSG91') {
                $moduleOutput = $this->_moduleManager->isOutputEnabled($moduleName);
                $moduleActive = $this->_moduleManager->isEnabled($moduleName);
                if ($moduleActive && $moduleOutput) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $instance = $objectManager->create('Excellence\MSG91\Model\MSG91');
                    return $instance;
                    break;
                }
            }
        }

        return $instance;
    }
    public function getAdminSms($sms_string, $vars)
    {
        $admin_nos = $this->_scopeConfig->getValue(self::ADMIN_NO, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $adminArr = explode(',', $admin_nos);
        $temp = $this->_templateFactory->create();
        for ($i = 0; $i < count($adminArr); $i++) {
            $to = $adminArr[$i];
            $temp->sendTransactionalSms($sms_string, $to, $vars);
        }
    }
    public function getTestSms($user_number)
    {
        $check = $this->getSmsProvider()->testSMS($user_number);
        return $check;
    }
    public function getSms($to, $processedResult)
    {
       
        $this->getSmsProvider()->sendSMS($to, $processedResult);
    }
    private function getAutoNumber()
    {
        return $number = rand(1000, 9999);
    }
    public function isActive()
    {
        $status = $this->_scopeConfig->getValue(self::MODULE_STATUS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $service_twilio = $this->_moduleManager->isEnabled('Excellence_Twilio');
        $service_plivo = $this->_moduleManager->isEnabled('Excellence_Plivo');
        $service_bulksms = $this->_moduleManager->isEnabled('Excellence_Bulksms');
        $service_textlocal = $this->_moduleManager->isEnabled('Excellence_Textlocal');
        $service_msg91 = $this->_moduleManager->isEnabled('Excellence_MSG91');
        if ($status && ($service_twilio || $service_plivo || $service_bulksms || $service_textlocal || $service_msg91)) {
            return 1;
        } else {
            return 0;
        }
    }
    public function setVerificationsCode($quoteId, $number, $uId)
    {
        $this->setVerificationCode($number);
        $this->setQuoteId($quoteId);
        $this->setUniqueId($uId);
        $this->save();
    }
    public function sendVerificationSMS($to, $uId)
    {
        $quoteId = $this->_validator->getQuote()->getId();
        $store = $this->_storeManager->getStore()->getName();
        $number = $this->getAutoNumber();
        $to = $this->helper->getFormattedPhoneNo($to);
        $smsTemplateVariables = array();
        $smsTemplateVariables['store_name'] = $store;
        $smsTemplateVariables['code'] = $number;
        $sms_string = $this->_scopeConfig->getValue(self::ORDER_VERIFICATION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        try {
            $temp = $this->_templateFactory->create();

            $code = $temp->sendTransactionalSms($sms_string, $to, $smsTemplateVariables);
            $this->setVerificationsCode($quoteId, $number, $uId);
            return $code;
        } catch (\Exception $ex) {

            return $ex->getMessage();
        }
    }
    public function sendRegistrationOtp($to, $uId)
    {
        $quoteId = $this->_validator->getQuote()->getId();
        $store = $this->_storeManager->getStore()->getName();
        $number = $this->getAutoNumber();
        $to = $this->helper->getFormattedPhoneNo($to);
        $smsTemplateVariables = array();
        $smsTemplateVariables['store_name'] = $store;
        $smsTemplateVariables['code'] = $number;
        $sms_string = $this->_scopeConfig->getValue(self::REGISTER_OTP, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        try {
            $temp = $this->_templateFactory->create();

            $code = $temp->sendTransactionalSms($sms_string, $to, $smsTemplateVariables);
            $this->setVerificationsCode($quoteId, $number, $uId);
            return $code;
        } catch (\Exception $ex) {

            return $ex->getMessage();
        }
    }
    public function sendLoginOtp($to, $uId)
    {
        $quoteId = $this->_validator->getQuote()->getId();
        $store = $this->_storeManager->getStore()->getName();
        $number = $this->getAutoNumber();
        $to = $this->helper->getFormattedPhoneNo($to);
        $smsTemplateVariables = array();
        $smsTemplateVariables['store_name'] = $store;
        $smsTemplateVariables['code'] = $number;
        $sms_string = $this->_scopeConfig->getValue(self::LOGIN_SMS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        try {
            $temp = $this->_templateFactory->create();

            $code = $temp->sendTransactionalSms($sms_string, $to, $smsTemplateVariables);
            $this->setVerificationsCode($quoteId, $number, $uId);
            return $code;
        } catch (\Exception $ex) {

            return $ex->getMessage();
        }
    }
    public function getCountryCode()
    {
        $configValue = $this->_scopeConfig->getValue(self::COUNTRY_CODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $codeValues = $this->_countryCode->toOptionArray();
        foreach ($codeValues as $codeValue) {
            if ($codeValue['value'] == $configValue) {
                $start = strpos($codeValue['label'], '(');
                $end = strpos($codeValue['label'], ')');
                $value = $end - $start;
                $code = substr($codeValue['label'], $start + 1, $value - 1);
            }
        }

        return $code;
    }
    public function verifySMSCode($code, $uId)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('verification_code', $code);
        $collection->addFieldToFilter('unique_id', $uId);
        return count($collection->getData());
    }
    public function isCustomerNotificationEnabled()
    {
        $customerNotify = $this->_scopeConfig->getValue(self::CUSTOMER_NOTIFICATION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enabled = $this->isActive();
        if ($enabled && $customerNotify) {
            return 1;
        } else {
            return 0;
        }
    }
    public function isAdminNotificationEnabled()
    {
        $adminNotify = $this->_scopeConfig->getValue(self::ADMIN_NOTIFICATION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enabled = $this->isActive();
        if ($enabled && $adminNotify == 1) {
            return 1;
        } else {
            return 0;
        }
    }
    public function isOrderCommentsEnabled()
    {
        $commentsNotify = $this->_scopeConfig->getValue(self::ORDER_COMM_CUSTOMER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $enabled = $this->isActive();
        if ($enabled && $commentsNotify == 1) {
            return 1;
        } else {
            return 0;
        }
    }
    public function isPhoneLoginEnabled()
    {
        $enabled = $this->_scopeConfig->getValue(self::PHONE_LOGIN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $orderverification = $this->_scopeConfig->getValue(self::MODULE_STATUS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($orderverification && $enabled) {
            return true;
        } else {
            return false;
        }
    }
    public function getIdByUniqueId($uniqueId)
    {
        $rows = $this->getCollection()->addFieldToFilter('unique_id', $uniqueId);
        foreach ($rows as $row) {
            if (!empty($row)) {
                $id = $row->getSmsId();
                return $id;
            }
        }
        return 0;
    }
    public function adminNotificationNo()
    {
        $telephone = $this->_scopeConfig->getValue(self::ADMIN_NO, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $numbers = explode(',', $telephone);
        return $numbers;
    }
    public function customerOrderConfirmationTemplate()
    {
        $sms_string = $this->_scopeConfig->getValue(self::CUSTOMER_ORDER_TEMP, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $sms_string;
    }
    public function customerOrderUpdateTemplate()
    {
        $sms_string = $this->_scopeConfig->getValue(self::CUSTOMER_ORDER_UPDATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $sms_string;
    }
    public function customerOrderOnholdTemplate()
    {
        $sms_string = $this->_scopeConfig->getValue(self::CUSTOMER_ORDER_ONHOLD, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $sms_string;
    }
    public function customerOrderInvoicedTemplate()
    {
        $sms_string = $this->_scopeConfig->getValue(self::CUSTOMER_ORDER_INVOICED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $sms_string;
    }
    public function customerOrderShippedTemplate()
    {
        $sms_string = $this->_scopeConfig->getValue(self::CUSTOMER_ORDER_SHIPPED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $sms_string;
    }
    public function customerOrderRefundedTemplate()
    {
        $sms_string = $this->_scopeConfig->getValue(self::CUSTOMER_ORDER_REFUNDED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $sms_string;
    }
    public function adminOrderConfirmationTemplate()
    {
        $sms_string = $this->_scopeConfig->getValue(self::ADMIN_ORDER_CONFIRM, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $sms_string;
    }
    public function adminOrderUpdateTemplate()
    {
        $sms_string = $this->_scopeConfig->getValue(self::ORDER_UPDATE_ADMIN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $sms_string;
    }
    public function orderCommentCustomerTemplate()
    {
        $sms_string = $this->_scopeConfig->getValue(self::ORDER_COMMENT_CUSTOMER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $sms_string;
    }
}
