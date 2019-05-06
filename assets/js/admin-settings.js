jQuery(function ($) {
    /*$('.change_colour').wpColorPicker();*/
    $('#wc_order_statuses_reset_defaults').click(function (event) {
        $('table tr.wc_order_statuses').each(function (index, el) {
            var key = $(el).data('statuskey');
            var label = key;
            if (key.substring(0, 3) == 'wc-')
                label = key.substring(3);

            $(el).find('input.statusname').val(wc_sa_defaults.labels[key]);
            $(el).find('input.statuslabel').val(label);
            $(el).find('input.statuscolor').val(wc_sa_defaults.colors[key]).trigger('change');

            if (typeof wc_sa_defaults.editing[key] == 'undefined')
                $(el).find('input.default_editing').attr('checked', false).trigger('change');
            else
                $(el).find('input.default_editing').attr('checked', 'checked').trigger('change');

        });
    });
    $('#reset_payment_gateways_st').click(function (event) {
        $('.payment_gateways_st').prop('value', '');
        return false;
    });

    $(document.body).on('wc-sa-init-colorpicker', function () {
        $('.color-picker-field').iris({
            change: function (event, ui) {
                $(this).parent().find('.colorpickpreview').css({backgroundColor: ui.color.toString()});
            },
            width: 280,
            hide: true,
            border: true,
            palettes: ['#828282', '#c62828', '#ef6c00', '#fdd835', '#559f55', '#386b98', '#6a1b9a']
        }).on('click focus', function (event) {
            event.stopPropagation();
            $('.iris-picker').hide();
            $(this).closest('td').find('.iris-picker').show();
            $(this).data('original-value', $(this).val());
        });
        $('body').on('click', function () {
            $('.iris-picker').hide();
        });
    }).trigger('wc-sa-init-colorpicker');

    $('.wc_sa_order_statuses_container select').selectWoo({
        multiple: true
    });
    $('.workflow_status_add').on('click', function () {
        var data = $(this).siblings('select').selectWoo('data');
        $("#wc_sa_status_workflow_tbody").find("tr").remove();
        $.each(data, function(i, val){
            var $html = '<tr data-status-key="'+val.id+'"><td class="sort column-sort ui-sortable-handle"><input type="hidden" name="wc_sa_workflow_order['+val.id+']" class="wc_sa_workflow_order_item" value="'+val.text+'"></td><td>'+val.text+'</td><td></td></tr>';
            $('#wc_sa_status_workflow_tbody').append($html);
        });
        $('.tb-close-icon').click();
    });
});
