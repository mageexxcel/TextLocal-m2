<?php
namespace Excellence\Sms\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Psr\Log\LoggerInterface;

class CustomerNotify implements ObserverInterface
{
    protected $logger;
    protected $smsFactory;
    protected $verification;
    protected $coreData;
    protected $request;
    protected $template;
    protected $storeManager;
    public function __construct(LoggerInterface $logger,
        \Excellence\Sms\Model\SmsFactory $smsFactory,
        \Magento\Framework\App\Request\Http $request,
        \Excellence\Sms\Model\VerificationFactory $verification,
        \Excellence\Sms\Model\TemplateFactory $template,
        \Excellence\Sms\Helper\Data $coreData,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->verification = $verification;
        $this->template = $template;
        $this->smsFactory = $smsFactory;
        $this->coreData = $coreData;
        $this->request = $request;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $model = $this->smsFactory->create();
        $customerNotify = $model->isCustomerNotificationEnabled();
        $adminNotify = $model->isAdminNotificationEnabled();

        if ($customerNotify || $adminNotify) {
            $oldStatus = $observer->getEvent()->getOrder()->getStatus();
            $state = $observer->getEvent()->getState();
            $order = $observer->getEvent()->getOrder();
            $status = $observer->getEvent()->getStatus();
            if ($status == 'holded') {
                $status = 'hold';
            }
            $new_state = \Magento\Sales\Model\Order::STATE_NEW;

            $verification_model = $this->verification->create();
            $helper = $this->coreData;

            if ($state == $new_state && $oldStatus == '') {
                $newOrderFlag = 1;
            } else {
                $newOrderFlag = 0;
            }

            $newStatus = $order->getConfig()->getStateDefaultStatus($state);

            $email = $observer->getOrder()->getBillingAddress()->getEmail();
            $incrementId = $order->getIncrementId();
            $telephone = $order->getBillingAddress()->getTelephone();
            if (trim($telephone) != '') {
                $flag = 1;
            } else {
                $flag = 0;
            }

            if ($oldStatus != $newStatus) {
                $store = $this->storeManager->getStore()->getFrontendName();
                $items = $order->getAllItems();
                $itemcount = count($items);
                foreach ($items as $itemId => $item) {
                    $date = $item->getCreatedAt();
                }

                $time = date('h:i A', strtotime($date)) . ' ' . date("jS F, Y", strtotime($date));
                $smsTemplateVariables = array();
                $smsTemplateVariables['order_no'] = $incrementId;
                $smsTemplateVariables['time'] = $time;
                $smsTemplateVariables['store_name'] = $store;
                if ($newStatus == 'holded') {
                    $newStatus = 'hold';
                }
                $smsTemplateVariables['order_status'] = $newStatus;
                $to = $helper->getFormattedPhoneNo($telephone);

                if (!$newOrderFlag) {
                    //comment to customer
                    if ($customerNotify) {
                        if ($flag) {

                            if ($newStatus == "hold") {
                                $sms_string = $model->customerOrderOnholdTemplate();
                            } elseif ($newStatus == "processing") {
                                $sms_string = $model->customerOrderInvoicedTemplate();
                            } elseif ($newStatus == "complete") {
                                $sms_string = $model->customerOrderShippedTemplate();
                            } elseif ($newStatus == "closed") {
                                $sms_string = $model->customerOrderRefundedTemplate();
                            } else {
                                $sms_string = $model->customerOrderUpdateTemplate();
                            }

                            $this->template->create()->sendTransactionalSms($sms_string, $to, $smsTemplateVariables);
                        }
                    }
                    // comment to admin
                    $numbers = $model->adminNotificationNo();

                    if ($adminNotify) {
                        if ($flag) {
                            foreach ($numbers as $number) {
                                if ($number != '') {
                                    $sms_string = $model->adminOrderUpdateTemplate();
                                    $number = $helper->getFormattedPhoneNo($number);
                                    $this->template->create()->sendTransactionalSms($sms_string, $number, $smsTemplateVariables);
                                }
                            }

                        }
                    }
                }

            }
        }

    }
}
