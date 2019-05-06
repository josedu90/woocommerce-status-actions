<?php
global $wpdb;

$custom_statuses = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE `post_type` = 'wc_custom_statuses' AND `post_status` NOT LIKE 'auto-draft'");
if(!count($custom_statuses)){
    return;
}
$custom_statuses = implode(',',$custom_statuses);
//Set default status style and icon
$wpdb->query("UPDATE {$wpdb->postmeta} SET `meta_value` = 'text-color' WHERE `post_id` IN ($custom_statuses) AND `meta_key` = '_icon_style'");
$wpdb->query("UPDATE {$wpdb->postmeta} SET `meta_value` = 'e301' WHERE `post_id` IN ($custom_statuses) AND `meta_key` = '_status_icon'");
$wpdb->query("UPDATE {$wpdb->postmeta} SET `meta_value` = 'e301' WHERE `post_id` IN ($custom_statuses) AND `meta_key` = '_action_icon'");
