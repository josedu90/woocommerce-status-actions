<?php
/**
 * Edit WooCommerce Order page
 *
 * @author   Actuality Extensions
 * @category Admin
 * @package  WC_SA/Admin
 * @version  1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_SA_Order')) :

    /**
     * WC_SA_Order.
     */
    class WC_SA_Order
    {

        /**
         * Constructor.
         */
        public function __construct()
        {
            if (is_admin()) {
                add_action('admin_footer', array($this, 'bulk_admin_footer'), 99);
                add_filter('woocommerce_admin_order_actions', array($this, 'admin_order_actions'), 199, 2);
                add_action('wc_order_is_editable', array($this, 'order_is_editable'), 50, 2);
            }
            add_action('woocommerce_order_status_changed', array($this, 'order_status_changed'), 777, 3);
            add_action('woocommerce_order_is_download_permitted', array($this, 'order_is_download_permitted'), 777, 2);
            add_filter('woocommerce_payment_complete_order_status', array($this, 'payment_complete_order_status'));
            add_action('woocommerce_thankyou', array($this, 'change_payments_method_status'), 777, 1);
            add_filter('views_edit-shop_order', array($this, 'change_order_labels'));
        }

        /**
         * Add extra bulk action options to mark orders as complete or processing.
         *
         */
        public function bulk_admin_footer()
        {
            global $post_type;
            if ('shop_order' == $post_type) {
                $user_id = get_current_user_id();
                $order_statuses = wc_sa_get_statuses(true);
                $need_remove = array('processing', 'completed', 'on-hold');
                ?>
                <script type="text/javascript" id="sa-status-bulk-actions">
                    jQuery(function () {

                        var $optgroup = jQuery('<optgroup>').attr('label', '<?php _e('Core statuses', 'woocommerce_status_actions'); ?>');

                        <?php foreach($need_remove as $slug){ ?>
                        jQuery('select[name="action"] option[value="mark_<?php echo $slug; ?>"], select[name="action2"] option[value="mark_<?php echo $slug; ?>"]').remove();
                        <?php } ?>
                        <?php foreach ($order_statuses as $st_id => $status) {
                        $hide = get_post_meta($st_id, '_hide_bulk_actions', true);
                        if (($hide == 'yes') || ($status->users_to && !in_array($user_id, $status->users_to)) || !wc_sa_is_core_status($status->label)) continue;
                        ?>
                        jQuery('<option>').val('mark_<?php echo $status->label; ?>').text('<?php printf(__('Change status to %s', 'woocommerce_status_actions'), strtolower($status->title)); ?>').appendTo($optgroup);
                        <?php } ?>

                        if ($optgroup.find('option').length) {
                            $optgroup.appendTo('select[name="action"], select[name="action2"]');
                        }
                        var $optgroup = jQuery('<optgroup>').attr('label', '<?php _e('Custom statuses', 'woocommerce_status_actions'); ?>');

                        <?php foreach ($order_statuses as $st_id => $status) {
                        $hide = get_post_meta($st_id, '_hide_bulk_actions', true);
                        if (($hide == 'yes') || ($status->users_to && !in_array($user_id, $status->users_to)) || wc_sa_is_core_status($status->label)) continue;
                        ?>
                        jQuery('<option>').val('mark_<?php echo $status->label; ?>').text('<?php printf(__('Change status to %s', 'woocommerce_status_actions'), strtolower($status->title)); ?>').appendTo($optgroup);
                        <?php } ?>
                        if ($optgroup.find('option').length) {
                            $optgroup.appendTo('select[name="action"], select[name="action2"]');
                        }
                    });
                </script>
                <?php
                $this->note_promt();
            }
        }

        public function admin_order_actions($actions, $the_order)
        {
            $new_actions = array();
            $statuses = wc_sa_get_statuses(true);
            $order_statuses = wc_get_order_statuses();
            if ($statuses) {
                foreach ($statuses as $st_id => $value) {
                    $status = new WC_SA_Status($st_id);
                    $display_for = $status->action_visibility;
                    $o_st = 'wc-' . $the_order->get_status();
                    if (in_array($o_st, $display_for)) {

                        $aicod = $status->action_icon;
                        $_a = array($status->label, 'wc-sa-action-icon', 'wc-sa-icon-uni' . $aicod);
                        if ($status->order_note_prompt == 'yes') {
                            $_a[] = 'sa_note_prompt';
                        }

                        $new_actions[$status->label] = array(
                            'url' => wp_nonce_url(admin_url('admin-ajax.php?action=woocommerce_mark_order_status&status=' . $status->label . '&order_id=' . $the_order->get_id()), 'woocommerce-mark-order-status'),
                            'name' => $status->title,
                            'action' => implode(' ', $_a)
                        );
                    }
                    if ($the_order->get_status() == $status->label) {
                        if (!empty($status->show_action_buttons)) {
                            foreach ($status->show_action_buttons as $st_key) {
                                $_key = substr($st_key, 3);
                                if (isset($order_statuses[$st_key])) {
                                    $_action = $_key;
                                    $name = $order_statuses[$st_key];
                                    switch ($_key) {
                                        case 'completed':
                                            $_action = 'complete';
                                            $name = __('Complete', 'woocommerce');
                                            break;
                                    }
                                    $new_actions[$_action] = array(
                                        'url' => wp_nonce_url(admin_url('admin-ajax.php?action=woocommerce_mark_order_status&status=' . $_key . '&order_id=' . $the_order->get_id()), 'woocommerce-mark-order-status'),
                                        'name' => $name,
                                        'action' => $_action
                                    );
                                }
                            }
                        }
                    }
                }

                if(!empty($new_actions)){
                    foreach ($actions as $key => $action){
                        $_key = $key == 'complete' ? 'completed' : $key;
                        if(array_key_exists('wc-' . $_key, $order_statuses)){
                            unset($actions[$key]);
                        }
                    }
                }
            }
            return array_merge($new_actions, $actions);
        }

        private function note_promt()
        {
            ?>
            <script type="text/html" id="tmpl-wc_as_note_prompt-modal">
                <div class="media-frame-title">
                    <h1><?php _e('Add Note', 'woocommerce_status_actions'); ?></h1>
                </div>
                <form class="wc_as_note_prompt_form" method="post">
                    <div class="media-frame-content" data-columns="10">
                        <?php _e('Add a note for your reference or a note to the customer. The customer will be notified.', 'woocommerce_status_actions'); ?>
                        <textarea id="add_order_note" class="input-text" rows="5" name="sa_order_note"
                                  type="text"></textarea>
                        <p>
                            <select id="order_note_type" name="sa_order_note_type">
                                <option value=""><?php _e('Private note', 'woocommerce_status_actions'); ?></option>
                                <option value="customer"><?php _e('Note to customer', 'woocommerce_status_actions'); ?></option>
                            </select>

                        </p>
                    </div>
                    <div class="media-frame-toolbar">
                        <div class="media-toolbar">
                            <div class="media-toolbar-primary search-form">
                                <button type="submit"
                                        class="button  button-primary media-button"><?php _e('Add', 'woocommerce_status_actions'); ?></button>
                            </div>
                        </div>
                    </div>
                </form>
            </script>
            <?php
        }

        public function order_status_changed($order_id, $old_status, $new_status)
        {
            $note = isset($_POST['sa_order_note']) ? trim($_POST['sa_order_note']) : '';
            $order = wc_get_order($order_id);
            $note = apply_filters('bulk_handler_custom_action_note', $note, $new_status, $order);
            if (!empty($note)) {
                $is_customer_note = isset($_POST['sa_order_note_type']) && $_POST['sa_order_note_type'] == 'customer' ? true : false;
                $order->add_order_note($note, $is_customer_note, true);
            }

            $custom = wc_sa_get_statusesList();
            if (isset($custom['wc-' . $new_status])) {
                $status = wc_sa_get_status_by_name($new_status);

                if ($status->stock_status == 'reduce') {
                    wc_reduce_stock_levels($order->get_id());
                } else if ($status->stock_status == 'restore') {
                    $status->restore_order_stock($order);
                }
                if ($status->google_print === 'yes' && get_option('wc_sa_google_cloud_enable', 'no') == 'yes') {
                    $gcpl = new GoogleCloudPrintLibrary_GCPL_v2();
                    $printer_id = ($status->google_print_printer) ? $status->google_print_printer : false;
                    $hidden_order_itemmeta = apply_filters('woocommerce_hidden_order_itemmeta', array(
                        '_qty',
                        '_tax_class',
                        '_product_id',
                        '_variation_id',
                        '_line_subtotal',
                        '_line_subtotal_tax',
                        '_line_total',
                        '_line_tax',
                        'method_id',
                        'cost',
                    ));
                    ob_start();
                    include_once 'templates/html-' . $status->google_print_template . '.php';
                    $html = ob_get_clean();
                    $printed = $gcpl->print_document($printer_id, get_bloginfo('name') . ' - Printed Order #' . $order->get_id(), $html, false, $status->google_print_copies);
                }
                if ($status->automatic_trigger === 'yes') {
                    $triggered_status = $status->triggered_status;
                    $time_period = (int)$status->time_period;
                    $time_period_type = $status->time_period_type;
                    if ($time_period > 0) {
                        $time = strtotime($time_period . ' ' . $time_period_type, 0);
                        $trigger_time = time() + $time;
                        $trigger_time = $this->get_trigger_time($trigger_time);
                        if ($trigger_time) {
                            wp_schedule_single_event($trigger_time, 'wc_sa_run_automatic_trigger', array($order_id, $triggered_status, $new_status));
                        }
                    }
                }
                if ($status->update_order_date === 'yes') {
                    $order->set_date_created(time());
                    $order->save();
                }
            }
            wc_delete_shop_order_transients($order_id);
        }

        public function order_is_download_permitted($permitted, $order)
        {
            $order_status = $order->get_status();
            $statuses = wc_sa_get_statuses_by_meta('_downloads_permitted', 'yes');
            if (in_array($order_status, $statuses)) {
                $permitted = true;
            }

            return $permitted;
        }

        public function order_is_editable($editable, $order)
        {
            $order_status = $order->get_status();
            $statuses = wc_sa_get_statuses_by_meta('_item_editing', 'yes');
            if (in_array($order_status, $statuses)) {
                $editable = true;
            } else if (wc_sa_is_core_status($order_status)) {
                $status = wc_sa_get_status_by_name($order_status);
                if ($status->item_editing == 'yes') {
                    $editable = true;
                } else {
                    $editable = false;
                }
            }

            return $editable;
        }

        public function payment_complete_order_status($new_order_status)
        {
            if ($new_order_status == 'processing') {
                $complete_status = get_option('wc_custom_status_payment_complete_status');
                if ($complete_status && !empty($complete_status))
                    $new_order_status = substr($complete_status, 3);
            }
            return $new_order_status;
        }

        public function change_payments_method_status($order_id)
        {
            if (!$order_id)
                return;

            $saved_st = get_option('wc_sa_payment_gateway_statuses');
            if (!is_array($saved_st)) {
                $saved_st = array();
            }

            $order = new WC_Order($order_id);
            $pm_id = $order->get_payment_method();

            if ($pm_id && !empty($pm_id) && isset($saved_st[$pm_id])) {
                $new_st = $saved_st[$pm_id];
                if (!empty($new_st)) {
                    $new_st = 'wc-' === substr($new_st, 0, 3) ? substr($new_st, 3) : $new_st;
                    $order->update_status($new_st);
                }
            }

        }

        public function get_trigger_time($trigger_time)
        {
            $trigger_options = get_option('trigger_options');
            $trigger_options['days_off'] = explode(',', $trigger_options['days_off']);
            $trigger_day = date('N', $trigger_time);
            if (!$trigger_options['weekdays']) {
                $trigger_time = 0;
            }

            if ($trigger_options['weekdays'] && !$trigger_options['weekdays'][$trigger_day]) {
                $trigger_time = $this->get_trigger_time($trigger_time + 86400); // +24h
            }

            if ($trigger_options['days_off'] && in_array(date('d/m/Y', $trigger_time), $trigger_options['days_off'])) {
                $trigger_time = $this->get_trigger_time($trigger_time + 86400); // +24h
            }

            return $trigger_time;
        }

        public function change_order_labels($views)
        {
            $statuses = wc_get_order_statuses();
            foreach ($statuses as $key => $status){
                if(isset($views[$key])){
                    $views[$key] = preg_replace('/>(\w+) </', '>' . $status . ' <', $views[$key]);
                }
            }
            return $views;
        }

        public function shop_order_views($views)
        {
            var_dump($views);
            return $views;
        }
    }

endif;

new WC_SA_Order();