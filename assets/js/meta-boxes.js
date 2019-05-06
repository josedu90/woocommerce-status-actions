jQuery(function ($) {
    $('#post-query-submit').remove();

    // Color Picker
    $(document.body).on('wc-sa-init-colorpicker', function () {
        $('.color-picker-field').iris({
            change: function (event, ui) {
                $(this).parent().find('.colorpickpreview').css({backgroundColor: ui.color.toString()});
                $(this).parents().find('.wc-sa-icon_style').attr('style', 'background-color:' + ui.color.toString() + '40; ' + 'color:' + ui.color.toString() + ';');
            },
            width: 280,
            hide: true,
            border: true,
            palettes: ['#828282', '#ef6c00', '#eda411', '#559f55', '#386b98', '#96588a', '#c62828']
        }).on('click focus', function (event) {
            event.stopPropagation();
            $('.iris-picker').hide();
            $(this).closest('p').find('.iris-picker').show();
            $(this).data('original-value', $(this).val());
        });
        $('body').on('click', function () {
            $('.iris-picker').hide();
        });
    }).trigger('wc-sa-init-colorpicker');

    // Color Picker
    $(document.body).on('wc-sa-init-iconpicker', function () {
        $('.icon-picker-field, .icon-picker').fontIconPicker({
            theme: 'fip-ae',
            source: wc_sa_icons_array,
            prefix_class: 'wc-sa-icon-uni',
            emptyIcon: false,
            hasSearch: false,
            onchange: function (a, b) {
                if (a.hasClass('status_icon')) {
                    $('.wc-sa-icon-color span, .wc-sa-icon-outline span').attr('class', 'wc-sa-icon-uni' + b);
                }
            }
        });
    }).trigger('wc-sa-init-iconpicker');

    $('[name="post_title"]').change(function (event) {
        var title = $(this).val();
        title = title.trim();
        if (title != '') {
            $('.wc-sa-text-color, .wc-sa-text-outline').text(title);
        }
    }).trigger('change');

    $('#status_email_notification').change(function (event) {
        if ($(this).is(':checked')) {
            $('.show_if_email_notification').show();
        } else {
            $('.show_if_email_notification').hide();
        }
    }).trigger('change');

    $('#status_email_message').change(function (event) {
        if ($(this).is(':checked')) {
            $('.show_if_custom_message').show();
        } else {
            $('.show_if_custom_message').hide();
        }
    }).trigger('change');

    $('#status_customer_account').change(function (event) {
        if ($(this).is(':checked')) {
            $('.show_if_customer_account').show();
        } else {
            $('.show_if_customer_account').hide();
        }
    }).trigger('change');

    $('#status_automatic_trigger').change(function (event) {
        if ($(this).is(':checked')) {
            $('.show_if_automatic_trigger').show();
        } else {
            $('.show_if_automatic_trigger').hide();
        }
    }).trigger('change');

    $('#status_email_recipients').change(function (event) {
        if ($(this).val() == 'custom') {
            $('.show_if_email_recipients_custom').show();
        } else {
            $('.show_if_email_recipients_custom').hide();
        }
    }).trigger('change');

    $('#status_customer_cancel_orders').change(function (event) {
        if ($(this).is(':checked')) {
            $('.show_if_cancel_orders').show();
        } else {
            $('.show_if_cancel_orders').hide();
        }
    }).trigger('change');

    if ($('form#post').length && $('#post_type').val() == 'wc_custom_statuses') {
        $('form#post').submit(function (event) {
            var error = '',
                is_core = $('.panel-wrap').hasClass('status-core');
            $('#status_validation').remove();
            if ($('#title').val() == '') {
                error += '<p>' + wc_sa_error_validation.name + '</p>';
            }
            if ($('#status_label').val() == '') {
                error += '<p>' + wc_sa_error_validation.label + '</p>';
            }
            if ($('#status_email_notification').is(':checked')) {

                if ($('#status_email_from_name').val() == '') {
                    error += '<p>' + wc_sa_error_validation.fromname + '</p>';
                }
                if ($('#status_email_from_address').val() == '') {
                    error += '<p>' + wc_sa_error_validation.fromemail + '</p>';
                }
                if ($('#status_email_subject').val() == '' && !is_core) {
                    console.log(is_core);
                    error += '<p>' + wc_sa_error_validation.email + '</p>';
                }
                if ($('#status_email_heading').val() == '' && !is_core) {
                    error += '<p>' + wc_sa_error_validation.emailhead + '</p>';
                }

            }

            if ($('#status_customer_account').is(':checked')) {
                if ($('#status_customer_account_visibility').val() == '') {
                    error += '<p>' + wc_sa_error_validation.visibility_rule + '</p>';
                }
                if ($('#status_customer_account_button_label').val() == '') {
                    error += '<p>' + wc_sa_error_validation.buttonlabel + '</p>';
                }
            }
            if ($('#status_automatic_trigger').is(':checked')) {
                if ($('#status_triggered_status').val() == '') {
                    error += '<p>' + wc_sa_error_validation.triggeredstatus + '</p>';
                }
                if ($('#status_time_period').val() == '') {
                    error += '<p>' + wc_sa_error_validation.timeperiod + '</p>';
                }
            }

            if (error != '') {
                var html = '<div id="status_validation" class="notice notice-error">' + error + '</div>';
                $('.wp-header-end').after(html);
                return false;
            }

        });
    }

    var frame,
        attArr = [],
        metaBox = $('#wc-sa-options.postbox'), // Your meta box id here
        addImgLink = metaBox.find('#choose_email_attachment'),
        imgContainer = metaBox.find( '#attachments_container'),
        imgIdInput = metaBox.find( '#status_email_attachment' );


    if(addImgLink.length){
        addImgLink.on('click', function(e){
            e.preventDefault();

            if ( frame ) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: 'Select or Upload Attachments',
                button: {
                    text: 'Use this media'
                },
                multiple: true
            });

            frame.on( 'select', function() {

                var attachments = frame.state().get('selection');

                attachments.each(function(val, i){
                    var att = val.toJSON(),
                        url = "";
                    if(att.mime == "image/png" || att.mime == "image/jpeg" ){
                        url = att.url;
                    }else{
                        url = att.icon;
                    }
                    imgContainer.append( '<span class="att-wrapper" data-id="'+att.id+'"><span class="att-clr"></span><img src="'+url+'"/></span>' );
                    attArr.push(att.id);
                });

                imgIdInput.val( attArr );

            });

            frame.open();
        });

        metaBox.on( 'click', '.att-clr', function( event ){

            var media_id = $(this).parent().data('id');
            var values = imgIdInput.val().split(',').filter(function(val){
                return val != media_id;
            });
            attArr = values;

            if(values){
                imgIdInput.val(values);
            }

            $(this).parent().remove();

        });
    }
});

jQuery(document).ready(function () {
    var go_back = jQuery('#go-back-button');
    jQuery('h1.wp-heading-inline').after(go_back.show());
    //remove publish metabox details
    jQuery('#use-style-icon').on('click', function () {
        var val = jQuery('#status_icon').val();
        jQuery('#status_action_icon').val(val);
        jQuery('span.selected-icon').html('<i class="wc-sa-icon-uni' + val + '"></i>');
    });
});
