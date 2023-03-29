define([
    'jquery',
    'mage/url',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-method'
], function ($, url, ko, Component, quote, selectShippingMethodAction) {
    'use strict';
    var date_hint = window.checkoutConfig.shipping.rgdd_delivery_date.date_hint;
    var notes_hint = window.checkoutConfig.shipping.rgdd_delivery_date.notes_hint;
    return Component.extend({
        defaults: {
            template: 'Rage_DeliveryDate/delivery-date'
        },
        initialize: function () {
            this._super();
            var is_unavailable_day = window.checkoutConfig.shipping.rgdd_delivery_date.is_unavailable_day;
            var disabled = window.checkoutConfig.shipping.rgdd_delivery_date.disabled;

            console.log('test disbale');
            //console.log(disabled);
            $('.opc-summary-wrapper .goBackCart').live('click', function() {
                window.location.href = url.build('checkout/cart/index');
            });
            var format = window.checkoutConfig.shipping.rgdd_delivery_date.format;
            if (!format) {
                format = 'yy-mm-dd';
            }
            if (is_unavailable_day == 1 && disabled) {
                var disabledDay = disabled.split(",").map(function (item) {
                    return parseInt(item, 10);
                });
            }
            ko.bindingHandlers.datetimepicker = {
                init: function (element, valueAccessor, allBindingsAccessor) {
                    var $el = $(element);
                    if (is_unavailable_day == 0) {
                        var options = {
                            minDate: 0,
                            dateFormat: format,
                            'showTimepicker': false,
                            showButtonPanel: false,
                        };
                    } else {
                        var options = {
                            minDate: 0,
                            dateFormat: format,
                            'showTimepicker': false,
                            showButtonPanel: false,
                            beforeShowDay: function (date) {
                                var day = date.getDay();
                                if (disabledDay.indexOf(day) > -1) {
                                    return [false];
                                } else {
                                    return [true];
                                }
                            }
                        };
                    }
                    $el.datetimepicker(options);
                    var writable = valueAccessor();
                    if (!ko.isObservable(writable)) {
                        var propWriters = allBindingsAccessor()._ko_property_writers;
                        if (propWriters && propWriters.datetimepicker) {
                            writable = propWriters.datetimepicker;
                        } else {
                            return;
                        }
                    }
                    writable($(element).datetimepicker("getDate"));
                },
                update: function (element, valueAccessor) {
                    var widget = $(element).data("DateTimePicker");
                    if (widget) {
                        var date = ko.utils.unwrapObservable(valueAccessor());
                        widget.date(date);
                    }

                    /*if(quote.shippingMethod()){
                        console.log(quote.shippingMethod().carrier_code);
                    } else {
                        console.log('no shipping');
                    }*/

                    $.ajax({
                        url: url.build('rdd/getdate'),
                        type: 'post',
                        data: { delivery_method : 'all' },
                        dataType: 'json',
                        showLoader: true, //use for display loader
                        beforeSend: function() {},
                        success: function(json) {

                            console.log('Result:'+json['success']);
                            if(json['success'] == true){
                                console.log('success:'+json['success']);
                                var rdd_standard_max_date = json['rdd_standard_max_date'];
                                var rdd_express_max_date = json['rdd_express_max_date'];
                                $('#label_carrier_standardshipping_standardshipping').after('<div id="rdd-standard-info">'+json['rdd_standard_text']+'</div>');
                                $('#label_carrier_expressshipping_expressshipping').after('<p class="note">Note: To get next day delivery, please order before 3pm EST. (No Weekend Delivery)</p><div id="rdd-express-info">'+json['rdd_express_text']+'</div>');

                                $('#delivery_date').val(rdd_standard_max_date);
                                $('#rgdd_standard_delivery_date').val(rdd_standard_max_date);
                                $('#rgdd_express_delivery_date').val(rdd_express_max_date);

                                if(quote.shippingMethod() && quote.shippingMethod().carrier_code == "expressshipping"){
                                    $('#delivery_date').val($('#rgdd_express_delivery_date').val());
                                } else {
                                   $('#delivery_date').val($('#rgdd_standard_delivery_date').val());
                                }

                                //console.log('Session shipping:-'+quote.shippingMethod().carrier_code);
                            } else {
                                var rdd_standard_max_date = '';
                                var rdd_express_max_date = '';
                            }
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                           alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                        }
                    });

                    $('.table-checkout-shipping-method input[type="radio"]').live('click', function() {
                        var code = 'your_custom_shipping_method_code';
                        // you can check your custom shipping method code using inspect element
                        // you can see the code in the value of input type radio

                        /*$('#delivery_date').val(rdd_date_calculated);*/

                        //console.log('test your_custom_shipping_method_code');

                        console.log(quote.shippingMethod().carrier_code);
                        if(quote.shippingMethod().carrier_code == "expressshipping"){
                            $('#delivery_date').val($('#rgdd_express_delivery_date').val());
                        } else {
                           $('#delivery_date').val($('#rgdd_standard_delivery_date').val());
                        }
                    });

                }
            };

            return this;
        },
        ko_date_hint: ko.observable(date_hint),
        ko_notes_hint: ko.observable(notes_hint),
    });
});
