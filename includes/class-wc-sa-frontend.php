<?php
/**
 * @author   Actuality Extensions
 * @version  1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_SA_Frontend')) :

    /**
     * WC_SA_Frontend Class.
     */
    class WC_SA_Frontend
    {

        public $allow_product_reviews;

        /**
         * Constructor.
         */
        public function __construct()
        {
            add_filter('woocommerce_pre_customer_bought_product', array($this, 'status_allow_product_review'), 99, 4);
            add_filter('woocommerce_valid_order_statuses_for_payment', array(&$this, 'pay_button_handler'));
            add_filter('woocommerce_valid_order_statuses_for_cancel', array(&$this, 'cancel_button_handler'));
            add_filter('woocommerce_my_account_my_orders_actions', array(&$this, 'my_account_my_orders_actions'), 10, 2);
            add_filter('query', array($this, 'wc_customer_bought_product'), 50, 1);
            add_action('woocommerce_before_template_part', array($this, 'show_status_workflow'), 1, 4);
            add_action('woocommerce_order_details_before_order_table', array($this, 'show_status_workflow_order_details'), 1, 1);
        }

        public function pay_button_handler($statuses)
        {
            $st = wc_sa_get_pay_button_statuses();
            if ($st) {

                $statuses = $st;
            }
            return $statuses;
        }

        public function cancel_button_handler($statuses)
        {
            $statuses = wc_sa_get_can_cancel_statuses();
            return $statuses;
        }

        public function my_account_my_orders_actions($actions, $order)
        {
            $st_actions = wc_sa_get_statuses_by_meta('_customer_account', 'yes', true);
            $confirm_prompt = wc_sa_get_statuses_by_meta('_customer_confirm_prompt', 'yes', true);
            $confirm_prompt = array_intersect($confirm_prompt, $st_actions);

            if ($st_actions) {
                $o_status = $order->get_status();
                $order_status = 'wc-' . $o_status;
                foreach ($st_actions as $st_id => $label) {
                    $status = wc_sa_get_status($st_id);
                    $visibility = $status->customer_account_visibility;
                    if (in_array($order_status, $visibility)) {
                        $button_label = $status->customer_account_button_label;
                        $url = wp_nonce_url(
                            admin_url("admin-ajax.php?action=wc_sa_mark_order_status&order_id={$order->get_id()}&status={$label}"), "wc-sa-mark-order-status"
                        );
                        $key = !empty($confirm_prompt) && in_array($label, $confirm_prompt) ? 'prompt_mark_custom_status_' . $label : 'mark_custom_status_' . $label;
                        $actions[$key] = array(
                            'url' => $url,
                            'name' => !empty($button_label) ? $button_label : $status->title
                        );
                    }
                }
            }

            return $actions;
        }

        public function wc_customer_bought_product($query)
        {
            global $wpdb;
            $query = trim($query);
            $pos = strpos($query, "SELECT im.meta_value FROM {$wpdb->posts} AS p");
            if ($pos === 0 && !empty($this->allow_product_reviews)) {
                $query = trim(preg_replace('/\s+/', ' ', $query));
                $pos2 = strpos($query, "p.post_status IN ( 'wc-completed', 'wc-processing' ) AND");
                if ($pos2 !== false) {
                    $statuses = "'wc-" . implode("', 'wc-", $this->allow_product_reviews) . "'";
                    $query = str_replace("p.post_status IN ( 'wc-completed', 'wc-processing' ) AND", "p.post_status IN ( 'wc-completed', 'wc-processing', {$statuses} ) AND", $query);
                }

            }
            return $query;
        }

        public function status_allow_product_review($null, $customer_email, $user_id, $product_id)
        {
            $reviews = $this->get_public_reviews();
            if (empty($reviews)) {
                return $null;
            }

            global $wpdb;
            $user = new WC_Customer(get_current_user_id());
            $statuses = "'wc-" . implode("', 'wc-", $reviews) . "'";
            $result = $wpdb->get_col("
			SELECT im.meta_value FROM {$wpdb->posts} AS p
			INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
			INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
			INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
			WHERE p.post_status IN ( " . $statuses . " )
			AND pm.meta_key IN ( '_billing_email', '_customer_user' )
			AND im.meta_key IN ( '_product_id', '_variation_id' )
			AND im.meta_value != 0
			AND pm.meta_value IN ( '" . $user->get_email() . "' )
		    ");
            if (in_array(absint($product_id), $result)) {
                return true;
            }

            return false;
        }

        public function show_status_workflow($template_name, $template_path, $located, $args)
        {
            $order = isset($args['order']) ? $args['order'] : wc_get_order(0);

            if(!$order){
                return;
            }

            if($template_name != "checkout/thankyou.php"){
                return;
            }

            $order_status = 'wc-' . $order->get_status();
            $workflow_statuses = get_option('wc_sa_workflow_order', array());
            if(!array_key_exists($order_status, $workflow_statuses)){
                return;
            }

            $selected_template = get_option("wc_sa_workflow_template", "wt_list");
            if($selected_template == "wt_list"){
                include_once "templates/html-status-workflow.php";
            }else{
                include_once "templates/html-status-workflow-pie.php";
            }
        }

        public function show_status_workflow_order_details($order)
        {
            if(!$order){
                return;
            }

            $order_status = 'wc-' . $order->get_status();
            $workflow_statuses = get_option('wc_sa_workflow_order', array());
            if(!array_key_exists($order_status, $workflow_statuses)){
                return;
            }

            $selected_template = get_option("wc_sa_workflow_template", "wt_list");
            if($selected_template == "wt_list"){
                include_once "templates/html-status-workflow.php";
            }else{
                include_once "templates/html-status-workflow-pie.php";
            }
        }

        public function get_public_reviews(){
            $reviews = array();
            $statuses = wc_sa_get_statuses(true);
            foreach ($statuses as $id => $status){
                $_status = new WC_SA_Status($id);
                if($_status->product_reviews == 'yes'){
                    array_push($reviews, $_status->label);
                }
            }
            return $reviews;
        }
    }

endif;

new WC_SA_Frontend();