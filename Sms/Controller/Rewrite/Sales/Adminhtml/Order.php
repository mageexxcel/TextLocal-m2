<?php
namespace Excellence\Sms\Controller\Rewrite\Sales\Adminhtml;
class Order extends \Magento\Framework\App\Action\Action
{
	protected $smsFactory;
	protected $verification;
	protected $template;
	protected $coreData;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Excellence\Sms\Model\smsFactory $smsFactory,
        \Excellence\Sms\Model\VerificationFactory $verification,
        \Excellence\Sms\Model\TemplateFactory $template,
        \Excellence\Sms\Helper\Data $coreData
    )
    {
    	$this->smsFactory = $smsFactory;
    	$this->verification = $verification;
    	$this->template = $template;
    	$this->coreData = $coreData;
    	$this->messageManager = $context->getMessageManager();
        return parent::__construct($context);
    }
     
    public function execute()
    {
        if ($order = $this->_initOrder()) 
        {
            $store = $order->getStoreName();
            $incrementId = $order->getIncrementId();
            try 
            {
                $response = false;
                $data = $this->getRequest()->getPost('history');
                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;

                $order->addStatusHistoryComment($data['comment'], $data['status'])
                        ->setIsVisibleOnFront($visible)
                        ->setIsCustomerNotified($notify);

                $comment = trim(strip_tags($data['comment']));
                $smsTemplateVariables = array();
                $smsTemplateVariables['order_id'] = $incrementId;
                $smsTemplateVariables['store_name'] = $store;
                $smsTemplateVariables['comment'] = $comment;

                $order->save();
                $order->sendOrderUpdateEmail($notify, $comment);

                $telephone = $order->getBillingAddress()->getTelephone();
                $email = $order->getBillingAddress()->getEmail();

                $model = $this->smsFactory->create();
                $helper = $this->coreHelper->create();
                $sms_string = $model->orderCommentCustomerTemplate();

                $telephone = $this->verification->getMobileByOrderId($incrementId);
                if (trim($telephone) != '') {
                    $flag = 1;
                } else {
                    $flag = 0;
                }


                if ($flag) {
                    $to = $helper->getFormattedPhoneNo($telephone);
                    $enabled = $model->isOrderCommentsEnabled();
                    if ($enabled) {
                        
                        $this->template->sendTransactionalSms($sms_string, $to, $smsTemplateVariables);
                    }
                }

                $this->loadLayout('empty');
                $this->renderLayout();
            } 
            catch (\NoSuchEntityException $e) 
            {
                $this->messageManager->addError(__('This order no longer exists.'));
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                return false;
            } 
            catch (\InputException $e) 
            {
                $this->messageManager->addError(__('This order no longer exists.'));
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                return false;
            }
        }
    } 
}