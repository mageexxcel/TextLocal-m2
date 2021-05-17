<?php
namespace Excellence\Sms\Controller\Index;
class Processotp extends \Magento\Framework\App\Action\Action
{
    
    protected $helper;
    protected $resultPageFactory;
    protected $sms;
    protected $registry;
    protected $session;
    protected $verifysotp;
    protected $messageManager;
    protected $resultJsonFactory;
    protected $resultRedirectFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Excellence\Sms\Helper\Data $helper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Excellence\Sms\Model\Sms $sms,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $context->getMessageManager();
        $this->session = $session;
        $this->registry = $registry;
        $this->sms = $sms;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper= $helper;
        return parent::__construct($context);
    }
    public function execute()
    { 
        $code = '';
        $mobile = $this->getRequest()->getPost('mobile');
        $this->session->setMobile($mobile);
        $uId = __(uniqid());
        $this->session->setUid($uId);
        try
        {
            $code = $this->sms->sendLoginOtp($mobile, $uId);
            $this->session->setCode($code);
            if(!empty($code))
            {
                $this->messageManager->addSuccess(__('OTP has been sent successfully.'));
            }
            else
            {

                $this->messageManager->addError(__('Please try again. Something went wrong!!'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('excellence_sms/index/loginotp/');
                return $resultRedirect;
            }
        }
        catch(\Exception $e)
        {

            $this->messageManager->addError(__('Please Enter Valid Mobile No!'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('excellence_sms/index/loginotp/');
            return $resultRedirect;
        }
    }
}
