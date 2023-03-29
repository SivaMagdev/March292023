/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'underscore',
    'escaper',
    'Magento_Ui/js/modal/modal',
    'jquery/jquery-storageapi'
], function ($, Component, customerData, _, escaper, modal) {
    'use strict';

    return Component.extend({
        defaults: {
            cookieMessages: [],
            messages: [],
            allowedTags: ['div', 'span', 'b', 'strong', 'i', 'em', 'u', 'a', 'button']
        },

        /**
         * Extends Component object by storage observable messages.
         */
        initialize: function () {
            this._super();

            this.cookieMessages = _.unique($.cookieStorage.get('mage-messages'), 'text');
            this.messages = customerData.get('messages').extend({
                disposableCustomerData: 'messages'
            });

            // Force to clean obsolete messages
            if (!_.isEmpty(this.messages().messages)) {
                customerData.set('messages', {});
            }

            $.cookieStorage.set('mage-messages', '');

            $(".messages .message .close" ).live( "click", function() {
                $(this).parent().parent().remove();
                /*var myClass = $(this).parent().parent().attr("class");
                alert(myClass);
                $(".page.messages").remove();*/
            });


           	/*console.log($(".messages").hasClass("message"));

           	if($(".messages").hasClass("message")){

           		var options = {
	               type: 'popup',
	               title: '',
	               responsive: true,
	               innerScroll: false,
	               clickableOverlay: false,
	               buttons: [{
	                   text: $.mage.__('Continue'),
	                   class: 'success-notifications',
	                   click: function() {
	                       this.closeModal();
	                   }
	               }]
	           	};

           		var popup = modal(options, $('.messages'));

            	$(".messages").modal("openModal");
           	}*/

            /*setTimeout(function() {
                $(".messages").hide('blind');
            }, 9000);*/
        },

        /**
         * Prepare the given message to be rendered as HTML
         *
         * @param {String} message
         * @return {String}
         */
        prepareMessageForHtml: function (message) {
            return escaper.escapeHtml(message+'<button type="button" class="close" data-dismiss="alert">×</button>', this.allowedTags);
        }
    });
});
