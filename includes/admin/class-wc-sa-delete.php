<?php
/**
 *
 * @category Admin
 * @package  WC_SA/Classes
 * @version  1.0.0
 * @since    2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC_SA_Delete Class
 */
class WC_SA_Delete
{


    /**
     * Hook in tabs.
     */
    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'add_admin_pages'));
        add_action('wp_loaded', array(__CLASS__, 'status_actions_delete'), 20);
    }

    public static function add_admin_pages($value = '')
    {
        if (isset($_GET['page']) && $_GET['page'] == 'wc_sa_delete_status') {
            add_submenu_page('', __('Delete Status', 'woocommerce_status_actions'), __('Delete Status', 'woocommerce_status_actions'), 'manage_woocommerce', 'wc_sa_delete_status', array(__CLASS__, 'output_delete_status'));
        }
    }

    /**
     * Renders the delete page on bulk action and per single delete
     * @return [type] [description]
     */
    public static function output_delete_status()
    {
        if (empty($_GET['status_id'])) {
            ?>
            <div id='message' class='error below-h2'>
                <p>
                    Something is wrong. Please go back and try again.
                </p>
            </div>
            <?php
            return;
        }

        $status_ids = $_GET['status_id'];
        $select_statuses = array();
        $order_statuses = wc_get_order_statuses();
        $custom_statuses = wc_sa_get_statuses();
        $undeletable = array();

        foreach ($status_ids as $status_id) {
            if (array_key_exists($status_id, $custom_statuses)) {
                $status_slug = 'wc-' . $custom_statuses[$status_id]->label;
                $select_statuses[$status_id] = $custom_statuses[$status_id]->title;
                unset($order_statuses[$status_slug]);
            }else{
                array_push($undeletable, 'wc-' . get_post($status_id)->post_name);
            }
        }

        $order_select = '';
        foreach ($order_statuses as $o => $o_name) {
            $order_select .= '<option value="' . $o . '">' . $o_name . '</option>';
        }

        // Build the selectbox and the confirmation button

        ?>
        <div class="wrap">
            <h2><?php _e('Delete Order Status', 'woocommerce_status_actions'); ?></h2>
            <form method="POST">
                <?php
                foreach ($select_statuses as $status_id => $status_name) {
                    ?>
                    <p><?php _e('You must reassign all orders marked as ', 'woocommerce_status_actions'); ?>
                        <strong><?php echo $status_name; ?></strong><?php _e(' to another before deleting it.', 'woocommerce_status_actions'); ?>
                    </p>
                    <div id="wrapper_for_<?php echo $status_id; ?>">
                        <fieldset>
                            <ul style="list-style:none;">
                                <li>
                                    <div id="select_for_<?php echo $status_id; ?>">
                                        <label for="delete_option1"><?php _e('Reassign all orders marked as  ', 'woocommerce_status_actions'); ?>
                                            <strong><?php echo $status_name; ?></strong><?php _e(' to:', 'woocommerce_status_actions'); ?>
                                        </label>
                                        <select name="wc_sa_delete_statuses[<?php echo $status_id; ?>]"
                                                class="linen_order_statuses"
                                                data-status_id="<?php echo $status_id; ?>">
                                            <?php echo $order_select; ?>
                                        </select>
                                    </div>
                                </li>
                            </ul>
                        </fieldset>
                    </div>
                    <?php
                }
                wp_nonce_field('delete_custom_status', 'nonce');
                ?>
                <?php if(count($undeletable)): ?>
                <p class="error-message">
                    <strong><?php _e("Warning:", "woocommerce_status_actions") ?></strong>
                    <?php _e("Cannot delete core statuses - ", "woocommerce_status_actions") ?>
                    <?php echo implode(",", $undeletable); ?>
                </p>
                <?php endif; ?>
                <a href="<?php echo home_url() . '/wp-admin/admin.php?page=wc-settings&tab=wc_sa_settings' ?>"
                   class="button"><?php _e('Go back to order statuses', 'woocommerce_status_actions') ?></a>
                <?php if(count($select_statuses)): ?>
                <span class="submit">
                    <button type="submit" class="button">
                        <?php _e('Reassign Orders & Delete Order Status', 'woocommerce_status_actions'); ?>
                    </button>
                </span>
                <?php endif; ?>
            </form>
        </div>
        <?php
    }

    public static function status_actions_delete()
    {
        global $wpdb;
        // If our required post parameters are set
        $i = 0;
        if (isset($_POST['wc_sa_delete_statuses']) && is_array($_POST['wc_sa_delete_statuses']) && !empty($_POST['wc_sa_delete_statuses'])) {
            // If not an administrator disallow it
            if (!is_admin()) return;
            // If user can not edit shop orders disallow it
            if (!current_user_can('edit_shop_orders')) return;
            // If nonce is not set or wrong, disallow it
            if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'delete_custom_status')) return;

            $custom_statuses = wc_sa_get_statuses();
            foreach ($_POST['wc_sa_delete_statuses'] as $status_id => $new_label) {
                $delete_label = 'wc-' . $custom_statuses[$status_id]->label;
                $query = "UPDATE {$wpdb->posts} SET post_status = '{$new_label}' WHERE post_status = '{$delete_label}'";
                $wpdb->query($query);
                wp_delete_post($status_id, true);
                $i++;
            }

            $sendback = admin_url("admin.php?page=wc-settings&tab=wc_sa_settings");
            if ($i > 0) {
                $sendback = add_query_arg(array('deleted' => $i), $sendback);
            }
            wp_redirect($sendback);
        }
    }
}

WC_SA_Delete::init();