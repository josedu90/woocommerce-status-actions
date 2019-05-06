jQuery( function ( $ ) {
  	$('.woocommerce-orders-table__cell-order-actions a.cancel').click(function(event) {
       return confirm(wc_sa_opt.i18_prompt_cancel);
    });

    $(".woocommerce-orders-table__cell-order-actions a[class^='prompt_mark_custom_status_'], .woocommerce-orders-table__cell-order-actions a[class*=' prompt_mark_custom_status_']").click(function(event) {
       return confirm(wc_sa_opt.i18_prompt_change);
    });
});
