define(
    [
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function($, modal) {
    
        $(document).ready(function() {
    
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: false,
                buttons: [
                    {
                        text: $.mage.__('Back'),
                        class: 'social-back-btn',
                        click: function () {
                            this.closeModal();
                        }
                    },
                    {
                        text: $.mage.__('Proceed'),
                        class: 'social-proceed-btn',
                        click: function () {
                            window.open($("#social-proceed-url").val(), '_blank');
                           this.closeModal();
                        }
                    }
                ]
            };
    
            var popup = modal(options, $('#social-popup'));
    
            $('.external-link-click').live( "click", function() {
    
    
                $("#social-proceed-url").val($(this).data('exthref'));
                $("#social-popup").modal("openModal");
            });
        });
    });