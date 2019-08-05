'use strict';

/**
 * Porterbuddy constants
 */
function Porterbuddy() {}
Porterbuddy.CARRIER_CODE = 'cnvporterbuddy';
Porterbuddy.METHOD_EXPRESS = 'express';
Porterbuddy.METHOD_DELIVERY = 'delivery';
Porterbuddy.COOKIE = 'porterbuddy_location';
Porterbuddy.SOURCE_BROWSER = 'browser';
Porterbuddy.SOURCE_IP = 'ip';
Porterbuddy.SOURCE_USER = 'user';




/**
 * Porterbuddy Checkout Widget
 */
window.PorterbuddyWidget = Class.create({
    initialize: function (data) {
        this.$element = jQuery(jQuery('input[name=shipping_method]').closest('form')[0] ||
            jQuery('input[name=shipping_method]').closest('div')[0]);

        this.widgetHtml = data.widgetHtml;
        this.chosenOptionNotAvailableText = data.chosenOptionNotAvailableText;
        this.porterbuddyNotAvailableText = data.porterbuddyNotAvailableText;
        this.$widget = jQuery(this.widgetHtml);
        this.$allRates = this.$element.find('input[name=shipping_method]');
        this.$porterbuddyRates = this.$allRates.filter('[value^="' + Porterbuddy.CARRIER_CODE + '_"]');
        var widgetComponent = this;
        this.$groupRate = this.$widget.find('#s_method_porterbuddy');
        this.$allRates.click(function(e, internal) {
          var $rate = jQuery(this);
          widgetComponent.$groupRate.prop('checked', widgetComponent.isPorterbuddyRate($rate));
          if(!widgetComponent.isPorterbuddyRate($rate)){
            window.pbUnselectDeliveryWindow();
            if(widgetComponent.$selectedRate != null){
              widgetComponent.$selectedRate.checked = false;
              widgetComponent.$selectedRate = null;
            }
          }else{
            widgetComponent.$selectedRate = $rate;
            window.$previousSelectedRate = $rate;
          }
        });
        this.$groupRate.click(function(e, internal) {
          if(widgetComponent.$porterbuddyRates.find(":checked").length === 0){
            if(window.$previousSelectedRate != null ){
              var windowVals = window.$previousSelectedRate.val().split('_');
              window.pbSetSelectedDeliveryWindow({'product': windowVals[1], 'start': windowVals[2], 'end': windowVals[3]});
              window.$previousSelectedRate.trigger('click', true);
            }else{
              window.pbSetSelectedDeliveryWindow(null, true);
              widgetComponent.$porterbuddyRates.eq(0).trigger('click', true);
            }
          }
        });

        if(window.$previousSelectedRate != null){
          this.selectAfterRefresh();
        }

        this.$porterbuddyRates.closest('li').hide();
        this.$widget.insertAfter(this.$porterbuddyRates.last().closest('li'));

    },

    selectAfterRefresh: function(){
      if(window.$previousSelectedRate == null){
        //we shoudn't be here
        return;
      }
      if(this.$porterbuddyRates.length === 0 ){
        //no rates possible
        this.$element[0].scrollIntoView();
        alert(this.porterbuddyNotAvailableText);
        return;
      }

      var option = this.$porterbuddyRates.filter('[value="' + window.$previousSelectedRate.val() + '"]');

      if(option.length === 0){
        //chosen option no longer available
        this.$element[0].scrollIntoView();
        alert(this.chosenOptionNotAvailableText);
        return;
      }
      option.eq(0).trigger('click', true);
    },

    isPorterbuddyRate: function($rate) {
        var exp = new RegExp('^' + Porterbuddy.CARRIER_CODE + '_');
        return exp.test($rate.val());
    },

    destroy: function () {
        if (this.$widget) {
            this.$widget.remove();
            this.$porterbuddyRates.closest('li').show();
            // TODO: unbind events
        }
    }
});
