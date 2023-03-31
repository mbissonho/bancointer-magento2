/* @api */
define([
    'Magento_Checkout/js/view/payment/default',
    'uiLayout',
    'mageUtils',
    'ko',
    'jquery',
    'underscore'
], function (Component, layout, utils, ko, $, _) {
    'use strict';

    ko.bindingHandlers.cpfCnpjMasked = {

        init: function(element, valueAccessor, allBindingsAccessor) {

            ko.utils.registerEventHandler(element, 'focusout', function() {
                var observable = valueAccessor();
                observable($(element).val());
            });
            ko.utils.registerEventHandler(element, 'keyup', function() {
                var observable = valueAccessor();
                observable($(element).val());
            });
            ko.utils.registerEventHandler(element, 'keydown', function() {
                var observable = valueAccessor();
                observable($(element).val());
            });
        },
        update: function (element, valueAccessor) {
            let value = ko.utils.unwrapObservable(valueAccessor());

            if (value.length <= 14) {
                value=value.replace(/\D/g,"");
                value=value.replace(/(\d{3})(\d)/,"$1.$2");
                value=value.replace(/(\d{3})(\d)/,"$1.$2");
                value=value.replace(/(\d{3})(\d{1,2})$/,"$1-$2");
            }else{
                value=value.replace(/\D/g,"")
                value=value.replace(/^(\d{2})(\d)/,"$1.$2");
                value=value.replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3");
                value=value.replace(/\.(\d{3})(\d)/,".$1/$2");
                value=value.replace(/(\d{4})(\d)/,"$1-$2");
            }

            $(element).val(value);
        }

    };

    return Component.extend({
        defaults: {
            template: 'Mbissonho_BancoInter/payment/boleto'
        },

        customerTaxvat: ko.observable(''),

        initialize: function () {
            this._super();

            this.methodCode = this.item.method;
            this.initializeCustomerTaxvatField();

            return this;
        },

        initializeCustomerTaxvatField: function () {

            try {

                let customerTaxvat = window.checkoutConfig?.quoteData?.customer_taxvat

                if(!_.isEmpty(customerTaxvat)) {
                    this.customerTaxvat(customerTaxvat);
                    return;
                }

                customerTaxvat = window.checkoutConfig?.customerData?.taxvat;

                if(!_.isEmpty(customerTaxvat)) {
                    this.customerTaxvat(customerTaxvat);
                }

            } catch (e) {
                console.warn('Something went wrong on method: initializeCustomerTaxvatField', e);
            }

        },

        getData: function () {

            let data = {
                'method': this.methodCode,
                'additional_data': {
                    'customer_taxvat': this.customerTaxvat()
                }
            };

            return data;
        }
    });
});
