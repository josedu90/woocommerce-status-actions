jQuery(document).ready(function () {
    if (jQuery('#trigger_options_days_off').length) {
        var days_off = JSON.parse(jQuery('#trigger_options_days_off').val());
        var calendar = jQuery('#days_off').multiDatesPicker({
            dateFormat: 'dd/mm/yy',
            onSelect: function () {
                jQuery('#trigger_options_days_off').val(jQuery(this).multiDatesPicker('getDates'));
            },
        });
        if (days_off[0]) {
            calendar.multiDatesPicker('addDates', days_off);
        }
        jQuery('#trigger_options_days_off').val(jQuery('#days_off').multiDatesPicker('getDates'));
    }

    jQuery('#refresh-printers').on('click', function (e) {
        e.preventDefault();
        var data = {action: 'wc_sa_refresh_cloud_printers'};
        jQuery('#refresh-printers').text('Updating');
        $.ajax({
            url: wc_sa_opt.ajax_url,
            type: 'POST',
            dataType: 'html',
            data: data,
        })
            .done(function (result) {
                jQuery('.available-printers-list').html(result);
            })
            .fail(function () {

            })
            .always(function (result) {
                jQuery('#refresh-printers').text('Refresh');
            });
    });

    jQuery('.button.preview').on('click', function (e) {
        e.preventDefault();
        console.log(window.location);
        jQuery('.order_number_error').hide();
        var order_id = jQuery('#wc_sa_preview_order_id').val();
        if (!order_id) {
            jQuery('.order_number_error').show();
            return;
        }
        var type = jQuery(this).data('preview');
        var url = window.location.origin + '?google_printer_action=order_preview&order_id=' + order_id + '&type=' + type;
        window.open(url, '_blank');
    })

    // Uploading files
    var file_frame;
    var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
    var set_to_post_id = jQuery('#wc_sa_template_image').val(); // Set this
    jQuery('#upload_image_button').on('click', function (event) {
        event.preventDefault();
        // If the media frame already exists, reopen it.
        if (file_frame) {
            // Set the post ID to what we want
            file_frame.uploader.uploader.param('post_id', set_to_post_id);
            // Open frame
            file_frame.open();
            return;
        } else {
            // Set the wp.media post id so the uploader grabs the ID we want when initialised
            wp.media.model.settings.post.id = set_to_post_id;
        }
        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select a image to upload',
            button: {
                text: 'Use this image',
            },
            multiple: false	// Set to true to allow multiple files to be selected
        });
        // When an image is selected, run a callback.
        file_frame.on('select', function () {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();
            // Do something with attachment.id and/or attachment.url here
            $('#ae-invoice-logo').attr('src', attachment.url);
            $('#wc_sa_template_image').val(attachment.id);
            // Restore the main post ID
            wp.media.model.settings.post.id = wp_media_post_id;
        });
        // Finally, open the modal
        file_frame.open();
    });
    // Restore the main ID when the add media button is pressed
    jQuery('a.add_media').on('click', function () {
        wp.media.model.settings.post.id = wp_media_post_id;
    });

    jQuery('#remove_image_button').on('click', function () {
        $('#ae-invoice-logo').attr('src', '');
        $('#wc_sa_template_image').val('');
    });
});
