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
          }
        });
        this.$porterbuddyRates.closest('li').hide();
        this.$widget.insertAfter(this.$porterbuddyRates.last().closest('li'));

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
