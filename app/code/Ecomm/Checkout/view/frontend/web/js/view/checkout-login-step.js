define(
    [
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Customer/js/model/customer'
    ],
    function (
        ko,
        Component,
        _,
        stepNavigator,
        customer
    ) {
        'use strict';
        /**
        * check-login - is the name of the component's .html template
        */
        return Component.extend({


            //add here your logic to display step,
            isVisible: ko.observable(false),
            isLogedIn: customer.isLoggedIn(),
            //step code will be used as step content id in the component template
            stepCode: 'mycart',
            //step title value
            stepTitle: 'My cart',

            /**
            *
            * @returns {*}
            */
            initialize: function () {
                this._super();
                // register your step
                stepNavigator.registerStep(
                    this.stepCode,
                    //step alias
                    null,
                    this.stepTitle,
                    //observable property with logic when display step or hide step
                    this.isVisible,

                    _.bind(this.navigate, this),

                    /**
                    * sort order value
                    * 'sort order value' < 10: step displays before shipping step;
                    * 10 < 'sort order value' < 20 : step displays between shipping and payment step
                    * 'sort order value' > 20 : step displays after payment step
                    */
                    7
                );

                return this;
            },

            /**
            * The navigate() method is responsible for navigation between checkout step
            * during checkout. You can add custom logic, for example some conditions
            * for switching to your custom step
            */
            navigate: function () {
                console.log("navigate"+this.stepCode);
                if(this.stepCode == 'mycart'){
                    //ko.observable(false);
                    //stepNavigator.next();
                    //stepNavigator.navigateTo('shipping');
                    stepNavigator.setHash('shipping');
                }
            },

            /**
            * @returns void
            */
            navigateToNextStep: function () {
                //console.log("navigateToNextStep");
                //stepNavigator.navigateTo('');
            }
        });
    }
);