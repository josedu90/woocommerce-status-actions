<!DOCTYPE HTML>
<html>
<head><!--

<script type='text/javascript' src='<?= WC_SA()->assets_url . 'plugins/JsBarcode/dist/JsBarcode.all.min.js' ?>'>
</script>
-->
<style type="text/css">
    body {
        font-size: 12px;
        font-family: Arial, sans-serif;
    }
    h1 {
        margin-top: 0;
        padding-top: 0;
    }

    small.labels {
        font-weight: bold;
        display: inline-block;
        margin-bottom: 6px;
    }

    small.footer {
        margin: 5px 0;
        color: #555;
        display: inline-block;
    }
    
    hr {
        border: 1px solid #eee;
    }

    table.ae-invoice-table,
    table.ae-invoice-address,
    table.ae-invoice-contact {
        width: 100%;
        border-collapse: collapse;
    }

    table.ae-invoice-table th {
        font-weight: bold;
        border-bottom: 1px solid #333;
    }
	.product_sku {
		line-height: 2;
		color: #555;
	}
    table.ae-invoice-table th,
    table.ae-invoice-table td,
    table.ae-invoice-address td,
    .ae-invoice-header td,
    .ae-invoice-contact td {
        padding-top: 12px;
        vertical-align: top;
    }

    .ae-invoice-table {
        margin: 12px 0;
    }

    .ae-invoice-table td:last-child {
        text-align: right;
    }
    .ae-invoice-table td:last-child {
        border-left: 1px solid #aaa;
    }
    .ae-invoice-table tbody td {
        border-bottom: 1px solid #eee;
    }

    .ae-invoice-table tr td:last-child,
    .ae-invoice-table tr th:last-child {
        padding: 12px 0;
    }

    .ae-invoice-table tr td {
        border-left: 1px solid #aaa;
    }
    
    .ae-invoice-table tr td:first-child,
    .ae-invoice-table tr th:first-child {
        border-left: 0;
        padding: 12px 0;
    }
	.product-simple {
		font-weight: 600;
		display: inline-block;
		margin-bottom: 5px;
	}
	ul.variation li {
		font-size: 12px;
	}
    h1 {
        display: inline-block;
    }
    table.ae-invoice-header {
        float: right;
        width: 50%;
    }
    table.ae-invoice-header small.labels {
        margin-right: -4px;
    }
    table.ae-invoice-header td {
        padding-top: 12px;
    }

    table.ae-invoice-address th,
    table.ae-invoice-address td,
    table.ae-invoice-contact td {
        width: 50%;
    }
    table.ae-invoice-table td.quantity,
    table.ae-invoice-table th.quantity {
        text-align: center;
    }

    table.ae-invoice-address th.product {
        text-align: left;
    }

    table.ae-invoice-table th {
        text-align: left;
        padding: 12px;
    }

    table.ae-invoice-contact {
        margin-bottom: 20px;
    }

    .ae-invoice-table-footer .cart_subtotal {
        text-align: right;
        padding: 12px !important;
    }
    .invoice_title {
        float: right;
    }
    #image-preview {
        float: left;
    }
    .clear {
        clear: both;
    }
    .header-line {
        margin-bottom: 20px;
        padding: 0 30px;
        display: block;
    }
    .ae-invoice-shop {
		float: left;
    }
    .ae-invoice-tax {
        margin-top: 5px;
    }
    .ae-invoice-table-footer tr:last-child .woocommerce-Price-amount {
        font-weight: bold;
    }
    .ae-invoice-table th.price {
        text-align: right;
    }
    @media print {
        html, body {
	        background: #fff;
        }
        .container {
			page-break-after: always;
		}
		.container:last-child {
			page-break-after: auto;
		}

		table {
			page-break-inside: auto;
		}

		table tr {
			page-break-inside: avoid;
			page-break-after: auto;
		}

		table td,
		table th {
			padding: 0.4em 1.2em;
			page-break-inside: avoid;
			page-break-after: auto;
		}
    }
</style>
</head>
<body id="actuality-print" class="actuality-print invoice">
<h1 class="invoice_title">
    <?php _e('Invoice', 'woocommerce_status_actions') ?>
</h1>
<?php if (get_option('wc_sa_template_image')) {
    $img = wp_get_attachment_url(get_option('wc_sa_template_image')); ?>
    <img id=' ' src='<?php echo $img ?>' style='max-height: 24px;'>
<?php } ?>
<div class="clear"></div>
<span class="ae-invoice-shop">
    <?php echo bloginfo('name'); ?><br>
    <?php $store_address = sprintf('%s<br> %s<br> %s, %s<br>', get_option('woocommerce_store_address'), get_option('woocommerce_store_address_2'), get_option('woocommerce_store_city'), get_option('woocommerce_store_postcode')); ?>
    <?= $store_address ?>
    <br>
    <?php if (get_option('wc_sa_tax_number')) { ?>
    <small class="labels ae-invoice-tax"><?php _e('Tax Number', 'woocommerce_status_actions') ?></small>
    <br>
    <?php echo get_option('wc_sa_tax_number'); ?>
	<?php } ?>
