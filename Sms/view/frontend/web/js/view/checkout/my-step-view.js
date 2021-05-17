define(
    [   
        'ko',
        'jquery',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/alert'
        
    ],
    function(
        ko,
        $,
        Component,
        _,
        stepNavigator,
        alert
    ) {
        'use strict';
        var telephone = window.customerPhoneno;
        var number;
        var sendUrl = window.sendsmsUrl;
        var verifyUrl = window.verifyUrl;
        var enable = window.enable;
        return Component.extend({
            defaults: {
                template: 'Excellence_Sms/checkout/mystep'
            },
            isVisible: ko.observable(false),
            initialize: function() {
                this._super();
                if(enable){
                  stepNavigator.registerStep(
                    'verification',
                    'verification',
                    'Verification',
                    this.isVisible,
                    _.bind(this.navigate, this),
                    15
                  );
                }
                return this;
                
            },
            navigate: function() {
                var self = this;
                self.isVisible(true);
            },
             number : ko.observable(window.customerPhoneno),
             uid : ko.observable(window.uid),
             code : ko.observable(),
            navigateToNextStep: function() {
                var self = this;
                jQuery.ajax({
                url: verifyUrl,
                type: "POST",
                showLoader: true,
                data: {userId:uid , code:self.code()},
                success: function(response){
                    if(response['yes']){
                      stepNavigator.next();
                    }
                    else{
                      alert({
                        title: 'Alert',
                        content: 'You have entered invalid OTP.',
                        actions: {
                          always: function(){}
                        }
                      });
                    }
                  }
                }); 
            },

            proceedToNext: function(){
              stepNavigator.next();
            },

            getotpformnumber: function(){
                var self = this;
                var phoneNumber = $("#email-otp").val();
                var filter = /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
                if (phoneNumber.match(filter)) {
                  if(phoneNumber.length==10){
                       var validate = true;
                  } else {
                    alert({
                      title: 'Alert',
                      content: 'Please enter 10 digit mobile number.',
                      actions: {
                        always: function(){}
                      }
                    });
                    var validate = false;
                  }
                }
                else {
                  alert({
                    title: 'Alert',
                    content: 'Please enter a valid mobile number.',
                    actions: {
                      always: function(){}
                    }
                  });
                  var validate = false;
                }
             
              if(validate){
                jQuery.ajax({
                url: sendUrl,
                showLoader: true,
                type: "POST",
                data: {userId:uid , number:phoneNumber},
                success: function(response) {
                      if(response['yes']){
                        alert({
                          title: 'Alert',
                          content: 'Verification SMS Sent! Please enter code below.',
                          actions: {
                            always: function(){}
                          }
                        });
                    
                        jQuery('#div-sms-verify-con').hide();
                        jQuery('#checkout-step-new').css('display','block');
                      } 
                      else 
                      {
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

            },
         
            
        });
    
    }
);