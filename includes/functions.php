<?php
/**
 * Returns array of statuses.
 *
 * @param bool $with_core
 * @return array $statuses
 */
function wc_sa_get_statuses($with_core = false)
{
    global $wpdb;

    wc_sa_add_default_statuses();

    $result = $wpdb->get_results("SELECT ID, post_name, post_title, pm.meta_value, pm2.meta_value as users_from, pm3.meta_value as users_to FROM {$wpdb->posts}
                                  INNER JOIN
                                  {$wpdb->postmeta} pm
                                    ON ID = pm.post_id
                                    AND pm.meta_key = '_hide_bulk_actions'
                                    INNER JOIN
                                  {$wpdb->postmeta} pm2
                                    ON ID = pm2.post_id
                                    AND pm2.meta_key = '_action_from_users'
                                    INNER JOIN
                                  {$wpdb->postmeta} pm3
                                    ON ID = pm3.post_id
                                    AND pm3.meta_key = '_action_to_users'
                                  WHERE post_type = 'wc_custom_statuses' AND post_status = 'publish' ORDER BY menu_order ASC");
    $statuses = array();
    if ($result) {
        foreach ($result as $key => $value) {

            if($with_core === false && wc_sa_is_core_status($value->post_name)){
                continue;
            }

            $statuses[$value->ID] = (object)array(
                'title' => $value->post_title,
                'label' => $value->post_name,
                'hide' => $value->meta_value,
                'users_from' => unserialize($value->users_from),
                'users_to' => unserialize($value->users_to)
            );
        }
        return $statuses;
    }
    return array();
}

function wc_sa_get_statuses_by_meta($meta_key = '', $meta_value = '', $ids = false)
{
    global $wpdb;
    $query = "SELECT status.ID, status.post_name FROM {$wpdb->posts} as status 
              LEFT JOIN {$wpdb->postmeta} meta ON (meta.post_id = status.ID AND meta.meta_key = '{$meta_key}')
              WHERE status.post_type = 'wc_custom_statuses' AND status.post_status = 'publish' AND meta.meta_value = '{$meta_value}'
              ORDER BY status.menu_order ASC";
    $result = $wpdb->get_results($query);
    $statuses = array();
    if ($result) {
        foreach ($result as $key => $value) {
            if ($ids === true) {
                $statuses[$value->ID] = $value->post_name;
            } else {
                $statuses[] = $value->post_name;
            }
        }
        return $statuses;
    }
    return array();
}

function wc_sa_get_display_in_reports_statuses()
{
    $statuses = wc_sa_get_statuses(true);
    $_status = array();
    foreach ($statuses as $id => $status){
        $status = new WC_SA_Status($id);
        if($status->display_in_reports == "yes"){
            array_push($_status, $status->label);
        }
    }
    return $_status;
}

function wc_sa_get_can_cancel_statuses()
{
    $default_cancel_statuses = array();
    foreach (WC_SA()->default_customer_cancel as $status => $cancel){
        $status = wc_sa_get_status_by_name($status);
        if($status->customer_cancel_orders == "yes"){
            $default_cancel_statuses[] = $status->label;
        }
    }
    return array_merge(wc_sa_get_statuses_by_meta('_customer_cancel_orders', 'yes'), $default_cancel_statuses);
}

function wc_sa_get_pay_button_statuses()
{
    $default_pay_statuses = array();
    foreach (WC_SA()->default_pay as $status => $pay){
        $status = wc_sa_get_status_by_name($status);
        if($status->customer_pay_button == "yes"){
            $default_pay_statuses[] = $status->label;
        }
    }
    return array_merge(wc_sa_get_statuses_by_meta('_customer_pay_button', 'yes'), $default_pay_statuses);
}

/**
 * Returns array of statuses.
 *
 * @return array $statuses
 */
function wc_sa_get_statusesList()
{
    global $wpdb;
    $result = $wpdb->get_results("SELECT ID, post_name, post_title FROM {$wpdb->posts} WHERE post_type = 'wc_custom_statuses' AND post_status = 'publish' ORDER BY menu_order ASC");
    $statuses = array();
    if ($result) {
        foreach ($result as $key => $value) {
            $statuses['wc-' . $value->post_name] = $value->post_title;
        }
        return $statuses;
    }
    return array();
}

/**
 * @return WC_SA_Status
 */
function wc_sa_get_status($the_order_status = false)
{
    if (!did_action('wc_sa_init')) {
        _doing_it_wrong(__FUNCTION__, __('wc_sa_get_status should not be called before the wc_sa_init action.', 'dl_calc'), '1.0.0');
        return false;
    }
    return new WC_SA_Status($the_order_status);
}

/**
 * Returns the status.
 *
 * @param string $name
 * @return bool|WC_SA_Status
 */
function wc_sa_get_status_by_name($name)
{
    global $wpdb;
    $status_id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_name = '{$name}' AND post_type = 'wc_custom_statuses' ");
    if ($status_id) {
        return wc_sa_get_status($status_id);
    }
    return false;
}

function wc_sa_get_acf_editor_btns()
{
    global $wpdb;
    $acf_fields = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'acf' ");
    $btn = '';
    if ($acf_fields) {
        foreach ($acf_fields as $field) {
            $id = $field->ID;
            $rule = get_post_meta($id, 'rule', true);
            if ($rule && $rule['param'] == 'post_type') {
                $is_shop = false;
                switch ($rule['operator']) {
                    case '==':
                        if (($rule['value'] == 'shop_order' || $rule['value'] == 'all')) {
                            $is_shop = true;
                        }
                        break;
                    case '!=':
                        if ($rule['value'] != 'shop_order') {
                            $is_shop = true;
                        }
                        break;
                }
                if ($is_shop) {
                    if ($btn == '')
                        $btn = array();

                    $post_meta = get_post_meta($id);

                    foreach ($post_meta as $key => $value) {
                        if (strrpos($key, 'field_') === 0) {
                            $meta = maybe_unserialize($value[0]);
                            $btn[$meta['name']] = array('label' => $meta['label']);

                        }
                    }
                }

            }
        }
    }
    return $btn;
}

function wc_sa_get_allowed_screens()
{
    $def = array(
        'dashboard',
        'edit-wc_custom_statuses',
        'wc_custom_statuses',
        'edit-shop_order',
        'shop_order',
        'toplevel_page_wc_crm',
        'woocommerce_page_wc_bulk_change_status'
    );
    $allowed_screens = apply_filters('wc_sa_allowed_screens', array());
    if (!is_array($allowed_screens)) {
        $allowed_screens = array();
    }
    return array_merge($def, $allowed_screens);
}

function wc_sa_get_default_order_statuses()
{
    $default_order_statuses = WC_SA()->default_statuses;
    return $default_order_statuses;
}

function wc_sa_get_hide_statuse_for_bulk()
{
    global $wpdb;
    $status_id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_name = '{$name}' AND post_type = 'wc_custom_statuses' ");
    if ($status_id) {
        return wc_sa_get_status($status_id);
    }
    return false;
}


function wc_sa_reorder_statuses($the_post, $next_id, $posttype, $index = 0, $posts = null)
{
    $args = array(
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'post_type' => $posttype,
        'post_status' => 'publish'
    );
    if (!$posts) $posts = get_posts($args);
    if (empty($posts)) return $index;

    $id = $the_post->ID;

    $post_in_level = false; // flag: is our term to order in this level of posts

    foreach ($posts as $post) {

        if ($post->ID == $id) { // our term to order, we skip
            $post_in_level = true;
            continue; // our term to order, we skip
        }
        // the nextid of our term to order, lets move our term here
        if (null !== $next_id && $post->ID == $next_id) {
            $index++;
            $index = wc_sa_set_post_order($id, $index);
        }

        // set order
        $index++;
        $index = wc_sa_set_post_order($post->ID, $index);

    }

    // no nextid meaning our term is in last position
    if ($post_in_level && null === $next_id) {
        $index = wc_sa_set_post_order($id, $index + 1);
    }

    return $index;
}

function wc_sa_set_post_order($post_id, $index)
{

    global $wpdb;
    $post_id = (int)$post_id;
    $index = (int)$index;

    $query = "UPDATE {$wpdb->posts} SET menu_order = {$index} WHERE ID = {$post_id}";
    $wpdb->query($query);

    return $index;
}

function wc_sa_get_count_orders_by_status_slug($status_slug)
{
    global $wpdb;
    $orders = $wpdb->get_var("SELECT count(ID) FROM {$wpdb->posts} WHERE post_status = 'wc-{$status_slug}' AND post_type = 'shop_order' ");
    return $orders;
}

function wc_sa_add_default_statuses(){
    global $wpdb;
    $results = $wpdb->get_col("SELECT CONCAT('wc-', post_name) FROM {$wpdb->posts} WHERE CONCAT('wc-', post_name) in ('" . implode("','", array_keys(WC_SA()->default_statuses)) . "') AND post_type = 'wc_custom_statuses'");
    $diff_statuses = array_diff(array_keys(WC_SA()->default_statuses), $results);

    if(!count($diff_statuses)){
        return;
    }

    foreach ($diff_statuses as $diff_status){
        $post_name = substr($diff_status, 3);
        wp_insert_post(
            array(
                'post_title' => WC_SA()->default_statuses[$diff_status],
                'post_name' => $post_name,
                'post_type' => 'wc_custom_statuses',
                'post_status'   => 'publish',
                'meta_input' => array(
                    '_hide_bulk_actions' => array_key_exists($post_name, WC_SA()->default_bulk_action) ? "yes" : "no",
                    '_action_from_users' => array(),
                    '_action_to_users' => array()
                )
            )
        );
    }
}

function wc_sa_is_core_status($status){
    return array_key_exists("wc-" . $status, WC_SA()->default_statuses);
}

/**
 * @param $status
 * @return WC_Email|stdClass
 */
function wc_sa_get_status_email_data($status){

    $data = new stdClass();
    $data->enabled = false;
    $data->recipient = "customer";
    $data->subject = "";
    $data->heading = "";
    $data->email_type = "html";

    $path = WC()->plugin_path() . "/includes/";

    include_once $path . 'emails/class-wc-email.php';
    switch ($status){
        case "processing":
            $data = include $path . 'emails/class-wc-email-customer-processing-order.php';
            break;
        case "on-hold":
            $data = include $path . 'emails/class-wc-email-customer-on-hold-order.php';
            $data->item_editing = "yes";
            break;
        case "completed":
            $data = include $path . "emails/class-wc-email-customer-completed-order.php";
            break;
        case "cancelled":
            $data = include $path . "emails/class-wc-email-cancelled-order.php";
            break;
        case "refunded":
            $data = include $path . "emails/class-wc-email-customer-refunded-order.php";
            break;
        case "failed":
            $data = include $path . 'emails/class-wc-email-failed-order.php';
            break;
    }

    return $data;
}
