/**
 * Magedelight
* Copyright (C) 2017 Magedelight <info@magedelight.com>
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
* @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
*/
/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'mage/url',
    'Magento_Ui/js/model/messageList',
     'mage/translate'
], function ($, VaultComponent,urlBuilder,globalMessageList, $t) {
    'use strict';
    var configPayfort = window.checkoutConfig.payment.md_payfort;
    return VaultComponent.extend({
        defaults: {
            template: 'Magedelight_Payfort/payment/form',
        },

        /**
         * @returns {String}
         */
        getToken: function () {
            return this.publicHash;
        },
        
        /**
         * Get last 4 digits of card
         * @returns {String}
         */
        getMaskedCard: function () {
            return this.details.maskedCC;
        },

        /**
         * Get expiration date
         * @returns {String}
         */
        getExpirationDate: function () {
            return this.details.expirationDate;
        },

        /**
         * Get card type
         * @returns {String}
         */
        getCardType: function () {
            return this.details.type;
        },
        /**
         * Get card type
         * @returns {String}
         */
        isCvvRequired: function () {
            var details = this.details;
            for (var datakey in details) {
                if(configPayfort.savedPaymentMethod=='recurring'){
                    return false;
                }
            }
            return true;
        },
        
        /**
         * @returns {*}
         */
        getData: function () {
            var data = {
                method: this.getCode()
            };
            var index = this.index;
            var cvvid = this.index + '_cvv';
            var cvvval = $('#'+cvvid).val();
            data['additional_data'] = {};
            data['additional_data']['public_hash'] = this.getToken();
            data['additional_data']['cvv'] = cvvval;
            data['additional_data']['device_fingerprint'] = $('#device_fingerprint').val();
            return data;
        },
        preparePayfortPayment: function () {
            var payfortconfig = window.checkoutConfig.payment.md_payfort;
            var threedsecure = payfortconfig.threedacitve;
            var cctype = this.getCardType();

            if (threedsecure && cctype!="MADA") {
                var cvvid = this.index + '_cvv';
                if (this.isCvvRequired()) {
                    if ($('#'+cvvid).valid()) {
                        var threedcheckUrl = urlBuilder.build('payfort/MerchantPage/ThreedRequest');
                        $.ajax(''+threedcheckUrl+'?isAjax=true',{
                                    method: 'POST',
                                    data: {paymentdata: this.getData()},
                                    dataType: 'html',
                                    showLoader: true,
                                    complete: function (responseresult) {
                                        var result = $.parseJSON(responseresult.responseText);
                                        if (result.success == true) {
                                            var response = result.md_payfort;
                                            console.log(response);
                                            var threedurl = response['3ds_url'];
                                            window.location.replace(threedurl);
                                        } else {
                                            globalMessageList.addErrorMessage({
                                                message: $t('An error occurred on the server. Please try to place the order again.')
                                            });
                                        }
                                    }
                                });
                    }
                } else {
                    var threedcheckUrl = urlBuilder.build('payfort/MerchantPage/ThreedRequest');
                    $.ajax(''+threedcheckUrl+'?isAjax=true',{
                                method: 'POST',
                                data: {paymentdata: this.getData()},
                                dataType: 'html',
                                showLoader: true,
                                complete: function (responseresult) {
                                    var result = $.parseJSON(responseresult.responseText);
                                    if (result.success == true) {
                                        var response = result.md_payfort;
                                        console.log(response);
                                        var threedurl = response['3ds_url'];
                                        window.location.replace(threedurl);
                                    } else {
                                        globalMessageList.addErrorMessage({
                                            message: $t('An error occurred on the server. Please try to place the order again.')
                                        });
                                    }
                                }
                            });
                }
            } else {
                    var cvvid = this.index + '_cvv';
                    if (this.isCvvRequired()) {
                        if ($('#'+cvvid).valid()) {
                            this.placeOrder();
                        }
                    } else {
                        this.placeOrder();
                    }
            }
        }

    });
});
