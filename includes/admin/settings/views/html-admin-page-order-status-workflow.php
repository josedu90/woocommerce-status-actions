<?php
/**
 * Shipping zone admin
 *
 * @package WooCommerce/Admin/Shipping
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<?php add_thickbox(); ?>

<h2><?php __("Order status workflow", "woocommerce_status_actions"); ?></h2>
<table class="form-table wc-sa-status-overflow-settings">
    <tbody>
    <tr valign="top" class="">
        <th scope="row" class="titledesc">
            <label for="workflow_name">
                <?php esc_html_e( 'Name', 'woocommerce_status_actions' ); ?>
                <?php echo wc_help_tip( __( 'This is the name of the status workflow.', 'woocommerce_status_actions' ) ); ?>
            </label>
        </th>
        <td class="forminp">
            <input type="text" data-attribute="wc_sa_workflow_name" name="wc_sa_workflow_name" id="wc_sa_workflow_name" value="<?php echo get_option('wc_sa_workflow_name') ?>">
        </td>
    </tr>
    <tr valign="top" class="">
        <th scope="row" class="titledesc">
            <label for="wc_sa_workflow_template">
                <?php esc_html_e( 'Design', 'woocommerce_status_actions' ); ?>
                <?php echo wc_help_tip( __( 'This is the template of the status workflow.', 'woocommerce_status_actions' ) ); ?>
            </label>
        </th>
        <td class="forminp">
            <select name="wc_sa_workflow_template" id="wc_sa_workflow_template">
                <?php $selected_template = get_option("wc_sa_workflow_template", "wt_list"); ?>
                <option value="wt_list" <?php selected($selected_template, "wt_list", true); ?>><?php _e("List", "woocommerce_status_actions"); ?></option>
                <option value="wt_round" <?php selected($selected_template, "wt_round", true); ?>><?php _e("Rounded", "woocommerce_status_actions"); ?></option>
            </select>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row" class="titledesc">
            <label for="wc_sa_workflow_active_color">
                <?php esc_html_e( 'Colour', 'woocommerce_status_actions' ); ?>
                <?php echo wc_help_tip( __( 'This is the background  colour of active statuses.', 'woocommerce_status_actions' ) ); ?>
            </label>
        </th>
        <td class="forminp forminp-color">&lrm;
            <?php $active_background = esc_attr( get_option('wc_sa_workflow_active_color', '#96588a') ); ?>
            <span class="colorpickpreview" style="background: <?php echo $active_background; ?>">&nbsp;</span>
            <input
                    name="wc_sa_workflow_active_color"
                    id="wc_sa_workflow_active_color"
                    type="text"
                    dir="ltr"
                    style="width: 6em;"
                    value="<?php echo $active_background; ?>"
                    class="colorpick"
                    placeholder=""
            />&lrm;
            <div id="colorPickerDiv_wc_sa_workflow_active_color" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row" class="titledesc">
            <label for="wc_sa_workflow_active_color">
                <?php esc_html_e( 'Inactive Colour', 'woocommerce_status_actions' ); ?>
                <?php echo wc_help_tip( __( 'This is the background  colour of non-active statuses.', 'woocommerce_status_actions' ) ); ?>
            </label>
        </th>
        <td class="forminp forminp-color">&lrm;
            <?php $not_active_background = esc_attr( get_option('wc_sa_workflow_not_active_color', '#dddddd') ); ?>
            <span class="colorpickpreview" style="background: <?php echo $not_active_background; ?>">&nbsp;</span>
            <input
                    name="wc_sa_workflow_not_active_color"
                    id="wc_sa_workflow_not_active_color"
                    type="text"
                    dir="ltr"
                    style="width: 6em;"
                    value="<?php echo $not_active_background; ?>"
                    class="colorpick"
                    placeholder=""
            />&lrm;
            <div id="colorPickerDiv_wc_sa_workflow_not_active_color" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div>
        </td>
    </tr>
    <tr valign="top" class="">
        <th scope="row" class="titledesc">
            <label>
                <?php esc_html_e( 'Workflow', 'woocommerce_status_actions' ); ?>
                <?php echo wc_help_tip( __( 'The following statuses are applied for the status workflow.', 'woocommerce_status_actions' ) ); ?>
            </label>
        </th>
        <td class="">
            <table class="wc-sa-statuses-workflow wp-list-table widefat fixed striped posts">
                <thead>
                <tr>
                    <th class="wc-shipping-zone-method-sort" width="10px"></th>
                    <th class="wc-shipping-zone-method-title"><?php esc_html_e( 'Status', 'woocommerce_status_actions' ); ?></th>
                    <th class="wc-shipping-zone-method-description"><?php esc_html_e( 'Description', 'woocommerce_status_actions' ); ?></th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <td colspan="4">
                        <a href="#TB_inline?&width=300&height=150&inlineId=wc_sa_order_statuses_container" class="thickbox button wc-sa-status-workflow-add"><?php esc_html_e( 'Add status', 'woocommerce' ); ?></a>
                    </td>
                </tr>
                </tfoot>
                <tbody class="ui-sortable" id="wc_sa_status_workflow_tbody">
                <?php
                $order_statuses = get_option('wc_sa_workflow_order', array());
                $order_statuses = is_array($order_statuses) ? $order_statuses : array();
                if(count($order_statuses)){
                    foreach ($order_statuses as $key => $order_status){ ?>
                        <tr data-status-key="<?php echo $key ?>">
                            <td class="sort column-sort ui-sortable-handle">
                                <input type="hidden" name="wc_sa_workflow_order[<?php echo $key; ?>]" class="wc_sa_workflow_order_item" value="<?php echo $order_status; ?>">
                            </td>
                            <td><?php echo $order_status; ?></td>
                            <td></td>
                        </tr>
                    <?php }
                }
                ?>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>

<div id="wc_sa_order_statuses_container" style="display: none;">
    <div class="wc_sa_order_statuses_container" style="margin: 20px 0;">
        <?php $statuses = array_merge(wc_sa_get_statusesList(), wc_sa_get_default_order_statuses()); ?>
        <label for="wc_sa_order_statuses"><?php _e("Choose statuses from below:", "woocommerce_status_actions") ?></label>
        <select name="wc_sa_order_statuses" id="wc_sa_order_statuses" style="width:100%" multiple>
            <?php foreach ($statuses as $key => $status): ?>
                <option value="<?php echo $key ?>" <?php echo array_key_exists($key, $order_statuses) ? "selected" : ""; ?>><?php echo $status; ?></option>
            <?php endforeach; ?>
        </select>
        <button class="workflow_status_add button" style="margin-top: 15px;"><?php _e("Add Statuses", "woocommerce_status_actions"); ?></button>
    </div>
</div>
