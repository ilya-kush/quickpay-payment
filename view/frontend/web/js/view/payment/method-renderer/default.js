/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
define([
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader'
], function (Component,fullScreenLoader) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'HW_QuickPay/payment/form',
        },
        redirectAfterPlaceOrder: false,

        /**
         *
         */
        afterPlaceOrder: function () {
            fullScreenLoader.startLoader();
            window.location.replace(window.checkoutConfig.payment[this.getCode()].redirect_url);
        },

        /**
         *
         * @returns {*}
         */
        getLogo: function () {
            return window.checkoutConfig.payment[this.getCode()].logo;
        },
    });
});
