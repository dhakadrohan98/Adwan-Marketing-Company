/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_QuickOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define([
    'jquery',
    'ko',
    'uiComponent',
    'Mageplaza_QuickOrder/js/model/qod_item',
    'Magento_Catalog/js/price-utils',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function ($, ko, Component, itemModel, priceUtils, customerData, $t) {
    'use strict';
    var self;
    return Component.extend({
        defaults: {
            template: 'Mageplaza_QuickOrder/qod_item'
        },

        /**
         * init function
         */
        initialize: function () {
            this._super();
            self = this;
            /** check products with Tier price when customer group changes */
            self.checkItems(window.qodConfig.customerGroup);
        },

        /**
         * check Item in list
         */
        checkItems: function (customerGroup) {
            itemModel.checkItems(customerGroup);
        },
        /**
         * get Items
         */
        getItems: function () {
            return itemModel.items();
        },

        /**
         * FormatPrice
         */
        getFormattedPrice: function (price) {
            return priceUtils.formatPrice(price, window.qodConfig.priceFormat);
        },

        /**
         * get Items
         */
        getItemsFromStorage: function () {
            // Retrieve the object from storage
            var retrievedObject = JSON.parse(localStorage.getItem('qodItems'));

            return retrievedObject;
        },

        /**
         * remove Item in list
         */
        removeItem: function (item_id) {
            itemModel.removeItem(item_id);
        },

        /**
         * plus qty Item in list
         */
        plusQty: function (item_id) {
            itemModel.plusQty(item_id);
        },

        /**
         * minus qty Item in list
         */
        minusQty: function (item_id) {
            itemModel.minusQty(item_id);
        },

        /**
         * change options of Item in list
         */
        changeOptions: function (item_id, optionId, event) {
            itemModel.changeOptions(item_id, optionId, event);
        },

        /**
         * change qty Item in list
         */
        changeQty: function (item_id, event) {
            itemModel.changeQty(item_id, event);
        },

        /**
         * double Item in list
         */
        doubleItem: function (item_id) {
            itemModel.doubleItem(item_id);
        },

        /**
         * check show type dom element for item
         */
        checktypeId: function (typeId) {
            var checktype = false;
            if (typeId == 'configurable') {
                checktype = true;
            }

            return checktype;
        },

        /**
         * check type show qty of stock dom element for item
         */
        checkTypeShowQty: function (typeId) {
            var checktype = false;
            if (typeId == 'configurable' || typeId == 'simple') {
                checktype = true;
            }

            return checktype;
        },

        /**
         * check salable qty for magento 2.3 than more
         */
        checkShowSalableQty: function(typeId, qtySalable) {
            var check = false;
            if((typeId == 'configurable' || typeId == 'simple') && qtySalable !== false ){
                check = true;
            }

            return check;
        },

        /**
         * check product out of stock for item
         */
        checkoutofStock: function (outofstock) {
            return outofstock == false ? true : false;
        },

        /**
         * Add item to cart
         */
        addCartAction: function () {
            var sections = ['cart'];
            itemModel.addCartAction();
            customerData.invalidate(sections);
        },

        /**
         * Add item to cart and redirect to checkout process
         */
        checkoutProcessAction: function () {
            var sections = ['cart'];
            itemModel.checkoutProcessAction();
            customerData.invalidate(sections);
        },
        /**
         * Slide down to show detail of product
         * @param item_id
         */
        slideDown: function (item_id) {
            var detail = $("#detail" + item_id),
                visible = detail.is(":hidden"),
                btnShowDetail = $('#btn-show-detail-' + item_id);
            detail.slideToggle();
            if (!visible) {
                btnShowDetail.html('Option details...');
            } else {
                btnShowDetail.html('Hide details');
            }
        },

        /**
         * Check required option
         * @param value
         * @returns {boolean}
         */
        isRequired: function (value) {
            var required = false;
            if (value == 1) {
                required = true;
            }

            return required;
        },

        /**
         * @param item_id
         * @param customOption
         * @param event
         */
        changeCustomOptions: function (item_id, customOption, event) {
            itemModel.changeCustomOptions(item_id, customOption, event);
        },

        /**
         * @returns {string}
         */
        getCustomOptionTemplate: function () {
            return 'Mageplaza_QuickOrder/customOption';
        },

        /**
         * @param customOptions
         * @returns {boolean}
         */
        checkCustomOption: function (customOptions) {
            var checkOption = false;
            if (customOptions.length > 0) {
                checkOption = true;
            }

            return checkOption;
        },

        /**
         * @param optionTypeId
         * @param optionValues
         * @returns {boolean}
         */
        hasChecked: function (optionTypeId, optionValues,value) {
            var checked = false,
                customValue;

            for (var i in optionValues) {
                if (i === value.optionId) {
                    customValue = optionValues[i];
                    break;
                }
            }

            if (typeof customValue == 'string') {
                if (optionTypeId === customValue) {
                    checked = true;
                    // return checked;
                }
                if(value.type === 'radio'){
                    checked = customValue;
                }
            }

            if (Array.isArray(customValue)) {
                $.each(customValue, function (i, optionValue) {
                    if (optionTypeId === optionValue) {
                        checked = true;
                    }
                });

            }

            return checked;
            // else {
            //     return optionTypeId === optionValues;
            // }
        },

        /**
         * check group product
         */
        checkGroup: function (typeId) {
            var checktype = false;
            if (typeId == 'grouped') {
                checktype = true;
            }

            return checktype;
        },

        /**
         * check bundle product
         */
        checkBundle: function (typeId) {
            var checktype = false;
            if (typeId == 'bundle') {
                checktype = true;
            }

            return checktype;
        },

        /**
         * change bundle option
         */
        changeBundle: function (item_id, product, option, even) {

            return itemModel.changeBundle(item_id, product, option, even);
        },

        /**
         * check selected option
         */
        hasCheck: function (option, selectproduct, product) {

            for (var i = 0; i < selectproduct.length; i++) {
                if (selectproduct[i].option_id == option.option_id && option.type == "radio" && selectproduct[i].selection_id == product.selection_id) {
                    return product.selection_id;
                }
                if (selectproduct[i].option_id == option.option_id && option.type == "select" && selectproduct[i].selection_id == product.selection_id) {
                    return true;
                }
                if (selectproduct[i].option_id == option.option_id && option.type == "checkbox" && selectproduct[i].selection_id == product.selection_id) {
                    return true;
                }
                if (selectproduct[i].option_id == option.option_id && option.type == "multi" && selectproduct[i].selection_id == product.selection_id) {
                    return true;
                }
            }

            return false;
        },

        /**
         * format qty
         */
        getFormattedQty: function (qty) {
            return parseInt(qty);
        },

        /**
         * get qty select option
         */
        checkQtybundle: function (bundleSelectProduct, option) {

            for (var i = 0; i < bundleSelectProduct.length; i++) {
                if (bundleSelectProduct[i].option_id == option) {
                    return this.getFormattedQty(bundleSelectProduct[i].selection_qty);
                }

            }

            return 0;
        },

        /**
         * check selection change qty
         */
        checkEnableBundle: function (bundleSelectProduct, option) {
            for (var i = 0; i < bundleSelectProduct.length; i++) {
                if (bundleSelectProduct[i].option_id == option && bundleSelectProduct[i].selection_can_change_qty == 1) {
                    return true;
                }
            }

            return false;
        },

        /**
         * get bundle product
         */
        getBundleProduct: function (bundleSelectProduct, option) {
            for (var i = 0; i < bundleSelectProduct.length; i++) {
                if (bundleSelectProduct[i].option_id == option && bundleSelectProduct[i].selection_can_change_qty == 1) {
                    return bundleSelectProduct[i];
                }
            }

            return false;
        },

        /**
         * check select option
         */
        hasCheckSelect: function (bundleProduct, option) {
            for (var i = 0; i < bundleProduct.length; i++) {
                if (bundleProduct[i].option_id == option) {
                    return false;
                } else {
                    return true;
                }
            }
        },

        /**
         * get template
         */
        getProductTemplate: function () {
            return 'Mageplaza_QuickOrder/qod_group_bundle';
        },

        /**
         * change qty bundle child product
         */
        changeOptionBundleQty: function (item_id, product, event) {
            itemModel.changeOptionBundleQty(item_id, product, event);
        },

        /**
         * change qty Bundle Item in list
         */
        changeBundleQty: function (item_id, event) {
            itemModel.changeBundleQty(item_id, event);
        },

        /**
         * plus qty Bundle Item in list
         */
        plusBundleQty: function (item_id) {
            itemModel.plusBundleQty(item_id);
        },

        /**
         * check tier price Grouped child product
         */
        checkTierPriceGroup: function (tier_price) {
            if (tier_price.length == 0) {
                return false;
            } else {
                return tier_price;
            }
        },

        /**
         * @param item_id
         * @param option
         */
        customOptionValue: function(item_id, option){
            return itemModel.customOptionValue(item_id, option);
        }
    })
});
