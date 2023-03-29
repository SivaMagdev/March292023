define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.bellnotification = customerData.get('bellnotification'); //pass your custom section name
            this.company = customerData.get('company'); //pass your custom section name
        }
    });

});