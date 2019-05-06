<?php
global $wpdb;

$custom_statuses = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE `post_type` = 'wc_custom_statuses' AND `post_status` NOT LIKE 'auto-draft'");
if(count($custom_statuses)){
    $custom_statuses = implode(',', $custom_statuses);
    //Set default status style and icon
    $wpdb->query("UPDATE {$wpdb->postmeta} SET `meta_value` = 'text-color' WHERE `post_id` IN ($custom_statuses) AND `meta_key` = '_icon_style'");
    $wpdb->query("UPDATE {$wpdb->postmeta} SET `meta_value` = 'eb49' WHERE `post_id` IN ($custom_statuses) AND `meta_key` = '_status_icon'");
    $wpdb->query("UPDATE {$wpdb->postmeta} SET `meta_value` = 'eb49' WHERE `post_id` IN ($custom_statuses) AND `meta_key` = '_action_icon'");
}
$core_statuses_array = array(
    'wc-pending' => array (
        'icon' => 'ec68',
        'name' => 'Pending Payment',
        'color' => '#828282',
    ),
    'wc-processing' => array (
        'icon' => 'ecd8',
        'name' => 'Processing',
        'color' => '#559f55',
    ),
    'wc-on-hold' => array (
        'icon' => 'ed04',
        'name' => 'On Hold',
        'color' => '#eda411',
    ),
    'wc-completed' => array (
        'icon' => 'ebcb',
        'name' => 'Completed',
        'color' => '#386b98',
    ),
    'wc-cancelled' => array (
        'icon' => 'ebc7',
        'name' => 'Cancelled',
        'color' => '#828282',
    ),
    'wc-refunded' => array (
        'icon' => 'ed52',
        'name' => 'Refunded',
        'color' => '#828282',
    ),
    'wc-failed' => array (
        'icon' => 'eb80',
        'name' => 'Failed',
        'color' => '#c62828',
    ),
);
update_option('wc_custom_status_edit_existing_status',$core_statuses_array);
update_option('woocommerce_status_actions_default_style', 1);