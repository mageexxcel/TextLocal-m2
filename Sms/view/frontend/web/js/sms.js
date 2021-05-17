   var base_url='';
function getUrl(url){
    base_url = url;
}

  require([
    "jquery",
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'mage/translate'
    ], function(jQuery,alert){
       
jQuery(document).ready(function(){
    
    jQuery(document).on('click','.sms-code',function(){
        button = jQuery(this);
        jQuery('#verify-please-wait').css('display', 'block');  
        sendCode();
    });   
    
    jQuery(document).on('click','.sms-verify',function(){
        button = jQuery(this);
        jQuery('#verify-please-wait').css('display', 'block');
        verfiyCode();

    });
    
    jQuery(document).on('click','#register',function(){
        button = jQuery(this);
       phone = jQuery('#mobile_no').val();
       jQuery('.verify-otp').submit(function(e){
            e.preventDefault();
        });
            jQuery.ajax({
            url:base_url+'excellence_sms/index/Registerotp',
            method:'post',
            dataType:'json',
            showLoader: true,
            data:{
                post:jQuery('.verify-otp').serialize(),
                number:phone,
                userId:jQuery('#uId').val(),
                email:jQuery('#email_address').val()
             },
            success:function(data){      
                if(data['yes'] == 1){
                    jQuery('#sms-Vcode').parent().parent().css('display', 'block');
                    jQuery('#create-account').css('display','block');
                    jQuery('#register').css('display','none');
                    jQuery('.sms-phone').hide();
                    jQuery('#sms-Vcode').show();
                    jQuery('#sms-hiddennum').val(phone);
                } else{
                    return false;
                }   
            }   
        });  
    });

    jQuery(document).on('click','#create-account',function(){
        button = jQuery(this);
        uid = jQuery('#uId').val();
       code = jQuery('#sms-Vcode').val();
        jQuery('#verify-otp-form').submit(function(e){
            e.preventDefault();
        });
        jQuery.ajax({
            url:base_url+'excellence_sms/index/verifyCode',
            method:'post',
            dataType:'json',
            showLoader: true,
            data:{
                post:jQuery('#verify-otp-form').serialize(),
                code:code,
                userId:uid,
            },
            success:function(data){      
                if(data['yes'] == 1){
                    jQuery("#verify-otp-form").unbind('submit').submit();
                } 
                else
                {
                    return false;
                }   
            }   
        });
    });
    
    
    function sendCode() {
        var phone = jQuery('#sms-phone').val();
        if (!phone) {
            phone = jQuery('#email_address').val();
        }
        if (!phone) {
            phone = jQuery('#mobile_no').val();
        }
        
        jQuery.ajax({
            url:base_url+'sms/index/sendSms',
            method:'post',
            dataType: 'json',
            data:{
                phone:phone,
                uId:jQuery('#uid').val()
             },
            success:function(data){     
                jQuery('#verify-please-wait').css('display', 'none');           
                if(data['yes']){
                    alert({
                        title: 'Alert',
                        content: 'Verification SMS Sent! Please enter code below.',
                        actions: {
                            always: function(){}
                        }
                    });
                    button.prop('class','button sms-code');
                    jQuery('#div-sms-verify-con').hide();
                    jQuery('#checkout-step-new').show();
                    jQuery('#sms-hiddennum').val(phone);
                } else{
                    alert({
                        title: 'Alert',
                        content: 'There is an error to send verification code.',
                        actions: {
                            always: function(){}
                        }
                    });
                }   
            }
        });
    }
    
    function verfiyCode() {
        jQuery.ajax({
            url:base_url+'sms/index/verifyCode',
            method:'post',
            dataType: 'json',
            showLoader: true,
            data:{
                code:jQuery('#sms-otp').val(),
                uId:jQuery('#uid').val()
             },
            success:function(data){     
                             
                if(data['yes']){
                    jQuery('#verify-please-wait').css('display', 'none'); 
                    jQuery('.btn-checkout').show();
                    button.hide();
                    jQuery('#co-payment-form').append(jQuery('#sms-fields'));
                }
                else{
                    alert({
                        title: 'Alert',
                        content: 'Invalid Verification Code.',
                        actions: {
                            always: function(){}
                        }
                    });
                }
            }
        });
    }
    
    jQuery('body').on('blur','#email_address',function(){
        var email = jQuery("#email_address").val();
        var check = validateEmail(email);
        if (check === 'phone') {
            jQuery('#sms-Vcode').parent().parent().css('display', 'block');
            jQuery('#pass').parent().parent().css('display', 'none');
            sendCode();
        } else {
            jQuery('#sms-Vcode').parent().parent().css('display', 'none');
            jQuery('#pass').parent().parent().css('display', 'block');
        }
    });
    
    function validateEmail(email) { 
        
        var phoneno = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;  
        jQuery('#sms-Vcode').parent().parent().css('display', 'none');
        if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {  
            return 'email'; 
        } else if((email.match(phoneno))) {   
            return 'phone';  
        } 
        return (false); 
    }
});

   });