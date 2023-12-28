/* Magedelight
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

/* @api */
define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'Magento_Payment/js/model/credit-card-validation/validator',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, mageTemplate, alert, ui, validator, fullScreenLoader) {
    'use strict';


    return function (widget) {

        $.widget('mage.transparent', widget, {
            _preparePaymentData: function (data, ccfields) {
                var preparedata;
                if (this.element.find('[data-container="' + this.options.gateway + '-cc-cvv"]').length) {
                    data[ccfields.cccvv] = this.element.find(
                        '[data-container="' + this.options.gateway + '-cc-cvv"]'
                    ).val();
                }
                preparedata = this._prepareExpDate();
                /* customize code for payfort payment gateway */
                if (this.options.gateway=='md_payfort') {
                    data[ccfields.ccexpdate] = preparedata.year + this.options.dateDelim + preparedata.month;
                } else {
                    data[ccfields.ccexpdate] = preparedata.month + this.options.dateDelim + preparedata.year;
                }

                /* echdo customize code for payfort payment gateway */
                data[ccfields.ccnum] = this.element.find(
                    '[data-container="' + this.options.gateway + '-cc-number"]'
                ).val();

                return data;
            },
        });

        return $.mage.SwatchRenderer;
    }

});

