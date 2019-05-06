<!DOCTYPE HTML>
<html lang="en-GB">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Development - Invoice </title>
    <style type="text/css">


        /* ==========*
         * HTML TAGS *
         * ==========*/

        html, body {
            background: #FFFFFF;
        }

        body {
            display: block;
            color: #000000;
            font: normal 14px/130% Verdana, Arial, Helvetica, sans-serif;
            margin: 8px;
            -webkit-print-color-adjust: exact;
        }

        a {
            color: #000000;
        }

        hr {
            margin-top: 1em;
        }

        blockquote {
            border-left: 10px solid #DDD;
            color: #444444;
            font-style: italic;
            margin: 1.5em;
            padding-left: 10px;
        }

        h1, h2, h3, h4, h5, h6 {
            color: #000000;
            line-height: 150%;
        }

        h1 {
            font-size: 32px;
        }

        h2 {
            font-size: 28px;
        }

        h3 {
            font-size: 24px;
        }

        h4 {
            font-size: 20px;
        }

        h5 {
            font-size: 16px;
        }

        h6 {
            font-size: 12px;
        }

        /* Creates a separator between multiple documents */
        body > div.container .separator {
            border-top: 2px dashed #DDDDDD;
            border-bottom: none;
            margin: 50px 0 0;
        }

        body > div.container:last-child .separator {
            display: none;
        }

        /* =============== *
         * UTILITY CLASSES *
         * =============== */

        .left {
            float: left;
        }

        .align-left {
            text-align: left;
        }

        .right {
            float: right;
        }

        .align-right {
            text-align: right;
        }

        .center {
            float: none;
            margin: 0 auto;
            text-align: center;
            width: 100%;
        }

        .align-center {
            text-align: center;
        }

        .clear {
            clear: both;
        }

        .container {
            background: #FFF;
            margin: 1em auto;
            padding: 2em;
        }

        .container header,
        .container main,
        .container footer {
            margin: 0 auto;
            max-width: 960px;
        }

        .visible-print-block,
        .visible-print-inline,
        .visible-print-inline-block {
            display: none !important;
        }

        footer hr {
            display: none;
        }

        /* ============= *
         * ORDER DETAILS *
         * ============= */

        .title a {
            font-size: 36px;
            font-weight: bold;
            text-decoration: none;
        }

        .title,
        .subtitle {
            margin: 0;
        }

        .left .logo {
            padding-right: 1em;
        }

        .right .logo {
            padding-left: 1em;
        }

        .company-title.left {
            padding-right: 1em;
        }

        .company-title.right {
            padding-left: 1em;
        }

        .company-information {
            margin-bottom: 3em;
        }

        .company-address {
            font-style: normal;
        }

        .company-address.has-logo {
            padding-top: 1em;
        }

        .customer-addresses {
            margin-left: -15px;
            margin-right: -15px;
        }

        .customer-addresses .column {
            padding: 0 15px;
            width: 33.33333333%;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

        .document-heading {
            margin: 2em 0;
        }

        .order-info {
            margin-bottom: 0;
        }

        .order-date {
            color: #666666;
            margin: 0;
        }

        span.coupon {
            background: #F4F4F4;
            color: #333;
            font-family: monospace;
            padding: 2px 4px;
        }

        /* ===== *
         * LISTS *
         * ===== */

        dl {
            margin: 1em 0;
        }

        dl.variation {
            font-size: 0.85em;
            margin: 0;
        }

        dl.variation dt {
            float: left;
            margin: 0 5px 0 0;
        }

        dl.variation dd {
            display: inline;
            margin: 0;
        }

        dl.variation p {
            margin: 0;
        }

        /* ============ *
         * ORDER TABLES *
         * ============ */

        table {
            border-collapse: collapse;
            font: normal 14px/130% Verdana, Arial, Helvetica, sans-serif;
            margin: 3em 0 2em;
            text-align: left;
            width: 100%;
        }

        table td,
        table th {
            background: #FFFFFF;
            border: 1px solid #DDDDDD;
            font-weight: normal;
            padding: 0.8em 1.2em;
            text-transform: none;
            vertical-align: top;
        }

        table th {
            font-weight: bold;
            -webkit-print-color-adjust: exact;
        }

        table thead.order-table-head th {
            background-color: #333333;
            border-color: #333333;
            color: #FFFFFF;
        }

        table tbody th a {
            color: #333333;
            font-weight: bold;
        }

        table tbody.order-table-footer td {
            border-color: #CCCCCC;
            border-width: 1px 0 0 0;
            border-style: solid;
            text-align: right;
        }

        table tbody tr.heading th {
            background-color: #666666;
            border-color: #666666;
            color: #FFFFFF;
        }

        table tbody tr.heading th.order-number a {
            color: #FFF;
            font-weight: bold;
            text-decoration: none;
        }

        table tbody tr.heading th.no-items {
            background-color: #A0A0A0;
            font-weight: 400;
        }

        table tbody tr.heading th.breadcrumbs {
            background-color: #D8D8D8;
            border-color: #D8D8D8;
            color: #666666;
            font-weight: normal;
        }

        table tbody tr.even,
        table tbody tr.even td {
            background-color: #F5F5F5;
        }

        tbody tr.odd,
        tbody tr.odd td {
            background-color: #FFFFFF;
        }

        thead th.id,
        tbody td.id,
        thead th.id > span,
        tbody td.id > span {
            border: 0;
            display: none;
            overflow: hidden;
            padding: 0;
            visibility: hidden;
        }

        .quantity,
        .total-quantity {
            text-align: center;
        }

        .price,
        .weight,
        .total-weight {
            text-align: right;
        }

        /* ============ *
         * PRINT STYLES *
         * ============ */

        @media print {

            /* Background is always white in print */
            html, body {
                background: #FFFFFF;
            }

            a {
                text-decoration: none;
            }

            /* Multiple document separators are not printed */
            body > div.container .separator {
                display: none;
            }

            /* Break pages when printing multiple documents */
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

            /* Print URL after link text */
            .document-heading a:after,
            .document-footer a:after {
                content: " (" attr(href) ")";
            }

            .visible-print-block {
                display: block !important;
            }

            .visible-print-inline {
                display: inline !important;

            }

            .visible-print-inline-block {
                display: inline-block !important;
            }

            .hidden-print {
                display: none !important;
            }
        }

    </style>

    <style type="text/css">
        .facsimile-ribbon {
            background: #0073AA;
            letter-spacing: 1px;
            line-height: 50px;
            color: #FFFFFF;
            opacity: .86;
            position: fixed;
            text-align: center;
            width: 200px;
            z-index: 9999;
            top: 25px;
            right: -50px;
            left: auto;
            transform: rotate(45deg);
            -webkit-transform: rotate(45deg);
        }

        .demo_store {
            display: none !important;
        }

        body > div.container:after {
            display: none;
        }
    </style>
</head>
<body id="woocoomerce-pip" class="woocommerce-pip invoice">
<div id="package-<?= $order->get_id() ?>" class="container">
    <header>
        <div class="document-header invoice-header">
            <h1 class="title order-info">
                <?= __('Delivery Note', 'woocommerce_status_actions') ?>
            </h1>
            <table class="customer-details">
                <tr>
                    <th colspan="2"><?= __('Order Details', 'woocommerce_status_actions') ?></th>
                </tr>
                <tr>
                    <td><?= __('Order Number', 'woocommerce_status_actions') ?></td>
                    <td><span class="order-number visible-print-inline"><?= $order->get_id() ?></span><a
                                class="order-number hidden-print" href="<?= get_edit_post_link($order->get_id()) ?>"
                                target="_blank">#<?= $order->get_id() ?></a></td>
                </tr>
                <tr>
                    <td><?= __('Order Date', 'woocommerce_status_actions') ?></td>
                    <td><?= date(wc_date_format(), strtotime($order->get_date_created())) ?></td>
                </tr>
            </table>
            <table class="customer-address">
                <tr>
                    <th>
                        <?= __('Shipping Address', 'woocommerce_status_actions') ?>
                    </th>
                </tr>
                <tr>
                    <td>
                        <address class="customer-address">
                            <?= $order->get_formatted_shipping_address() ?>
                        </address>
                    </td>
                </tr>
            </table>
            <div class="customer-note">
                <blockquote>
                    <?= ($order->get_customer_note()) ? $order->get_customer_note() : '-' ?>
                </blockquote>
            </div>
        </div>
    </header>
    <main class="document-body invoice-body">
        <table class="order-table invoice-order-table">
            <thead class="order-table-head">
            <tr>
                <th class="sku"><?= __('SKU', 'woocommerce_status_actions') ?></th>
                <th class="product"><?= __('Product', 'woocommerce_status_actions') ?></th>
                <th class="quantity"><?= __('Packed', 'woocommerce_status_actions') ?></th>
                <th class="price"><?= __('Price', 'woocommerce_status_actions') ?></th>
            </tr>
            </thead>
            <tbody class="order-table-body">
            <?php foreach ($order->get_items() as $item_id => $item) { ?>
                <?php $product = wc_get_product($item->get_product_id()) ?>
                <tr class="row table-item odd">
                    <td class="sku"><?php echo $product->get_sku() ?></td>
                    <td class="product">
                        <span class="product product-simple"><a href="#"
                                                                target="_blank"><?php echo $product->get_name() ?></a></span>
                    </td>
                    <td class="quantity"><?php echo $item->get_quantity() ?></td>
                    <td class="price"><?php echo wc_price($item->get_total() / $item->get_quantity()) ?></td>
                    <td class="id"><span data-item-id="0"></span></td>
                </tr>
                <?php $row_colour = ($row_colour == 'grey') ? 'white' : 'grey' ?>
            <?php } ?>
            </tbody>
            <tfoot class="order-table-footer">
            </tfoot>
        </table>
    </main>
    <footer>

    </footer>
</div>
</body>
</html>