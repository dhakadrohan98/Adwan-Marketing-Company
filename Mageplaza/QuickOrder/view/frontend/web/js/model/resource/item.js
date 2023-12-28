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
define(['jquery'], function ($) {
        "use strict";
        return {
            initialize: function () {
                this._super();
                /**
                 * Object fields key.
                 * @type {string[]}
                 */
                this.initFields = [
                    'item_id',
                    'product_id',
                    'name',
                    'sku',
                    'sku_child',
                    'qty',
                    'qtystock',
                    'qty_salable',
                    'price',
                    'imageUrl',
                    'type_id',
                    'product_url',
                    'options',
                    'optionIds',
                    'options_select_value',
                    'options_select_value_id',
                    'super_attribute',
                    'outofstock',
                    'childProduct',
                    'bundleOption',
                    'bundleProduct',
                    'bundleSelectOption',
                    'customerGroup',
                    'tier_price'
                ];
            },
            /**
             * Init data
             * @param data
             */
            init: function (data) {
                var dataPrepare = $.extend({}, data);
                var groupTotal = 0;
                var bundleTotal = 0;
                var ChildTotal = 0;
                dataPrepare.item_id = parseInt(data.item_id) + parseInt($.now());
                if (data.type_id === "grouped") {
                    $.each(data.childProduct, function (index, value) {
                        if (value.tier_price.length > 0) {
                            ChildTotal = parseInt(value.qty) * value.price;
                            for (var i = 0; i < value.tier_price.length; i++) {
                                if (i + 1 < value.tier_price.length) {
                                    if (
                                        value.qty >= value.tier_price[i]['price_qty'] &&
                                        value.qty < value.tier_price[i + 1]['price_qty']
                                    ) {
                                        ChildTotal = value.tier_price[i]['price'] * value.qty;
                                    } else if (value.qty < value.tier_price[0]['price_qty']) {
                                        ChildTotal = parseInt(value.qty) * value.price;
                                    } else if (value.qty > value.tier_price[value.tier_price.length - 1]['price_qty']) {
                                        ChildTotal = parseInt(value.qty) * value.tier_price[value.tier_price.length - 1]['price'];
                                    }
                                } else {
                                    if (value.qty >= value.tier_price[value.tier_price.length - 1]['price_qty']) {
                                        ChildTotal = value.tier_price[value.tier_price.length - 1]['price'] * value.qty;
                                    }
                                    if (value.tier_price[0]['price_qty'] == 1) {
                                        value.total = parseInt(value.qty) * value.tier_price[0]['price'];
                                    }
                                }
                            }
                            groupTotal += ChildTotal;
                        } else {
                            groupTotal += parseInt(value.qty) * value.price;
                        }
                    });
                    dataPrepare.total = groupTotal;
                } else if (data.type_id === "bundle") {
                    $.each(data.bundleProduct, function (index, value) {
                        if (value.is_default == "1") {
                            if (value.tier_price.length > 0) {
                                ChildTotal = parseInt(value.selection_qty) * value.price;
                                for (var i = 0; i < value.tier_price.length; i++) {
                                    if (i + 1 < value.tier_price.length) {
                                        if (
                                            parseInt(value.selection_qty) >= value.tier_price[i]['price_qty'] &&
                                            parseInt(value.selection_qty) < value.tier_price[i + 1]['price_qty']
                                        ) {
                                            ChildTotal = value.tier_price[i]['price'] * parseInt(value.selection_qty);
                                        } else if (parseInt(value.selection_qty) < value.tier_price[0]['price_qty']) {
                                            ChildTotal = parseInt(value.selection_qty) * value.price;
                                        } else if (value.selection_qty > value.tier_price[value.tier_price.length - 1]['price_qty']) {
                                            ChildTotal = parseInt(value.selection_qty) * value.tier_price[value.tier_price.length - 1]['price'];
                                        }
                                    } else {
                                        if (parseInt(value.selection_qty) >= value.tier_price[value.tier_price.length - 1]['price_qty']) {
                                            ChildTotal = value.tier_price[value.tier_price.length - 1]['price'] * parseInt(value.selection_qty);
                                        }
                                        if (value.tier_price[0]['price_qty'] == 1) {
                                            ChildTotal = parseInt(value.selection_qty) * value.tier_price[0]['price'];
                                        }
                                    }
                                }
                                bundleTotal += ChildTotal;
                            } else {
                                bundleTotal += parseInt(value.selection_qty) * value.price;
                            }
                            data.bundleSelectOption.push(value);
                        }
                        var total = bundleTotal * parseInt(data.qty);
                        if (dataPrepare.tier_price.length > 0) {
                            for (var i = 0; i < dataPrepare.tier_price.length; i++) {
                                if (i + 1 < dataPrepare.tier_price.length) {
                                    if (
                                        dataPrepare.qty >= dataPrepare.tier_price[i]['price_qty'] &&
                                        dataPrepare.qty < dataPrepare.tier_price[i + 1]['price_qty']
                                    ) {
                                        dataPrepare.total = total * (100 - dataPrepare.tier_price[i]['price']) / 100;
                                    } else if (dataPrepare.qty < dataPrepare.tier_price[0]['price_qty']) {
                                        dataPrepare.total = total;
                                    } else if (dataPrepare.qty > dataPrepare.tier_price[dataPrepare.tier_price.length - 1]['price_qty']) {
                                        dataPrepare.total = total * (100 - dataPrepare.tier_price[dataPrepare.tier_price.length - 1]['price']) / 100;
                                    }
                                } else {
                                    if (dataPrepare.qty >= dataPrepare.tier_price[dataPrepare.tier_price.length - 1]['price_qty']) {
                                        dataPrepare.total = total * (100 - dataPrepare.tier_price[dataPrepare.tier_price.length - 1]['price']) / 100;
                                    } else {
                                        dataPrepare.total = total;
                                    }
                                }
                            }
                        } else {
                            dataPrepare.total = total;
                        }
                    });
                } else {
                    if (typeof data.tier_price !== "undefined" && data.tier_price.length > 0) {
                        dataPrepare.total = data.qty * data.price;
                        for (var i = 0; i < data.tier_price.length; i++) {
                            if (i + 1 < data.tier_price.length) {
                                if (
                                    data.qty >= data.tier_price[i]['price_qty'] &&
                                    data.qty < data.tier_price[i + 1]['price_qty']
                                ) {
                                    dataPrepare.total = data.tier_price[i]['price'] * data.qty;
                                } else if (data.qty < data.tier_price[0]['price_qty']) {
                                    dataPrepare.total = data.qty * data.price;
                                } else if (data.qty > data.tier_price[data.tier_price.length - 1]['price_qty']) {
                                    dataPrepare.total = data.tier_price[data.tier_price.length - 1]['price'] * data.qty;
                                }
                            } else {
                                if (data.qty >= data.tier_price[data.tier_price.length - 1]['price_qty']) {
                                    dataPrepare.total = data.tier_price[data.tier_price.length - 1]['price'] * data.qty;
                                }
                            }
                        }
                    } else {
                        dataPrepare.total = data.qty * data.price;
                    }
                }

                return dataPrepare;
            }
        };
    }
);