/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'ko',
    'mageUtils',
    'uiComponent',
    'uiLayout',
    'Magento_Customer/js/model/address-list',
    'jquery'
], function (_, ko, utils, Component, layout, addressList, $) {
    'use strict';

    var defaultRendererTemplate = {
        parent: '${ $.$data.parentName }',
        name: '${ $.$data.name }',
        component: 'Magento_Checkout/js/view/shipping-address/address-renderer/default',
        provider: 'checkoutProvider'
    };

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-address/list',
            visible: addressList().length > 0,
            rendererTemplates: []
        },

        /** @inheritdoc */
        initialize: function () {
            this._super()
                .initChildren();

            addressList.subscribe(function (changes) {
                    var self = this;

                    changes.forEach(function (change) {
                        if (change.status === 'added') {
                            self.createRendererComponent(change.value, change.index);
                        }
                    });
                },
                this,
                'arrayChange'
            );

            return this;
        },

        /** @inheritdoc */
        initConfig: function () {
            this._super();
            // the list of child components that are responsible for address rendering
            this.rendererComponents = [];

            return this;
        },

        /** @inheritdoc */
        initChildren: function () {
            _.each(addressList(), this.createRendererComponent, this);

            return this;
        },

        /**
         * Create new component that will render given address in the address list
         *
         * @param {Object} address
         * @param {*} index
         */
        createRendererComponent: function (address, index) {
            var rendererTemplate, templateData, rendererComponent;

            //console.table(address.customAttributes);
            //console.table(address.customAttributes.address_status);

            var address_status = '';

            $.each( address.customAttributes, function( key, value ) {
              //console.log(key+" : "+value.attribute_code);
              //console.table(value);
              if(value.attribute_code == 'address_status'){
                address_status = value.label;
              }
              console.log(address_status);
            });

            //console.log(address.customAttributes.address_status.label);
            //console.table(address);
            //for(const [key,value] of Object.entries(address)) { console.log(${key}: ${value}) }
            // typeof arrayName[index] === 'undefined'
            var approved_address = 0;
            //if(address.customAttributes.address_status.label == 'Approved'){
            if(address_status == 'Approved'){

                //console.table(address.isDefaultBilling);
                //console.log(address.isDefaultBilling);

                //console.log(address.isDefaultBilling()+'-'+address.isDefaultShipping());
                //console.log(address.isDefaultBilling());

                //console.log(this.rendererTemplates[address.getType()]);
                //console.log(this.rendererTemplates[address.isDefaultBilling()]);

                if(address.isDefaultBilling() === null || address.isDefaultShipping() == true){

                    if (index in this.rendererComponents) {
                        this.rendererComponents[index].address(address);
                    } else {
                        // rendererTemplates are provided via layout
                        rendererTemplate = address.getType() != undefined && this.rendererTemplates[address.getType()] != undefined ? //eslint-disable-line
                            utils.extend({}, defaultRendererTemplate, this.rendererTemplates[address.getType()]) :
                            defaultRendererTemplate;
                        templateData = {
                            parentName: this.name,
                            name: index
                        };
                        rendererComponent = utils.template(rendererTemplate, templateData);
                        utils.extend(rendererComponent, {
                            address: ko.observable(address)
                        });
                        layout([rendererComponent]);
                        this.rendererComponents[index] = rendererComponent;
                    }

                    approved_address++;
                }

            }
            console.log("Aproved Address: "+approved_address);
            $('#address-count').val(approved_address);
            /*if(approved_address == 0){
                $('#shipping-method-buttons-container').remove();
                console.log("Aproved Address: -test");
            }*/
        }
    });
});
