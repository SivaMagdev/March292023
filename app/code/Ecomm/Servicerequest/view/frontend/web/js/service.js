define([
    "jquery",
    "jquery/ui"
], function($) {
    "use strict";
    $(function(){
  $("tr.view").on("click", function(){
    $(this).toggleClass("open").next(".fold").toggleClass("open");
  });
   });
});