</span>
<table class="ae-invoice-header">
    <tr>
        <td style="width: 50%;">
            <small class="labels"><?php _e('Order', 'woocommerce_status_actions') ?></small>
            <br>
            #<?php echo $order->get_id() ?>
        </td>
        <td style="text-align: right;">	
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <small class="labels"><?php _e('Date', 'woocommerce_status_actions') ?></small>
            <br>
            <?php echo date_i18n(wc_date_format(), strtotime($order->get_date_created())) ?>
        </td>
    </tr>
</table>
<div class="container>">
<div class="clear"></div>
<table class="ae-invoice-address">
    <tr>
        <td class="ae-invoice-bill-to">
            <small class="labels"><?php _e('Billing Address', 'woocommerce_status_actions') ?></small>
            <br>
            <?php echo $order->get_formatted_billing_address() ?>
        </td>
        <td class="ae-invoice-ship-to">
            <small class="labels"><?php _e('Shipping Address', 'woocommerce_status_actions') ?></small>
            <br>
            <?php echo $order->get_formatted_shipping_address() ?>
        </td>
    </tr>
</table>
<table class="ae-invoice-contact">
    <tr>
        <td>
            <small class="labels"><?php _e('Email', 'woocommerce_status_actions') ?></small>
            <br>
            <?php echo $order->get_billing_email() ?>
        </td>
        <td>
            <small class="labels"><?php _e('Telephone', 'woocommerce_status_actions') ?></small>
            <br>
            <?php echo $order->get_billing_phone() ?>
        </td>
    </tr>
    <tr>
        <td>
            <?php if ($order->get_used_coupons()) { ?>
                <small class="labels"><?php _e('Coupons', 'woocommerce_status_actions') ?></small>
                <br>
                <?php
                foreach ($order->get_used_coupons() as $coupon) {
                    echo '<code>' . $coupon . '</code>';
                }
            } ?>
        </td>
        <td>
			<?php if ($order->get_customer_note()) { ?>
				<small class="labels"><?php _e('Note', 'woocommerce_status_actions') ?></small>
				<br>
				<?php echo $order->get_customer_note() ?>
			<?php } ?>
        </td>
    </tr>
</table>
<span>
	<?php
    echo get_option('wc_sa_header');
    ?>
</span>
<table class="ae-invoice-table">
    <thead>
    <tr>
        <th class="product"><?php _e('Product', 'woocommerce_status_actions') ?></th>
        <th class="quantity"
            style="width: 10%;"><?php _e('Quantity', 'woocommerce_status_actions') ?></th>
        <th class="price"
            style="width: 20%;"><?php _e('Price', 'woocommerce_status_actions') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($order->get_items() as $key => $item) { ?>
        <?php
        $product = wc_get_product($item->get_product_id());
        ?>
        <tr class="row table-item">
            <td class="product">
                <span class="product product-simple"><?php echo $product->get_name() ?></span><br>
                <?php if ($product->get_sku()) { ?>
                	<span class="product_sku"><?php echo $product->get_sku() ?></span><br>
                <?php } ?>
                <?php if ($item->get_variation_id() && $meta_data = $item->get_formatted_meta_data('')) { ?>
                    <ul class="variation">
                        <?php
                        foreach ($meta_data as $meta_id => $meta) {
                            if (in_array($meta->key, $hidden_order_itemmeta, true)) {
                                continue;
                            }
                            ?>
                            <li class="meta-label variation-<?php echo $meta_id ?>"><?php echo esc_attr($meta->display_key); ?>:<span class="variation-<?php echo $meta_id ?>">
                                <?php echo esc_textarea(rawurldecode($meta->value)); ?></span></li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </td>
            <td class="quantity"><small>&times;&nbsp;</small><?php echo $item->get_quantity() ?></td>
            <td class="price"><?php echo wc_price($item->get_total()) ?></td>
        </tr>
    <?php } ?>
    </tbody>
    <tfoot class="ae-invoice-table-footer">
    <?php foreach ($order->get_order_item_totals() as $key => $value) { ?>
        <tr>
            <td class="cart_subtotal" colspan="2">
                <strong class="order-<?php echo $key ?>"><?php echo $value['label'] ?></strong>
            </td>
            <td class="value">
                <span class="amount"><?php echo $value['value'] ?></span>
            </td>
        </tr>
    <?php } ?>
    </tfoot>
</table>
</div>
<small class="footer">    <?php echo get_option('wc_sa_returns_policy'); ?> </small>
<?php if (get_option('wc_sa_footer')) { ?>
    <hr>
<?php } ?>
<small class="footer"> <?php echo get_option('wc_sa_footer'); ?> </small>
</body><!--

<script>
    JsBarcode(".barcode").init();
</script>
-->
</html>