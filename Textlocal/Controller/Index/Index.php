<?php
namespace Excellence\Textlocal\Controller\Index;
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_textlocalFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Excellence\Textlocal\Model\TextlocalFactory $textlocalFactory
        )
    {
        $this->_textlocalFactory=$textlocalFactory;
        return parent::__construct($context);
    }
     
    public function execute()
    {
        $model= $this->_textlocalFactory->create();
        $data=$model->intialize();
        
    } 
}
