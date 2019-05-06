<?php
/**
 * Status Data
 *
 * Display the status data meta box.
 *
 * @author      Actuality Extensions
 * @category    Admin
 * @package     WC_SA/Admin/Meta Boxes
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * WC_SA_Meta_Box_Status_Data Class.
 */
class WC_SA_Meta_Box_Status_Data
{

    public static function output_settings($post)
    {
        wp_nonce_field('wc_sa_save_data', 'wc_sa_meta_nonce');
        $status = new WC_SA_Status($post);
        $order_statuses = wc_get_order_statuses();
        $cloudPrint = new GoogleCloudPrintLibrary_GCPL_v2();
        include_once 'views/html-status-settings.php';
    }


    public static function save($post_id, $post)
    {
        self::save_options($post_id, $post);
        wc_delete_shop_order_transients();
    }

    private static function save_options($post_id, $post)
    {
        global $wpdb;

        $sa = new WC_SA_Status(0);
        foreach ($sa->get_defaults() as $meta_key => $default_val) {
            $meta_val = isset($_POST[$meta_key]) ? $_POST[$meta_key] : $default_val;
            if($meta_key == "email_attachments" && !is_array($meta_val)){
                $meta_val = explode(',', $meta_val);
            }
            update_post_meta($post_id, '_' . $meta_key, $meta_val);
        }

        if(array_key_exists('wc-' . $post->post_name, WC_SA()->default_editing) && !isset($_POST['item_editing'])){
            update_post_meta($post_id, '_item_editing', 'no');
        }

        if(array_key_exists($post->post_name, WC_SA()->default_reports) && !isset($_POST['display_in_reports'])){
            update_post_meta($post_id, '_display_in_reports', 'no');
        }

        if(array_key_exists($post->post_name, WC_SA()->default_pay) && !isset($_POST['customer_pay_button'])){
            update_post_meta($post_id, '_customer_pay_button', 'no');
        }

        if(array_key_exists($post->post_name, WC_SA()->default_bulk_action) && !isset($_POST['hide_bulk_actions'])){
            update_post_meta($post_id, '_hide_bulk_actions', 'no');
        }

        if(array_key_exists($post->post_name, WC_SA()->default_widget) && !isset($_POST['dashboard_widget'])){
            update_post_meta($post_id, '_dashboard_widget', 'no');
        }

        if(array_key_exists($post->post_name, WC_SA()->default_customer_cancel) && !isset($_POST['customer_cancel_orders'])){
            update_post_meta($post_id, '_customer_cancel_orders', 'no');
        }

        if(array_key_exists($post->post_name, WC_SA()->default_customer_reviews) && !isset($_POST['product_reviews'])){
            update_post_meta($post_id, '_product_reviews', 'no');
        }

        $label = get_post_meta($post_id, '__label', true);

        if (strlen($post->post_name) > 17) {

            $original_slug = $post->post_name;
            $slug = _truncate_post_slug($original_slug, 17);
            $post_type = 'wc_custom_statuses';

            $check_sql = "SELECT post_name FROM $wpdb->posts WHERE post_name = %s AND post_type = %s AND ID != %d LIMIT 1";
            $post_name_check = $wpdb->get_var($wpdb->prepare($check_sql, $slug, $post_type, $post_id));

            if ($post_name_check) {
                $suffix = 2;
                do {
                    $alt_post_name = _truncate_post_slug($slug, 17 - (strlen($suffix) + 1)) . "-$suffix";
                    $post_name_check = $wpdb->get_var($wpdb->prepare($check_sql, $alt_post_name, $post_type, $post_id));
                    $suffix++;
                } while ($post_name_check);
                $slug = $alt_post_name;
            }

            $post->post_name = $slug;
            $query = "UPDATE {$wpdb->posts} SET post_name = %s WHERE ID = %d";
            $wpdb->query($wpdb->prepare($query, $slug, $post_type, $post_id));

        }

        if (!empty($label) && $post->post_name != $label) {
            $new_label = 'wc-' . $post->post_name;
            $old_label = 'wc-' . $label;
            $query = "UPDATE {$wpdb->posts} SET post_status = '{$new_label}' WHERE post_status = '{$old_label}'";
            $wpdb->query($query);
        }
        update_post_meta($post_id, '__label', $post->post_name);
        update_post_meta($post_id, '_email_notification', isset($_POST['email_notification']) ? $_POST['email_notification'] : "no");
    }

