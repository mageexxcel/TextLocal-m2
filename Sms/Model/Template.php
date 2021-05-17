<?php
namespace Excellence\Sms\Model;
class Template extends \Magento\Framework\Model\AbstractModel implements TemplateInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'excellence_sms_template';
    const ORDER_UPDATE_ADMIN='sms/sms_template_settings/order_update_admin';
    protected $_smsFactory;
    protected $inlineTranslation;
    protected $transportBuilder;
    protected $filterProvider;
    public function __construct(\Magento\Framework\Model\Context $context,
                               \Magento\Framework\Registry $registry,
    	                        \Excellence\Sms\Model\SmsFactory $smsFactory,
                                \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
                                \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
                                \Magento\Cms\Model\Template\FilterProvider $filterProvider

    )
    { 
        $this->filterProvider = $filterProvider;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_smsFactory = $smsFactory;
        parent::__construct($context, $registry);
    }
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
    public function sendTransactionalSms($sms_string, $to, $vars) 
    {
        $customernumeber = '+'.$to;
        $this->setSentSuccess(false);
        $processedResult = $this->filterProvider->getBlockFilter()
                             ->setVariables($vars)
                             ->filter($sms_string); 
        $this->_smsFactory->create()->getSms($customernumeber, $processedResult); 
        if(!empty($vars['code']))
        {
            $code = $vars['code'];
            return $code;
        }
        else {
            return true;
        }
    }
   
}
