require([
    "jquery",
    "Magento_Ui/js/modal/modal",
    "slick",
    "grtyoutube",
], function ($, slick, modal, grtyoutube) {
    $(document).ready(function () {
        var $slider = jQuery(".single-item");

        if ($slider.length) {
            var currentSlide;
            var slidesCount;
            var prevButton;
            var nextButton;
            var sliderCounter = document.createElement("div");
            sliderCounter.classList.add("slider__counter");

            var updateSliderCounter = function (slick, currentIndex) {
                currentSlide = slick.slickCurrentSlide() + 1;
                slidesCount = slick.slideCount;
                jQuery(sliderCounter).text(currentSlide + "/" + slidesCount);
            };

            $slider.on("init", function (event, slick) {
                $slider.append(sliderCounter);
                updateSliderCounter(slick);
            });

            $slider.on("afterChange", function (event, slick, currentSlide) {
                updateSliderCounter(slick, currentSlide);
            });

            $slider.slick();
        }

        $(".quote-container").mousedown(function () {
            $(".single-item").addClass("dragging");
        });
        $(".quote-container").mouseup(function () {
            $(".single-item").removeClass("dragging");
        });
    });

    $(document).ready(function () {
        var $slider = jQuery(".home-single-item");

        if ($slider.length) {
            var currentSlide;
            var slidesCount;
            var prevButton;
            var nextButton;
            var sliderCounter = document.createElement("div");
            sliderCounter.classList.add("slider__counter");

            var updateSliderCounter = function (slick, currentIndex) {
                currentSlide = slick.slickCurrentSlide() + 1;
                slidesCount = slick.slideCount;
                jQuery(sliderCounter).text(currentSlide + "/" + slidesCount);
            };

            $slider.on("init", function (event, slick) {
                $slider.append(sliderCounter);
                updateSliderCounter(slick);
            });

            $slider.on("afterChange", function (event, slick, currentSlide) {
                updateSliderCounter(slick, currentSlide);
            });

            $slider.slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                infinite: false,
            });
        }
    });

    $(document).ready(function () {
        $(".label-short-header").live("click", function () {
            if ($(this).next("div").is(":visible")) {
                $(this).next("div").slideUp("normal");
                $(this)
                    .find("i")
                    .removeClass("arrow-open")
                    .addClass("arrow-close");
            } else if (jQuery(".short-accordion-body").is(":hidden")) {
                $(".short-accordion-body").slideDown("normal");
                $(this)
                    .find("i")
                    .removeClass("arrow-close")
                    .addClass("arrow-open");
            } else {
                $(".short-accordion-body").slideUp("normal");
                $(this).next("div").slideToggle("normal");
                $(this)
                    .find("i")
                    .removeClass("arrow-open")
                    .addClass("arrow-close");
            }
        });

        $("#short-container").live("click", function () {
            if ($(this).is(":checked")) {
                $(".short-accordion-head")
                    .find("i")
                    .removeClass("arrow-close")
                    .addClass("arrow-open");
                $(".short-accordion-body").slideDown("normal");
            } else {
                $(".short-accordion-head")
                    .find("i")
                    .removeClass("arrow-open")
                    .addClass("arrow-close");
                $(".short-accordion-body").slideUp("normal");
            }
        });
    });

    jQuery(document).ready(function () {
        jQuery(document).on("click", ".action-close, .secondary", function () {
            jQuery(".checkout-agreement .required-entry").removeAttr(
                "disabled"
            );
            jQuery(".checkout-agreement .required-entry").prop("checked", true);
        });

        jQuery(document).on(
            "click",
            ".checkout-agreement .required-entry",
            function () {
                jQuery(this).attr("disabled", true);
                jQuery(this).prop("checked", false);
            }
        );
    });

    jQuery(document).ready(function () {
        jQuery(".cus-in-carousel").slick({
            slidesToShow: 1,
            dots: true,
            autoplay: true,
            autoplaySpeed: 2000,
            infinite: true,
            arrows: false,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        infinite: true,
                        dots: true,
                    },
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    },
                },
                {
                    breakpoint: 400,
                    settings: {
                        arrows: false,
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    },
                },
            ],
        });
    });
    jQuery(document).ready(function () {
        jQuery(".drl-track-slider").slick({
            infinite: false,
        });
    });

    jQuery(document).ready(function () {
        jQuery(".logo-carousel").slick({
            dots: false,
            infinite: true,
            speed: 500,
            slidesToShow: 4,
            slidesToScroll: 1,
            autoplay: false,
            autoplaySpeed: 2000,
            arrows: false,
            responsive: [
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1,
                    },
                },
                {
                    breakpoint: 400,
                    settings: {
                        arrows: false,
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    },
                },
            ],
        });
    });

    jQuery(document).ready(function () {
        jQuery(".order-products").slick({
            dots: true,
            speed: 500,
            slidesToShow: 3,
            slidesToScroll: 1,
            autoplay: false,
            autoplaySpeed: 2000,
            arrows: true,
        });
    });

    jQuery(document).ready(function () {
        jQuery('.up-del-cars').slick({
            dots: false,
            speed: 500,
            slidesToShow: 2,
            slidesToScroll: 2,
            autoplay: false,
            autoplaySpeed: 2000,
            arrows: false
        });
    });

    jQuery(document).ready(function () {
        jQuery(".home-banner-carousel").slick({
            dots: true,
            speed: 500,
            slidesToShow: 1,
            slidesToScroll: 1,
            autoplay: false,
            autoplaySpeed: 2000,
            arrows: false,
            responsive: [
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    },
                },
                {
                    breakpoint: 400,
                    settings: {
                        arrows: false,
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    },
                },
            ],
        });
    });

    jQuery(document).ready(function () {
        jQuery("#slick-container-shoutouts").slick({
            dots: true,
            speed: 500,
            slidesToShow: 2,
            slidesToScroll: 1,
            autoplay: false,
            autoplaySpeed: 2000,
            arrows: false,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1,
                        infinite: true,
                        dots: true,
                    }
                },
                {
                     breakpoint: 767,
                     settings: {
                         slidesToShow: 1,
                         slidesToScroll: 1
                      }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 400,
                    settings: {
                        arrows: false,
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });
    });

    jQuery(document).ready(function () {
        jQuery(".carousel").slick({
            infinite: true,
            dots: false,
            speed: 500,
            slidesToShow: 3,
            centerMode: true,
            centerPadding: "0",
            slidesToScroll: 1,
            autoplay: false,
            autoplaySpeed: 2000,
            arrows: true,
            variableWidth: false,
            initialSlide: 0,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1,
                        infinite: true,
                        dots: false,
                    }
                },
                {
                     breakpoint: 767,
                     settings: {
                        centerMode: true,
                        centerPadding: "0",
                         slidesToShow: 1,
                         slidesToScroll: 1,
                         arrows: false,
                        variableWidth: false,
                        dots: true,

                      }
                },
                {
                    breakpoint: 600,
                    settings: {
                        centerMode: true,
                        centerPadding: "0",
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        arrows: false,
                        variableWidth: false,
                        dots: true,
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        variableWidth: false,
                        dots: true,
                        centerMode: true,
                        centerPadding: "0",
                        arrows: false,
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    }
                }
            ]
        });
    });

    jQuery(document).ready(function () {
        jQuery(".corporate-carousel").slick({
            infinite: true,
            dots: false,
            speed: 500,
            slidesToShow: 3,
            centerMode: false,
            centerPadding: "0",
            slidesToScroll: 1,
            autoplay: false,
            autoplaySpeed: 2000,
            arrows: true,
            variableWidth: false,
            initialSlide: 0,
            responsive: [
                {
                    breakpoint: 600,
                    settings: {
                        centerMode: false,
                        centerPadding: "0",
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        arrows: true,
                        variableWidth: false,
                        dots: true,
                    },
                },
                {
                    breakpoint: 400,
                    settings: {
                        variableWidth: false,
                        dots: true,
                        centerMode: false,
                        centerPadding: "0",
                        arrows: true,
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    },
                },
            ],
        });
    });

    jQuery(".block-search .label").click(function () {
        jQuery(".select").css({
            "margin-top": "72px",
            left: "0px",
            display: "inline-block",
        });
    });

    jQuery(function () {
        jQuery("a.bla-1").YouTubePopUp();
        jQuery("a.bla-2").YouTubePopUp({ autoplay: 0 }); // Disable autoplay
    });

    /* var maxLength = 15;
    jQuery('#custom_attribute > option').text(function(i, text) {
        if (text.length > maxLength) {
            return text.substr(0, maxLength) + '...';  
        }
    });

    */

    jQuery(".decreaseQty").click(function () {
        var id = jQuery(this).attr("data-id");
        var value = parseInt(
            jQuery(this).parent(".control").find(".qty").val()
        );
        if (value == 1) return;
        value--;
        jQuery(this).parent(".control").find(".qty").val(value);
    });

    jQuery(".increaseQty").click(function () {
        var id = jQuery(this).attr("data-id");
        var value = parseInt(
            jQuery(this).parent(".control").find(".qty").val()
        );
        value++;
        jQuery(this).parent(".control").find(".qty").val(value);
    });
});
var acc = document.getElementsByClassName("accordion");
var i;

for (i = 0; i < acc.length; i++) {
    acc[i].addEventListener("click", function () {
        this.classList.toggle("active");
        var panel = this.nextElementSibling;
        if (panel.style.maxHeight) {
            panel.style.maxHeight = null;
        } else {
            panel.style.maxHeight = panel.scrollHeight + "px";
        }
    });
}
