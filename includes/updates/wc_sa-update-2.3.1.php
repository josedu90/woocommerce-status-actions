<?php
global $wpdb;

$custom_statuses = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE `post_type` = 'wc_custom_statuses' AND `post_status` NOT LIKE 'auto-draft'");
foreach ($custom_statuses as $status) {
    $wpdb->query("INSERT INTO {$wpdb->postmeta} (`post_id`,`meta_key`,`meta_value`) VALUES ({$status},'_action_from_users','a:0:{}')");
    $wpdb->query("INSERT INTO {$wpdb->postmeta} (`post_id`,`meta_key`,`meta_value`) VALUES ({$status},'_action_to_users','a:0:{}')");
}

