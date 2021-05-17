<?php
namespace Excellence\Sms\Controller\Adminhtml\Order;

class AddComment extends \Magento\Sales\Controller\Adminhtml\Order\AddComment
{
    
   

    public function execute()
    {  

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //Load product by product id
        $this->smsFactory = $objectManager->create('Excellence\Sms\Model\Sms');
        $this->request = $objectManager->get('Magento\Framework\App\Request\Http');
        $this->template = $objectManager->create('Excellence\Sms\Model\Template');
        $this->coreData = $objectManager->get('Excellence\Sms\Helper\Data');
       
          $order = $this->_initOrder();
          $store = $order->getStoreName();
          $incrementId = $order->getIncrementId();
   
           $data = $this->getRequest()->getPost('history');
           $comment = trim(strip_tags($data['comment']));
                $smsTemplateVariables = array();
                $smsTemplateVariables['order_id'] = $incrementId;
                $smsTemplateVariables['store_name'] = $store;
                $smsTemplateVariables['comment'] = $comment;
            
                $model = $this->smsFactory;
                $helper = $this->coreData;
                $sms_string = $model->orderCommentCustomerTemplate();

                $telephone = $order->getBillingAddress()->getTelephone();
                $email = $order->getBillingAddress()->getEmail();

                if (trim($telephone) != '') 
                {
                    $flag = 1;
                } 
                else 
                {
                    $flag = 0;
                }


                 if ($flag) 
                    {
                        $to = $helper->getFormattedPhoneNo($telephone);
                        $enabled = $model->isOrderCommentsEnabled();
                        
                         if ($enabled) { 
                           $this->template->sendTransactionalSms($sms_string, $to, $smsTemplateVariables);
                            
                        }
                       
                    } 
            //order comment to admin
                  $numbers = $model->adminNotificationNo();

                   $adminNotify = $model->isAdminNotificationEnabled();
                   if($adminNotify){
                         if ($flag) 
                        { 

                            foreach ($numbers as $number) {  
                                 if ($number != '') { 
                                    $number =   $helper->getFormattedPhoneNo($number);
                                $sms_string = $model->orderCommentCustomerTemplate();
                               
                                $this->template->sendTransactionalSms($sms_string, $number, $smsTemplateVariables);
                           
                            }
                           }
                           
                         } 
                    }         
          

         if ($order) {
            try {
                $data = $this->getRequest()->getPost('history');
                if (empty($data['comment']) && $data['status'] == $order->getDataByKey('status')) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a comment.'));
                }

                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;

                $history = $order->addStatusHistoryComment($data['comment'], $data['status']);
                $history->setIsVisibleOnFront($visible);
                $history->setIsCustomerNotified($notify);
                $history->save();

                $comment = trim(strip_tags($data['comment']));

                $order->save();
                /** @var OrderCommentSender $orderCommentSender */
                $orderCommentSender = $this->_objectManager
                    ->create('Magento\Sales\Model\Order\Email\Sender\OrderCommentSender');

                $orderCommentSender->send($order, $notify, $comment);

                return $this->resultPageFactory->create();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                $response = ['error' => true, 'message' => __('We cannot add order history.')];
            }
            if (is_array($response)) {
                $resultJson = $this->resultJsonFactory->create();
                $resultJson->setData($response);
                return $resultJson;
            }
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
       // return parent::execute();
        
    }
}
