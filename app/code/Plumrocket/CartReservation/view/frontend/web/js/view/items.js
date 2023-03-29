/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_CartReservation
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

define([
    'ko',
    'uiComponent',
    'Magento_Catalog/js/price-utils',
    'Magento_Customer/js/customer-data',
    'domReady!'
], function (ko, Component, priceUtils, customerData) {
    'use strict';

    return Component.extend({
        cart: customerData.get('cart'),

        initialize: function () {
            this._super();
        },

        getSubtotal: function () {
            return this.cart.subtotal;
        },

        getItems: function () {
            var cartItems = this.cart().items;

            if (cartItems.length) {
                var itemsToDisplay = this.getMaxItemsToDIsplay();
                return cartItems.slice(0, itemsToDisplay);
            }

            return {};
        },

        formatPrice: function (price) {
            return priceUtils.formatPrice(price);
        },

        hasGlobalTimer: function () {
            return !!window.prcrGlobalTimerHtml;
        },

        hasItems: function () {
            return (this.cart() && 'items' in this.cart()) ? this.cart().items.length : 0;
        },

        getGlobalTimerHtml: function () {
            return window.prcrGlobalTimerHtml;
        },

        getMaxItemsToDIsplay: function () {
            return window.checkout ? window.checkout.maxItemsToDisplay : 50;
        },
    });
});
