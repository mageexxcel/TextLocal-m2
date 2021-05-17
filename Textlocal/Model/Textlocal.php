<?php
namespace Excellence\Textlocal\Model;
use Magento\Framework\Model\Context;
use Excellence\Textlocal\Helper\Data;
class Textlocal extends  \Magento\Framework\Model\AbstractModel {

    const HASH_KEY='textlocal/textlocal_settings/hash_key';
    const USERNAME='textlocal/textlocal_settings/username';
    const SENDER_NAME='textlocal/textlocal_settings/sender_name';    
    const SMS_ORDER_VERIFICATION='smssection/advancesetting/orderverification';
    public function __construct(Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;     
    }
    
    public function sendSMS($to,$message) {
    $hash_key= $this->scopeConfig->getValue(self::HASH_KEY,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);    
    $username= $this->scopeConfig->getValue(self::USERNAME,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    $sender_name= $this->scopeConfig->getValue(self::SENDER_NAME,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    $mess = urlencode($message);
    $data = "username=".$username."&hash=".$hash_key."&message=".$mess."&sender=".$sender_name."&numbers=".$to;
    $ch = curl_init('http://api.textlocal.in/send/?');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec( $ch );
    return $result;
    }
    
    public function testSMS($user_number) 
    {
        $message =__('This is a Test Message. Your API is Working Fine.');
        try{
            $sms = $this->sendSMS($user_number,$message);
            return 1;
        }
        catch(\Exception $e){
            return 0;
        }
    }
    public function isModuleActive() {
        $smsModule =$this->scopeConfig->getValue(self::SMS_ORDER_VERIFICATION,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $hash_key = $this->scopeConfig->getValue(self::HASH_KEY,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $username = $this->scopeConfig->getValue(self::USERNAME,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($smsModule && trim($hash_key) != '' && trim($username) != '') {
            return true;
        } else {
            return false;
        }
    }

}
