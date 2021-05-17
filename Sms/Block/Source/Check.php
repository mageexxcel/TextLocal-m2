<?php
namespace Excellence\Sms\Block\Source;

use Magento\Framework\Data\Form\Element\AbstractElement;
class Check extends \Magento\Config\Block\System\Config\Form\Field
{
   public function __construct(
       \Magento\Backend\Block\Template\Context $context
   ) {
       parent::__construct($context);
   }
   protected function _getElementHtml(AbstractElement $element)
   {
      $url  = $this->getUrl('excellence/index/TestSms');
     
      $html = "<a target='_blank'  href='#' id =".$element->getHtmlId()."_test style='vertical-align: -webkit-baseline-middle'>Test Module</a>";
      $html .='<div id="popup-mpdal"> 
             <input type="text" name="telephoneNumber" class="input-text admin__control-text" id="telephoneNumber" >
</div>';
      $html .="<script>
    require(
        [
            'jquery',
            'Magento_Ui/js/modal/modal'
        ],
        function(
            $,
            modal
        ) {
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Enter Mobile Number',
                buttons: [{
                    text: $.mage.__('Send Test SMS'),
                    class: '',
                    click: function () {
                      var number = $('#telephoneNumber').val();
                      console.log(number);
                        $.ajax({
                        url: '".$url."',
                        type: 'POST',
                        showLoader: true,
                        data: {number:number},
                        success: function(response) {
                            var json_obj = JSON.parse(response);
                             if(json_obj.yes == true){
                              }
                          location.reload();
                          console.log(json_obj ,'object');
                          console.log(response,'response');
                        }
                       }); 
                       this.closeModal();
                    }
                }]
            };

            var popup = modal(options, $('#popup-mpdal'));
             $( '#sms_sms_settings_test_module_test' ).on( 'click', function(event) {
              event.preventDefault();
             $('#popup-mpdal').modal('openModal');
            });
          
        }
    );
</script>";
      return $html;
   }
}


