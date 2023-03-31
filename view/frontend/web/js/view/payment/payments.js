/* @api */
define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'mbissonho_bancointer_boleto',
            component: 'Mbissonho_BancoInter/js/view/payment/method-renderer/boleto-method'
        },
    );

    return Component.extend({});
});
