require(
    ['jquery'],function ($) {
        $(document).on(
            'submit', '#newsletter-validate-detail', function (e) {
                var form = $('#newsletter-validate-detail');
                if (form.validation('isValid')) {
                    var email = $("#newsletter-validate-detail #newsletter").val();
                    var url = form.attr('action');
                    var loadingMessage = $('#loading-message');

                    if (loadingMessage.length == 0) {
                        form.find('.input-group').append('<div id="loading-message" style="display:none;padding-top:10px;color: red;font-size: 13px;">&nbsp;</div>');
                        var loadingMessage = $('#loading-message');
                    }

                    e.preventDefault();
                    try {
                        loadingMessage.html('Submitting...').show();
                        $('.scg-msg > messages').html();

                        $.ajax(
                            {
                                url: url,
                                dataType: 'json',
                                type: 'POST',
                                data: {email: email},
                                complete: function (data) {
                                    $('#newsletter-validate-detail #newsletter_subscribe').prop("disabled", false);
                                },
                                success: function (data) {
                                    // alert(data.error);
                                    if (data.error == false) {
                                        $("#newsletter-validate-detail #newsletter").val('');
                                        $('#newsletter-validate-detail .scg-msg > .messages').html(
                                            '<div class="message-success success message" ><div >' +
                                            data.message + '</div></div>'
                                        );
                                    } else {
                                        $('#newsletter-validate-detail .scg-msg > .messages').html(
                                            '<div class="message-error error message" >' +
                                            '<div>'+data.message +'</div></div>'
                                        );
                                    }
                                    loadingMessage.html(data.message);
                                }
                            }
                        );
                    } catch (e) {
                        loadingMessage.html(e.message);
                    }
                }
            }
        );

    
    }
)
