<?php
namespace Excellence\Sms\Model\Rewrite\Sales;

class Order extends \Magento\Sales\Model\Order
{ 


     public function setState($state)
    { 
        $status = $this->getStatus();

         $this->_eventManager->dispatch('sales_order_status_after', ['order' => $this, 'state' => $state, 'status' => $status]);
        return $this->setData(self::STATE, $state);
    }
   
}
