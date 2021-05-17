<?php
namespace Excellence\Sms\Model;
class Login extends \Magento\Framework\Model\AbstractModel implements LoginInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'excellence_sms_login';

    protected function _construct()
    {
        $this->_init('Excellence\Sms\Model\ResourceModel\Login');
    }
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
    public function getLoginCollection(){
        return $this->getCollection();
    }
    public function getSmsLoginData($mobile){     
        $mainTable = $this->getResource()->getMainTable();
        $connection = $this->getResource()->getConnection();
        $where = $connection->quoteInto('t1.user_group =? AND ','registered').$connection->quoteInto('t1.mobile_no =?',$mobile);
        $query = $connection->select()->from(array('t1'=>$mainTable))->where($where);
        $data = $connection->fetchAll($query);
        return $data;
    }
    public function setLoginData($mobile, $email) {
        $this->setMobileNo($mobile);
        $this->setEmail($email);
        $this->setUserGroup('guest');
        $this->save();
    }
    public function getMobileNoValue($mobile) {
       $data = $this->getCollection()->addFieldToFilter('mobile_no', $mobile)->getFirstItem();
       $telephone = $data->getMobileNo();
       return $telephone;
    }
    public function getEmailValue($email) {
       $data = $this->getCollection()->addFieldToFilter('email', $email)->getFirstItem();
       $emailValue  = $data->getEmail(); 
       return $emailValue;
    }
    public function updateLoginData($mobile, $email) {
        $tableName =$this->getMainTable();
        $where = $this->getConnection()->quoteInto('email =? AND ', $email).$writeConnection->quoteInto('user_group =?', 'guest');
        $query = $this->getConnection()->update($tableName, array('mobile_no'=>$mobile),$where);
    }
    public function getEmailByMobile($mobile) {
        $collection=$this->getCollection();        
        $collection->addFieldToFilter('mobile_no',$mobile);        
        return $collection->getFirstItem()->getEmail();
    }
    public function getIdByEmail($email) {
        $login_rows = $this->getCollection()->addFieldToFilter('user_group','guest')->addFieldtoFilter('email', $email);
        foreach ($login_rows as $login_row) {
            if (!empty($login_row)) {
                $login_id = $login_row->getSmsId();
                return $login_id;
            }
        }
        return 0;
    }
}
