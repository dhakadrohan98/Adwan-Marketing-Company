/**
* Magedelight
* Copyright (C) 2018 Magedelight <info@magedelight.com>
*
* NOTICE OF LICENSE
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
*
* @category Magedelight
* @package Magedelight_Payfort
* @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/iframe',
        'Magento_Vault/js/view/payment/vault-enabler',
         'Magento_Checkout/js/model/payment/additional-validators',
         'Magento_Checkout/js/model/full-screen-loader',
         'Magento_Checkout/js/action/set-payment-information'
    ],
    function ($, Component,VaultEnabler,additionalValidators,fullScreenLoader,setPaymentInformationAction) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magedelight_Payfort/payment/payfort-form'
            },
            placeOrderHandler: null,
            validateHandler: null,
             initialize: function () {
                this._super();
                this.vaultEnabler = new VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());
                return this;
            },
            /**
             * @param {Function} handler
             */
            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            /**
             * @param {Function} handler
             */
            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            /**
             * @returns {Object}
             */
            context: function () {
                return this;
            },

            /**
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return true;
            },

            /**
             * @returns {String}
             */
            getCode: function () {
                return 'md_payfort';
            },

            /**
             * @returns {Boolean}
             */
            isActive: function () {
                return true;
            },
             /**
             * @returns {String}
             */
            getVaultCode: function () {
               return 'md_payfort_cc_vault';
            },
            /**
            * @returns {Bool}
            */
           isVaultEnabled: function () {
               return this.vaultEnabler.isVaultEnabled();
           },
            placeOrder: function () {
                if (this.validateHandler() && additionalValidators.validate()) {

                    fullScreenLoader.startLoader();

                    this.isPlaceOrderActionAllowed(false);
                    var vaultdata = {
                    };
                    if (this.isVaultEnabled()) {
                        vaultdata['is_active_payment_token_enabler'] = this.vaultEnabler.isActivePaymentTokenEnabler();
                    }
                    vaultdata['device_fingerprint'] = $('#device_fingerprint').val();
                   $.when(
                       setPaymentInformationAction(
                           this.messageContainer,
                           {
                                method: this.getCode(),
                                additional_data:vaultdata
                            }
                       )
                   ).done(this.done.bind(this))
                        .fail(this.fail.bind(this));

                    this.initTimeoutHandler();
                }
            }
        });
    }
);
