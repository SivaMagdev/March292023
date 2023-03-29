define([
    'jquery',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Customer/js/customer-data',
    'prcr'
    ], function ($, getTotalsAction, customerData, timers) {
    
    $(document).ready(function(){
    
        showCart();
    
    $(document).on('click', '.cart .decreaseQty', function(){
    
    
    var $this = $(this);
    var ctrl = ($(this).attr('id').replace('-upt','')).replace('-dec','');
    var currentQty = $("#cart-"+ctrl+"-qty").val();
    
    if(currentQty>1){
    var newAdd = parseInt(currentQty)-parseInt(1);
    $("#cart-"+ctrl+"-qty").val(newAdd);
    }
    
    
    
    });
    // $( "#price" ).empty().append( Price Update Code );
    $(document).on('click', '.cart .price', function(){
    var price = $(this).val();
    var data = price;
    $.ajax({
    type: "POST",
    url:BASE_URL+'priceengine/index/cartpagepriceupdate',
    data:{price:data},
    showLoader: true,
    success: function(response){
    console.log(response);
    setTimeout(function() {
    showCart();
    
    }, 2000);
    //if request if made successfully then the response represent the data
    
    }
    })
    
    });
    
    // $( "#price" ).empty().append( Price Code );
    
    $(document).on('click', '.cart .increaseQty', function(){
    
    var $this = $(this);
    var ctrl = ($(this).attr('id').replace('-upt','')).replace('-dec','');
    var currentQty = $("#cart-"+ctrl+"-qty").val();
    
    var newAdd = parseInt(currentQty)+parseInt(1);
    $("#cart-"+ctrl+"-qty").val(newAdd);
    
    ajaxCartUpdate();
    
    });
    
    function ajaxCartUpdate(){
    var form = $('.cart-container form#form-validate');
    $.ajax({
    type: "POST",
    url:BASE_URL+'autoupdatecart/index/autoupdatecart',
    data: form.serialize(),
    showLoader: true,
    success: function (res1) {
    if(res1.success==false)
    {
    var result = res1.error_message.split('~');
    $("<div class='message notice' id='error_div'>"+result[1]+"</div>").insertAfter("#"+result[0]+"-upt");
    }
    setTimeout(function() {
    showCart();
    }, 2000);
    }
    })
    }
    
    
    function showCart()
    {
    
    var form = $('.cart-container form#form-validate');
    $.ajax({
    url: form.attr('action'),
    data: form.serialize(),
    showLoader: true,
    success: function (res) {
    //console.log(res);
    var parsedResponse = $.parseHTML(res);
    var result = $(parsedResponse).find(".cart-container #form-validate");
    //alert(result);
    var sections = ['cart'];
    /*
    console.log(parsedResponse);
    console.log(result);
    console.log(sections);
    */
    
    $(".cart-container #form-validate").replaceWith(result);
    var discountresult = $(parsedResponse).find(".cart-container #discount-coupon-form");
    $(".cart-container #discount-coupon-form").replaceWith(discountresult);
    
    
    var totalqty = 0;
    $('input.qty').each(function() {
    var num = parseInt(this.value, 10);
    if (!isNaN(num)) {
    totalqty += 1;
    }
    })
    
    if(totalqty == 0)
    {
    $(".drl-heading-cart").remove();
    $(".drl-add-more-prod").remove();
    location.reload();
    }
    else
    {
    var producthtml;
    if(totalqty > 1)
    producthtml = totalqty+' Products';
    else
    producthtml = totalqty+' Product';
    $(".drl-head-count").html('My Cart ('+producthtml+')');
    }
    /* Minicart reloading */
    customerData.reload(sections, true);
    /* Totals summary reloading */
    var deferred = $.Deferred();
    getTotalsAction([], deferred);
    setTimeout(function(){
        timers.load();
    }, 2000);
    },
    error: function (xhr, status, error) {
    var err = eval("(" + xhr.responseText + ")");
    console.log(err.Message);
    }
    });

  
    }
    
    $(document).on('change', 'input[name$="[qty]"]', function(){
    ajaxCartUpdate();
    });
    });
    });