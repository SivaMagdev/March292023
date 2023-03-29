define([ "jquery" ], function($){
    $(document).scroll(function () {
        var $window = $(window);
        var windowsize = $window.width();
        var height = $(document).scrollTop();
            if(height  > 150 && windowsize >= 768) {
                $('.page-header').addClass('fixed-menu');
            }else{
                $('.page-header').removeClass('fixed-menu');
            }
    });
});