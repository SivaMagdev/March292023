define([
    "jquery",
    "jquery/ui"
], function($) {
    jQuery(".profile .icon_wrap").click(function() {
        jQuery(this).parent().toggleClass("active");
        jQuery(".notifications").removeClass("active");
    });

    jQuery(".notifications .icon_wrap").click(function() {
        jQuery(this).parent().toggleClass("active");
        jQuery(".profile").removeClass("active");
    });

    jQuery(".show_all .link").click(function() {
        jQuery(".notifications").removeClass("active");
        jQuery(".popup").show();
    });

    jQuery(".close, .shadow").click(function() {
        jQuery(".popup").hide();
    });

      $(document).click(function() {
        if(this != $(".notification_ul")[0]) {
            $(".notification_ul").hide();
        }
    });
         
});