    public static function generate_styles($return = false)
    {
        $statuses = wc_sa_get_statuses(true);
        $new_style = '<!-- Custom Order Status styles -->';
        $new_style .= '<style type="text/css">';
        foreach ($statuses as $key => $value) {
            $sa = new WC_SA_Status($key);
            $class_name = $sa->label == "completed" ? "complete" : $sa->label;
            $icod = $sa->status_icon;
            $scod = $sa->action_icon;
            $display_widget = $sa->dashboard_widget == "yes" ? "inherit" : "none";
            if (version_compare(WC_VERSION, '3.3', '>=')) {
                switch (get_option('woocommerce_status_actions_default_style', '1')) {
                    case '0':
                        $new_style .= '
				header mark.status-' . $sa->label . '::after, .widefat .column-order_status mark.status-' . $sa->label . '::after{
					color: ' . $sa->status_colour . ';
					content: "\\' . $icod . '";
					font-family: \'WC-SA-Icons\' !important;
					font-variant: normal;
				    font-weight: 400;
                    left: 50%;
                    top: 50%;
                    margin: -8px 0 0 -8px;
				    line-height: 17px;
				    font-size: 16px;
				    position: absolute;
				}
				.widefat .column-order_action button.status-' . $sa->label . '::after{
					content: "\\' . $scod . '";
					font-family: \'WC-SA-Icons\' !important;
				}
				#woocommerce_dashboard_status .wc_status_list li.' . $sa->label . '-orders{
				    display: '.$display_widget.';
				}
				#woocommerce_dashboard_status .wc_status_list li.' . $sa->label . '-orders a::before{
				    color: ' . $sa->status_colour . ';
				    content: "\\' . $icod . '";
					font-family: \'WC-SA-Icons\' !important;
				}
				mark.status-' . $sa->label . ', .widefat .column-order_status mark.status-' . $sa->label . '{
				    position: relative;
					background-color:' . $sa->status_colour . '40;
                    border-radius: 4px;
                    width: 33px;
                    text-align: center;
                    display: inline-block;
                    line-height: 2.5em;
                    height: 33px;
                    white-space: nowrap;
				    max-width: 100%;
				    -webkit-font-smoothing: antialiased;
				}
				header mark.status-' . $sa->label . ' span,.widefat .column-order_status mark.status-' . $sa->label . ' span{
					opacity: 0;
					overflow: hidden;
				    text-overflow: ellipsis;
				    margin: 0 1em;
				}
				.widefat td {
					padding: 1em;	
				}
				';
                        break;
                    default:
                        $new_style .= '
				header mark.status-' . $sa->label . ', .widefat .column-order_status mark.status-' . $sa->label . '{
				    color: ' . $sa->status_colour . ';
					background-color:' . $sa->status_colour . '40;
					border-radius: 4px;
					display: inline-block;
				    line-height: 32px;
				    border-radius: 4px;
				    border-bottom: 1px solid rgba(0,0,0,.05);
				    cursor: inherit!important;
				    white-space: nowrap;
				    max-width: 100%;
				}
                .widefat .column-order_action button.status-' . $sa->label . '::after{
					content: "\\' . $scod . '";
					font-family: \'WC-SA-Icons\' !important;
				}
				#woocommerce_dashboard_status .wc_status_list li.' . $sa->label . '-orders{
				    display: '.$display_widget.';
				}
				#woocommerce_dashboard_status .wc_status_list li.' . $sa->label . '-orders a::before{
				    color: ' . $sa->status_colour . ';
				    content: "\\' . $icod . '";
					font-family: \'WC-SA-Icons\' !important;
				}
				a.button.wc-action-button.wc-action-button-'. $class_name . '.' . $class_name .'::after{
				    content: "\\' . $scod . '";
					font-family: \'WC-SA-Icons\' !important;
				}
				';
                        break;
                }
            } else {
                switch ($sa->icon_style) {
                    case 'icon-outline':
                        $new_style .= '
				.widefat .column-order_status mark.status-' . $sa->label . '::after{
					color: ' . $sa->status_colour . ';
					content: "\\' . $icod . '";
					font-family: \'WC-SA-Icons\' !important;
					font-variant: normal;
				    font-weight: 400;
				    height: 100%;
				    left: 0;
				    margin: 0;
				    position: absolute;
				    text-indent: 0;
				    text-transform: none;
				    top: 0;
				    width: 100%;
				    font-size: 47%;
				    line-height: 17px;
				}
				.widefat .column-order_status mark.status-' . $sa->label . '{
					color:' . $sa->status_colour . ';
					border: 1px solid ' . $sa->status_colour . ';
					width: 11px;
    				height: 11px;
    				padding: 3px;
    				border-radius: 100%;
    				text-align: center;
				}				
				';
                        break;
                    case 'text-color':
                        $new_style .= '
				.widefat .column-order_status mark.status-' . $sa->label . '{
					background-color:' . $sa->status_colour . ';
					border: 1px solid ' . $sa->status_colour . ';
					color: #fff;
				    display: block;
				    border-radius: 16px;
				    font-size: 0px;
				    font-weight: normal;
				    line-height: 0px;
				    min-width: 80px;
				    padding: 0;
				    text-align: center;
				    width: auto;
				    height: auto;
				}
				.widefat .column-order_status mark.status-' . $sa->label . ':after{
					content: "' . $sa->label . '";
				    display: block;
				    font-size: 9px;
				    line-height: 17px;
				    text-transform: uppercase;
				    font-weight: bold;
				    text-indent: 1px !important;
				}
				.widefat tr .column-order_status{
					width: 80px;
				}
				';
                        break;
                    case 'text-outline':
                        $new_style .= '
				.widefat .column-order_status mark.status-' . $sa->label . '{
					color:' . $sa->status_colour . ';
					border: 2px solid ' . $sa->status_colour . ';
				    display: block;
				    border-radius: 16px;
				    font-size: 0px;
				    line-height: 0px;
				    min-width: 80px;
				    padding: 0;
				    text-align: center;
				    text-indent: 1px;
				    width: auto;
				    height: auto;
				}
				.widefat .column-order_status mark.status-' . $sa->label . ':after{
					content: "' . $sa->label . '";
				    display: block;
				    font-size: 9px;
				    line-height: 15px;
				    text-indent: 1px !important;
				    font-weight: bold;
				    text-transform: uppercase;
				}
				.widefat tr .column-order_status{
					width: 80px;
				}
				';
                        break;

                    default:
                        $new_style .= '
				.widefat .column-order_status mark.status-' . $sa->label . '::after{
					color: #fff;
					content: "\\' . $icod . '";
					font-family: \'WC-SA-Icons\' !important;
					font-variant: normal;
				    font-weight: 400;
				    height: 100%;
				    left: 0;
				    margin: 0;
				    position: absolute;
				    text-indent: 0;
				    text-transform: none;
				    top: 0;
				    width: 100%;
				    font-size: 47%;
				    line-height: 17px;
				}
				.widefat .column-order_status mark.status-' . $sa->label . '{
					background-color:' . $sa->status_colour . ';
					border: 1px solid ' . $sa->status_colour . ';
					width: 11px;
    				height: 11px;
    				padding: 3px;
    				border-radius: 100%;
    				text-align: center;
				}
				';
                        break;
                }


                switch ($sa->icon_style) {
                    case 'icon-color':
                        $new_style .= '
					#woocommerce_dashboard_status .wc_status_list li.' . $sa->label . '-orders a:before{
						color: #fff;
						content: "\\' . $icod . '";
						font-family: \'WC-SA-Icons\' !important;
						font-size: 12px;
						background-color:' . $sa->status_colour . ';
						border: 1px solid ' . $sa->status_colour . ';
						width: 18px;
	    				height: 18px;
	    				line-height: 18px;
	    				padding: 3px;
	    				border-radius: 100%;
	    				text-align: center;
					}';
                        break;
                    default:
                        $new_style .= '
					#woocommerce_dashboard_status .wc_status_list li.' . $sa->label . '-orders a:before{
						color: ' . $sa->status_colour . ';
						content: "\\' . $scod . '";
						font-family: \'WC-SA-Icons\' !important;
						font-size: 12px;
						color:' . $sa->status_colour . ';
						border: 1px solid ' . $sa->status_colour . ';
						width: 18px;
	    				height: 18px;
	    				line-height: 18px;
	    				padding: 3px;
	    				border-radius: 100%;
	    				text-align: center;
					}';
                        break;
                }
            }
        };
        $new_style .= '</style>';

        if($return){
            return $new_style;
        }

        echo $new_style;
    }
}
