/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
define([
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ], function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'quickpay_gateway',
                component: 'HW_QuickPay/js/view/payment/method-renderer/default'
            },
            {
                type: 'quickpay_mobilepay',
                component: 'HW_QuickPay/js/view/payment/method-renderer/default'
            },
            {
                type: 'quickpay_klarna',
                component: 'HW_QuickPay/js/view/payment/method-renderer/default'
            },
            {
                type: 'quickpay_swish',
                component: 'HW_QuickPay/js/view/payment/method-renderer/default'
            },
            {
                type: 'quickpay_anyday',
                component: 'HW_QuickPay/js/view/payment/method-renderer/default'
            },
            {
                type: 'quickpay_trustly',
                component: 'HW_QuickPay/js/view/payment/method-renderer/default'
            },
            {
                type: 'quickpay_viabill',
                component: 'HW_QuickPay/js/view/payment/method-renderer/default'
            },
            {
                type: 'quickpay_vipps',
                component: 'HW_QuickPay/js/view/payment/method-renderer/default'
            },
        );
        return Component.extend({});
    });
