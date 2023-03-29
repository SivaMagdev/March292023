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
    'jquery',
    'Magento_Customer/js/customer-data',
    'prcrCountdown',
    'mage/cookies',
    'mage/translate',
    'domReady!',
], function ($, customerData) {
    'use strict';

    var configurableOptionsSelector = '[data-role=swatch-options], .product-item-details',
        bundleOptionsSelector = '#product_addtocart_form',
        prevBundleIds = null,
        popupTime;

    /**
     * Events and standard observers
     */
    $(document).on('prcr.countdownInit_before', function (event, $timer, $btn, data) {
        var observer = $timer.data('on-init-before');
        if (observer && window[observer]) {
            window[observer]($timer, $btn, data);
        }
    });

    $(document).on('prcr.countdownInit_after', function (event, $timer, $btn, data) {
        var observer = $timer.data('on-init-after');
        if (observer && window[observer]) {
            window[observer]($timer, $btn, data);
        }
    });

    $(document).on('prcr.countdownInit_expiry', function (event, $timer, $btn, data) {
        var observer = $timer.data('on-expiry');
        if (observer && window[observer]) {
            window[observer]($timer, $btn, data);
        }
    });

    $(document).on('prcr.hide', function (event, $timer, $btn, data) {
        var observer = $timer.data('on-hide');
        if (observer && window[observer]) {
            window[observer]($timer, $btn, data);
        }
    });

    window.prcrGroupedProductInit = function ($timer, $btn) {
        timers.checkGroupedProduct($timer, $btn, true);
    }

    window.prcrGroupedProductChildExpiry = function ($timer, $btn) {
        var $parentTimer = $('.prcr_product_child_' + $timer.data('product'));
        timers.hide($parentTimer, {}, true);
    }

    window.prcrGridProductReservedStatus = function ($timer, $btn) {
        $timer.closest('.product-item').find('img').each(function () {
            var $img = $(this),
                wrapperClass = 'prcr-product-reserved-wrapper';

            if ($img.attr('src').indexOf('media/catalog/product/cache') != -1) {
                var $parent = $img.parent();
                if (! $parent.hasClass(wrapperClass)) {
                    $parent
                        .addClass(wrapperClass)
                        .append('<span class="prcr-product-reserved-text">' + $.mage.__('Reserved') + '</span>');

                    $timer.on('prcr.hide', function () {
                        $parent
                            .removeClass(wrapperClass)
                            .find('.prcr-product-reserved-text')
                            .remove();
                    });
                }
            }
        });
    }

    /**
     * Observers on change product options
     */
    var showChildTimerOfConfigurableProduct = function () {
        if (! $.mage.SwatchRenderer) {
            return;
        }

        var $node = $(this);
        if (! $node.data('mageSwatchRenderer')) {
            // For product list.
            $node = $node.find('> [class^="swatch-opt-"]');
            if (! $node.data('mageSwatchRenderer')) {
                return;
            }
        }

        var productId = null,
            selected = 0,
            $attributes = $node.find('> div.swatch-attribute');

        $attributes.each(function () {
            if ($(this).attr('option-selected')) {
                selected++;
            }
        });

        // Method getProduct can use only if all options is selected.
        if ($attributes.length === selected) {
            productId = $node.SwatchRenderer('getProduct');
        }

        if (productId && timers.productTimers && timers.productTimers[productId] && timers.productTimers[productId].timer_expire_at) {
            var $timer = $('.prcr_product_child_' + productId);
            timers.countdownInit($timer, timers.productTimers[productId]);
            timers.showStatus($timer);
            $node.data('last-selected-id', productId);
        } else {
            var lastSelectedId = $node.data('last-selected-id');
            if (lastSelectedId > 0) {
                var $timer = $('.prcr_product_child_' + lastSelectedId);
                timers.hide($timer, {'product_id': lastSelectedId});
            }
        }
    }

    function onSwatchChange () {
        $(configurableOptionsSelector).on('change', showChildTimerOfConfigurableProduct);
    }

    onSwatchChange();

    $(bundleOptionsSelector).on('change', function () {
        if (! $.mage.priceBundle || ! timers.productTimers) {
            return;
        }

        var optionConfig = $(this).priceBundle('option', 'optionConfig'),
            productIds = [],
            item = null;

        // Get selected product ids.
        $.each(optionConfig.selected, function (group, options) {
            $.each(options, function (key, option) {
                if (option && optionConfig.options[group].selections[option]) {
                    productIds.push(
                        optionConfig.options[group].selections[option].optionId
                    );
                }
            });
        });

        // Stop if items don't changed.
        if (prevBundleIds == productIds.join(',')) {
            return;
        }
        prevBundleIds = productIds.join(',');

        // Find timer with higher time.
        $.each(productIds, function (key, productId) {
            if (timers.productTimers[productId] && timers.productTimers[productId].timer_expire_at) {
                var _item = timers.productTimers[productId];
                if (! item || _item.timer_expire_at > item.timer_expire_at) {
                    item = _item;
                }
            }
        });

        if (item) {
            var $timer = $('.prcr_product_child_' + item.product_id);
            timers.countdownInit($timer, item);
            timers.showStatus($timer);
        } else {
            // If timer is not exists then take first child id.
            var $timer = $('.prcr_product_child_' + productIds[0]);
            timers.hide($timer, {'product_id': productIds[0]});
        }
    });

    /**
     * Refresh timers after updating of cart
     */
    $(document).on('customer-data-reload', function (event, sections) {
        var sectionName = 'prcr-timer';
        if (-1 === sections.indexOf(sectionName)) {
            return;
        }

        // Trigger is called before data update, so need the small delay
        setTimeout(function () {
            var data = customerData.get(sectionName)();
            timers.processData(data);
        }, 100);
    });

    /**
     * Add to cart via ajax
     */
    $(document).on('ajax:addToCart', function () {
        if ($('body').hasClass('page-product-bundle catalog-product-view')) {
            prevBundleIds = null;
        }
    });

    /**
     * Minicart global timer and item timers
     */
    var $miniCart = $('[data-block="minicart"]');

    $miniCart.on('contentUpdated', function () {
        if ($miniCart.data('mageSidebar')) {
            var removeSelector = $miniCart.data('mageSidebar').options.button.remove;
            $miniCart.find(removeSelector).on('click', function () {
                // var $timer = $(this).closest('#mini-cart > li.product-item').find('.prcr-timer');
                // timers.hide($timer);
                // Stop all timers before reloading of minicart.
                $miniCart.find('#mini-cart .prcr-timer').each(function () {
                    timers.hide($(this));
                });
            });
        }

        if (timers.minicartInitTimeout) {
            clearTimeout(timers.minicartInitTimeout);
        }

        timers.minicartInitTimeout = setTimeout(function () {
            if (window.prcrGlobalTimerHtml) {
                if (! timers.minicartGlobalTimerExists) {
                    timers.minicartGlobalTimerExists = true;
                    $miniCart.find('#minicart-content-wrapper')
                        .prepend(window.prcrGlobalTimerHtml);
                    timers.showGlobalTimer();
                }
            }

            if (! timers.minicartItemTimersExist) {
                timers.minicartItemTimersExist  = true;
                $.each(timers.itemTimers, function (id, item) {
                    timers.showOnItem(item);
                });
            }

            timers.init();
        }, 1000);
    });

    /**
     * Update timers when products are updated by Product Filter
     */
    $('body').on('contentUpdated', function() {
        // Init timers of products
        timers.processDataProducts(timers.productTimers);
        timers.init();

        // Reinitialize observer for configurable options
        onSwatchChange();
    });

    /**
     * Public API
     */
    var timers = {
        defaults: {
            format: 'hms',
            labelsFew: $.mage.__('years,months,weeks,days,hours,minutes,seconds'),
            labelsOne: $.mage.__('year,month,week,day,hour,minute,second')
        },

        mode: null,
        productIds: [],
        productTimers: null,
        itemTimers: null,
        globalTimer: null,
        currentTime: null,
        refreshedAt: null,
    };

    timers.setMode = function (mode) {
        timers.mode = mode;
    };

    timers.addProductIdToRequest = function (id) {
        timers.productIds.push(id);
    };

    timers.countdownInit = function ($timer, data) {
        data = data || {};
        var $btn = timers.getButton($timer);
        $timer.trigger('prcr.countdownInit_before', [$timer, $btn, data]);
        var time = data.timer_expire_at || $timer.data('time');

        if (! $timer.length) {
            return false;
        }

        if (time <= 0 || typeof time === 'undefined') {
            if (! $timer.hasClass('prcr_product')) {
                timers.hide($timer, data);
            }
            return false;
        }

        var $timerCountdown = $timer.find('> span');
        if (timers.hasCountdown($timerCountdown)) {
            $timerCountdown.countdown('destroy');
        }

        var layout = $timer.data('layout');
        if (layout && (time - timers.currentTime) / 3600 < 1) {
            layout = layout.replace(/{hn+}[\-/\: ]*/i, '').replace(/{hl}[\-/\: ]*/i, '');
        }

        var labelsFew = $timer.data('labels-few') || timers.defaults.labelsFew;
        var labelsOne = $timer.data('labels-one') || timers.defaults.labelsOne;

        time = timers.fixIncorrectOSTime(time, timers.currentTime)

        $timerCountdown
            .removeClass('prcrTimerInit')
            .countdown({
                until:      new Date(time * 1000),
                format:     ($timer.data('format') || timers.defaults.format),
                layout:     layout,
                padZeroes:  true,
                labels:     labelsFew.split(','),
                // The display texts for the counters if only one
                labels1:    labelsOne.split(','),
                onExpiry: function () {
                    timers.hide($timer, data);
                    timers.hideMinicartEmptyRow($timer);
                    $timer.trigger('prcr.countdownInit_expiry', [$timer, $btn, data]);
                }
            });

        $timer.show();
        if ($timer.data('show')) {
            $(timers.prepareSelector($timer.data('show'), data)).show();
        }
        if ($timer.data('hide')) {
            $(timers.prepareSelector($timer.data('hide'), data)).each(function () {
                var $el = $(this).hide();
                if ($el.is('input[type=text], input[type=number]')) {
                    // Set 0 if current hidden element is qty.
                    $el.val(0);
                }
            });
        }
        // Show reserved status.
        timers.showStatus($timer);
        timers.getPaymentRequestButton($btn).hide();
        $btn.hide();

        $timer.trigger('prcr.countdownInit_after', [$timer, $btn, data]);
        return true;
    };

    timers.reminderPopupInit = function (data) {
        if (! window.prcrReminderPopupEnabled || timers.isCheckout()) {
            return;
        }

        var reminderTime = window.prcrReminderPopupTime,
            reminderOpenAt = null,
            timerExpireAt = null,
            timerExists = false,
            storage = customerData.get('prcr-reminder-popup')();

        // Remove previous timeout if exists
        if (timers.reminderPopupTimeout) {
            clearTimeout(timers.reminderPopupTimeout);
        }

        // Check time based on global timer or item timers
        if (data.global_timer) {
            timerExpireAt = timers.getMinAllowedExpireAt(timerExpireAt, data.global_timer.timer_expire_at, true);
            if (storage.timer_expire_at > 0 && storage.timer_expire_at === data.global_timer.timer_expire_at) {
                timerExists = true;
            }
        } else if (data.items) {
            $.each(data.items, function(id, item) {
                timerExpireAt = timers.getMinAllowedExpireAt(timerExpireAt, item.timer_expire_at, true);

                if (storage.timer_expire_at > 0 && storage.timer_expire_at === item.timer_expire_at) {
                    timerExists = true;
                }
            });
        }

        /*// Check time based on previous calculation. It needs if customer refreshes page in couple seconds before popup.
        if (timerExists) {
            timerExpireAt = timers.getMinAllowedExpireAt(timerExpireAt, storage.timer_expire_at, false);
        }

        storage.timer_expire_at = timerExpireAt;*/

        if (timerExpireAt > 0) {
            reminderOpenAt = timerExpireAt - timers.currentTime - reminderTime;
            reminderOpenAt = Math.max(reminderOpenAt, 2);

            timers.reminderPopupTimeout = setTimeout(function () {
                window.modalContainer.modal('openModal');
                storage.timer_expire_at = null;
                timers.reminderPopupInit(data);
            }, reminderOpenAt * 1000);
        }
    }

    timers.getMinAllowedExpireAt = function (currentExpireAt, newExpireAt, checkReminderTime) {
        var reminderTime = window.prcrReminderPopupTime,
            maxFrequencyTime = 3 * 60,
            timeAfterInteraction = null,
            timeBeforeReminder = null,
            now = Date.now() / 1000,
            storage = customerData.get('prcr-reminder-popup')();

        // Stop if timer is expired
        if (newExpireAt <= 0 || newExpireAt <= now) {
            return currentExpireAt;
        }

        // Stop if timer can't have reminder
        if (checkReminderTime && (newExpireAt - now < reminderTime)) {
            return currentExpireAt;
        }

        // Check if reminder will not appear too often
        if (storage.interaction_at > 0) {
            timeAfterInteraction = (Date.now() - storage.interaction_at) / 1000;
            timeBeforeReminder = newExpireAt - timers.currentTime - reminderTime;
            if (maxFrequencyTime > timeAfterInteraction + timeBeforeReminder) {
                return currentExpireAt;
            }
        }

        if (null === currentExpireAt) {
            currentExpireAt = newExpireAt;
        } else {
            currentExpireAt = Math.min(currentExpireAt, newExpireAt);
        }

        return currentExpireAt;
    }

    timers.getButton = function ($timer) {
        return $timer.siblings('button[id!=product-updatecart-button], .pac-btn-wrap');
    };

    timers.getPaymentRequestButton = function ($btn) {
        return $btn.siblings('div.action.tocart.payment-request-button');
    };

    timers.hide = function ($timer, data, force) {
        force = force || false;
        if (! timers.hasCountdown($timer.find('> span')) && ! force) {
            return;
        }

        var $btn = timers.getButton($timer);
        $timer.hide();
        if ($timer.data('show')) {
            $(timers.prepareSelector($timer.data('show'), data)).hide();
        }
        if ($timer.data('hide')) {
            $(timers.prepareSelector($timer.data('hide'), data)).show();
        }
        timers.getPaymentRequestButton($btn).show();
        $btn.show();
        $timer.find('> span').countdown('destroy');
        // Hide reserved status.
        timers.hideStatus();

        $timer.trigger('prcr.hide', [$timer, $btn, data]);
    };

    timers.hasCountdown = function ($el) {
        return $el.hasClass('is-countdown') || $el.hasClass('hasCountdown');
    };

    /**
     * Show product timer on category or product page
     *
     * @param item
     * @return void
     */
    timers.showOnProduct = function (item) {
        var $timer = $('.prcr_product_' + item.product_id);

        if (! $timer.length) {
            return;
        }

        if ($timer.data('children')) {
            $(configurableOptionsSelector).trigger('change');
        }

        if (item.removed) {
            timers.hide($timer, item);
            return;
        }

        // For configurable and bundle products.
        // Stop if not all children are reserved.
        var childrenCount = $timer.data('children-count');
        if (childrenCount
            && item.reserved_children_count
            && childrenCount > item.reserved_children_count
        ) {
            return;
        }

        if (childrenCount
            && item.max_qty <= 0
            && ! item.reserved_children_count
        ) {
            return;
        }

        timers.countdownInit($timer, item);
    };

    timers.showOnItem = function(item) {
        var $timer = $('.prcr_item_' + item.item_id);
        if (! $timer.length) {
            return;
        }

        timers.countdownInit($timer, item);
        timers.hideEmptyRow($timer);
    };

    timers.showGlobalTimer = function() {
        var $timer = $('.prcr_global_timer');

        if (! $timer.length) {
            return;
        }

        timers.countdownInit($timer, timers.globalTimer);
    };

    timers.prepareSelector = function(selector, data) {
        selector = selector.replace(/:PRODUCT_ID/g, data.product_id);
        return selector;
    };

    timers.load = function() {
        var data = {
            mode: timers.mode,
            productIds: timers.productIds,
        };

        // Load timers.
        $.ajax({
            url: window.prcrTimerLoadPath + '?_=' + Date.now(),
            data: JSON.stringify(data),
            method: 'POST',
            global: false,
            showLoader: false,
            dataType: 'json',
            contentType: 'application/json; charset=utf-8',
            processData: false,
            success: function(response) {
                if (response) {
                    response = JSON.parse(response);
                }

                if (! response.success) {
                    return;
                }

                timers.refreshedAt = Date.now();
                // timers.productIds = [];

                timers.processData(response);
            }
        });
    };

    timers.removeItem = function() {
        if (timers.removeItemTimeout) {
            clearTimeout(timers.removeItemTimeout);
        }

        timers.removeItemTimeout = setTimeout(function() {
            $.ajax({
                url: window.prcrTimerRemoveItemPath + '?_=' + Date.now(),
                data: {},
                method: 'POST',
                global: false,
                showLoader: false,
                dataType: 'json',
                success: function(response) {
                    if (response) {
                        response = JSON.parse(response);
                    }

                    if (response.success) {
                        customerData.invalidate(['cart', 'messages']);
                        if (response.messages.length > 0) {
                            customerData.set('messages', {
                                messages: [{
                                    type: 'success',
                                    text: response.messages
                                }]
                            });
                        }

                        if (timers.isCheckout() || timers.isCart()) {
                            setTimeout(function() {
                                window.location.reload();
                            }, 3000);
                        } else {
                            $miniCart.find('.prcr_item').each(function() {
                                // Countdown script stops working if minicart reloads with non-stopped timers, so stop them.
                                timers.hide($(this));
                                timers.minicartItemTimersExist = false;
                            });
                            customerData.reload(['cart', 'prcr-timer']);
                        }
                    }
                }
            });
        }, 1000);
    };

    timers.processData = function(data) {
        if (! data.success) {
            return;
        }

        timers.currentTime = data.current_time;

        if (data.products) {
            timers.productTimers = $.extend({}, timers.productTimers || [], data.products);
            timers.processDataProducts(timers.productTimers);
        }

        if (data.global_timer) {
            timers.globalTimer = data.global_timer;
            timers.showGlobalTimer();

            if (timers.globalTimer.timer_expire_at == null && window.modalContainer) {
                window.modalContainer.modal('closeModal');
            }
        } else {
            if (data.items && Object.keys(data.items).length) {
                timers.itemTimers = $.extend({}, timers.itemTimers || [], data.items);
                $.each(data.items, function(id, item) {
                    timers.showOnItem(item);
                });
            } else {
                if (window.modalContainer) {
                    window.modalContainer.modal('closeModal');
                }
            }
        }

        timers.init();
        timers.reminderPopupInit(data);
    }

    timers.processDataProducts = function(productTimers) {
        $.each(productTimers, function(id, item) {
            timers.showOnProduct(item);

            // Fix. Grid + Ajax for bundle and grouped products.
            if (item.removed) {
                $('.prcr_product_child_' + item.product_id + '.prcr_product_bundle,'
                    + '.prcr_product_child_' + item.product_id + '.prcr_product_grouped'
                ).each(function() {
                    timers.hide($(this));
                });
            }
        });

        // Show timer if selected by default items are reserved.
        $('[data-role=swatch-options]').trigger('change');
        $('#product_addtocart_form').trigger('change');
    }

    timers.init = function(data) {
        $('.prcrTimerInit').each(function() {
            var $el = $(this);
            var $timer = $el.parent();
            $el.removeClass('prcrTimerInit');

            timers.checkGroupedProduct($timer);
            if (! timers.countdownInit($timer)) {
                if ($timer.hasClass('prcr_item') || $timer.hasClass('prcr_global_timer')) {
                    timers.removeItem();
                }
            }
            timers.hideEmptyRow($timer);
            timers.hideMinicartEmptyRow($timer);
        });
    };

    timers.showStatus = function($timer) {
        if ($timer && ! $timer.data('show-status')) {
            return;
        }

        var $el = $('body.catalog-product-view .stock.available');
        if (! $el.hasClass('prcr-reserved')) {
            $el.addClass('prcr-reserved')
                .append('<span class="prcr-reserved-text">' + $.mage.__('Reserved') + '</span>');
        }
    };

    timers.hideStatus = function() {
        var $el = $('body.catalog-product-view .stock.available');
        if ($el.hasClass('prcr-reserved')) {
            $el.removeClass('prcr-reserved')
                .find('.prcr-reserved-text')
                .remove();
        }
    };

    timers.hideEmptyRow = function($timer) {
        if (! $timer.hasClass('prcr_item')) {
            return;
        }

        // Hide ":" before timer on cart page.
        var $dt = $timer.closest('dd').prev('dt');
        if ($dt && ! $dt.html()) {
            $dt.addClass('prcr-dt-empty');
        }
    };

    timers.hideMinicartEmptyRow = function($timer) {
        if (! $timer.hasClass('prcr_item') || $timer.find('> span.prcrTimerInit').length || $timer.is(':visible')) {
            return;
        }

        // Hide "See Details" section if it hasn't options.
        var $dl = $timer.closest('dl');
        if ($dl.find('> dd').size() == 0) {
            $dl.closest('div.product.options').addClass('prcr-options-empty');
        }
    };

    timers.checkGroupedProduct = function($timer, $btn, showTime) {
        var $btn = $btn || timers.getButton($timer);
        showTime = showTime || false;

        /**
         * Check all children and if all of them have timers - hide tocart button and show (if needed) timer.
         * This is use for the grouped products.
         */
        if ($timer.data('children')) {
            var childrenCount = $timer.data('children-count');
            var reservedChildrenCount = 0;
            $($timer.data('children').toString().split(',')).each(function(key, id) {
                if (timers.productTimers && timers.productTimers[id] && timers.productTimers[id].timer_expire_at) {
                    reservedChildrenCount++;

                    if (showTime) {
                        var expireAt = timers.productTimers[id].timer_expire_at;
                        if (! $timer.data('time')
                            || $timer.data('time') > expireAt
                        ) {
                            $timer.data('time', expireAt);
                        }
                    }
                }
            });

            if (childrenCount
                && reservedChildrenCount
                && reservedChildrenCount >= childrenCount
            ) {
                timers.showStatus($timer);
                timers.getPaymentRequestButton($btn).hide();
                $btn.hide();
            }
        }
    };

    timers.calculateUntilTime = function(serverExpireTime) {
        var browserTime = new Date().getTime();
        var timeToExp = serverExpireTime - timers.currentTime;

        return new Date(browserTime + timeToExp * 1000);
    };

    timers.isCheckout = function () {
        return timers.mode === 'checkout';
    };

    timers.isCart = function () {
        return timers.mode === 'cart';
    }

    /**
     * Fix the Operating System incorrect time
     *
     * Bug with incorrect time was found on Windows when time in not synchronized
     * in that case timer would have incorrect time
     *
     * Try to repair it by comparing server and OS times
     *
     * @param timerEndTime in seconds
     * @param currentServerTime in seconds
     * @returns {number|*} correct timer end time in seconds
     */
    timers.fixIncorrectOSTime = function (timerEndTime, currentServerTime) {
        // Difference in 55 seconds should be enough to say whether OS time is incorrect
        if (Math.abs(currentServerTime - (Date.now() / 1000)) > 55) {
            var secondsToEnd = timerEndTime - currentServerTime;
            return (Date.now() / 1000) + secondsToEnd;
        }

        return timerEndTime;
    };

    return timers;
});
