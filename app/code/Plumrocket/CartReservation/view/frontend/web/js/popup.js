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
 * @package     Plumrocket Cart Reservation v2.x.x
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

define([
    'uiComponent',
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function (Component, $, modal, customerData) {
    'use strict';

    return Component.extend({
        modalOverlayElement: $('#expiration-popup'),

        initialize: function () {
            var self = this;
            self._super();
            var modalClass = 'pcr-modal' + (this.shouldShowOverlay ? '' : ' pcr-no-overlay');

            var options = {
                type: 'popup',
                responsive: true,
                innerSctoll: false,
                title: false,
                modalClass: modalClass,
                buttons: [
                    {
                        text: $.mage.__("Go To Shopping"),
                        click: function () {
                            window.modalContainer.modal("closeModal");
                        }
                    },
                    {
                        class: 'action primary',
                        text: $.mage.__("Go To Checkout"),
                        click: function () {
                            self.redirectCheckout();
                        },
                    }
                ],
                opened: function () {
                    customerData.get('prcr-reminder-popup')().interaction_at = Date.now();
                },
                closed: function () {
                    customerData.get('prcr-reminder-popup')().interaction_at = Date.now();
                }
            };

            modal(options, self.modalOverlayElement);
            window.modalContainer = self.modalOverlayElement;
        },

        redirectCheckout: function () {
            $(location).attr('href', window.checkout.checkoutUrl);
        },
    });
});
