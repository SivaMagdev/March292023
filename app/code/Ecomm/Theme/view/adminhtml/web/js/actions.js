define([
    "jquery",
    'uiGridColumnsActions'
], function($, actions){
    'use strict';

    //make it draggable on customer listing page only
    if($( 'body' ).hasClass( 'customer-index-index' )){
        return actions.extend({
            defaults: {
                draggable: true
            }
        });
    }else{
        return actions;
    }
});