<?php
$ex_statuses = get_option('wc_custom_status_edit_existing_status', array());
wc_sa_add_default_statuses();
if(!empty($ex_statuses)){
    foreach($ex_statuses as $key => $ex_status){
        $slug = substr($key, 3);
        $status = wc_sa_get_status_by_name($slug);
        if(is_a($status, 'WC_SA_Status')){
            $color = WC_SA()->color_statuses['wc-' . $status->label];
            $icon = isset($ex_status['icon']) ? $ex_status['icon'] : $status->status_icon;
            $name = isset($ex_status['name']) ? $ex_status['name'] : $status->title;
            $bulk_action = isset($ex_status['hide_bulk']) ? $ex_status['hide_bulk'] : $status->hide_bulk_actions;
            $item_editing = isset($ex_status['item_editing']) ? $ex_status['item_editing'] : $status->item_editing;

            wp_update_post(
                array(
                    'ID' => $status->id,
                    'post_title' => $name,
                    'meta_input' => array(
                        '_status_colour' => $color,
                        '_status_icon' => $icon,
                        '_hide_bulk_actions' => $bulk_action,
                        '_item_editing' => $item_editing
                    )
                )
            );
        }
    }
}