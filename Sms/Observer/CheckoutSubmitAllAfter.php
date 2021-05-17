<?php
namespace Excellence\Sms\Observer;
use \Psr\Log\LoggerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
 
 
class CheckoutSubmitAllAfter implements ObserverInterface
{
    protected $logger;
    protected $smsFactory;
    protected $loginFactory;
    protected $verification;
    protected $validator;
    protected $orderFactory;
    protected $helper;
    protected $request;
    protected $session;
    protected $quote;
    protected $template;
    protected $checkSession;
    protected $messageManager;
    protected $storeManager;
    public function __construct(LoggerInterface $logger,\Excellence\Sms\Model\Sms $smsFactory,
    	                        \Excellence\Sms\Model\LoginFactory $loginFactory,
    	                        \Excellence\Sms\Model\Verification $verification,
    	                        \Magento\Checkout\Model\Session\SuccessValidator $validator,
                                \Magento\Sales\Model\OrderFactory $orderFactory,
                                \Excellence\Sms\Helper\Data $helper,
                                \Magento\Framework\App\Request\Http $request,
                                \Magento\Checkout\Model\Session $session,
                                \Magento\Quote\Model\QuoteFactory $quote,
                                \Excellence\Sms\Model\Template $template,
                                \Magento\Checkout\Model\SessionFactory $checkSession,
                                \Magento\Framework\Message\ManagerInterface $messageManager,
                                \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->checkSession = $checkSession;  
        $this->template = $template;
        $this->quote = $quote;
        $this->session = $session;
        $this->request = $request;
        $this->helper= $helper;
        $this->orderFactory = $orderFactory;
        $this->logger = $logger;
        $this->smsFactory= $smsFactory;
        $this->loginFactory = $loginFactory;
        $this->verification = $verification;
        $this->validator = $validator;
    }
 
    public function execute(Observer $observer)
    {
        $customerNotify = $this->smsFactory->isCustomerNotificationEnabled();
          
        if($customerNotify)
        {
            $oldStatus = '';
            $oldStatus = $observer->getEvent()->getOrder()->getData('status');
            $order_id = $observer->getEvent()->getOrder()->getId();
            $state = $observer->getEvent()->getState();
            $order = $observer->getEvent()->getOrder();
            $status = $observer->getEvent()->getStatus();
            $orderModel = $this->orderFactory->create()->load($order_id);
            $new_state = \Magento\Sales\Model\Order::STATE_NEW;
            if ($status === true) {
                $newStatus = $orderModel->getConfig()->getStateDefaultStatus($state);
            } else {
                $newStatus = $status;
            }
    
            $email = $observer->getOrder()->getBillingAddress()->getEmail();
            $incrementId = $order->getIncrementId();
            $shipping = $orderModel->getBillingAddress();
            $telephone = $shipping->getTelephone();
         
            if (trim($telephone) != '') {
                $flag = 0;
            } else {
                $flag = 1;
            }
            $time = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            $date = date('h:i A', strtotime($time)) . ' ' . date("jS F, Y", strtotime($time));
            $store = $this->storeManager->getStore()->getFrontendName();
            $items = $order->getAllItems();
            $currencyCode = $orderModel->getOrderCurrencyCode();
            $grandTotal = $orderModel->getGrandTotal();
            $itemcount = count($items);
            if ($newStatus == 'holded'){
                $newStatus = 'hold';
            }
            $smsTemplateVariables = array();
            $smsTemplateVariables['order_no'] = $incrementId;
            $smsTemplateVariables['time'] = $date;
            $smsTemplateVariables['store_name'] = $store;
            $smsTemplateVariables['order_status'] = $newStatus;
            $smsTemplateVariables['currency'] = $currencyCode;
            $smsTemplateVariables['order_total'] = $grandTotal;
            $to = $this->helper->getFormattedPhoneNo($telephone);
            $sms_string = $this->smsFactory->customerOrderConfirmationTemplate();
            $admin_string = $this->smsFactory->adminOrderConfirmationTemplate();
            $quote_id = $orderModel->getQuoteId();
            $quote = $this->quote->create()->load($quote_id);
            $this->smsFactory->getAdminSms($admin_string,$smsTemplateVariables);
            $this->template->sendTransactionalSms($sms_string, $to, $smsTemplateVariables);
           
        }
    }
}