<?php
namespace Excellence\Sms\Helper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleListInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_moduleList;
    protected $_moduleManager;
    protected $_smsFactory;
    protected $_scopeConfig;
    protected $address;
    protected $resource;
    public function __construct(
           Context $context,
           ModuleListInterface $moduleList,
           \Excellence\Sms\Model\SmsFactory $smsFactory,
           \Magento\Customer\Api\AddressRepositoryInterface $address,
           \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->resource = $resource;
        $this->address = $address;
        $this->_moduleList = $moduleList;
        $this->_smsFactory = $smsFactory;
        $this->_scopeConfig = $context->getscopeConfig();
        parent::__construct($context);
    }
    public function getModule()
    {
        $moduleInfo = $this->_moduleList->getAll();
        return $moduleInfo;
    }
    public function getFormattedPhoneNo($telephone) {
        $code = $this->_smsFactory->create()->getCountryCode();
        if(strpos($telephone,$code)!==false){
            $number = $telephone;
        }else{
            $number = $code.$telephone;
        }
        return $number;
    }
    public function getCustomerPhone($customerId){
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('sms_login');
        try{
            $telephone = $connection->fetchOne("SELECT mobile_no FROM " . $table . " WHERE customer_id = ".$customerId);
            return $telephone;
        }
        catch(\Exception $e){
            $e->getMessage();
        }
        
    }
}