define([
    'jquery',
    'ko',
    'mage/utils/wrapper',
    'mage/url'
], function ($, ko, wrapper, urlBuilder) {
    'use strict';

    return function (stepNavigator) {
        stepNavigator.navigateTo = wrapper.wrap(stepNavigator.navigateTo, function (originalAction, code, scrollToElementId) {
            if (code == 'mycart') {
                var url = urlBuilder.build('checkout/cart/index');
                window.location.href = url;
            }
            return originalAction(code, scrollToElementId);
        });

        return stepNavigator;
    };
});