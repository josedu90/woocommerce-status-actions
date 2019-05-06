<?php
/**
 * Status Settings
 *
 * @author   Actuality Extensions
 * @category Admin
 * @package  WC_SA/Admin
 * @version  1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_SA_Settings')) :

    /**
     * WC_SA_Settings.
     */
    class WC_SA_Settings extends WC_Settings_Page
    {

        /**
         * Constructor.
         */
        public function __construct()
        {

            $this->id = 'wc_sa_settings';
            $this->label = __('Order Statuses', 'woocommerce_status_actions');

            add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_page'), 20);
            add_action('woocommerce_settings_' . $this->id, array($this, 'output'));
            add_action('woocommerce_settings_save_' . $this->id, array($this, 'save'));
            add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));
        }

        /**
         * Get sections.
         *
         * @return array
         */
        public function get_sections()
        {

            $sections = array(
                '' => __('Order Statuses', 'woocommerce_status_actions'),
                'general' => __('General Options', 'woocommerce_status_actions'),
                'print' => __('Printing Options', 'woocommerce_status_actions'),
                'automation' => __('Automation', 'woocommerce_status_actions'),
                'gateways' => __('Gateways', 'woocommerce_status_actions'),
                'template' => __('Template', 'woocommerce_status_actions'),
                'workflow' => __('Workflow', 'woocommerce_status_actions'),
                'license'  => __('License', 'woocommerce_status_actions')
            );

            return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
        }

        /**
         * Output the settings.
         */
        public function output()
        {
            global $current_section;

            add_action('woocommerce_admin_field_edit_existing_status', array($this, 'edit_existing_status'));
            add_action('woocommerce_admin_field_edit_gateway_order_status', array($this, 'edit_gateway_order_status'));
            add_action('woocommerce_admin_field_edit_wc_sa_trigger_days', array($this, 'edit_wc_sa_trigger_days'));
            add_action('woocommerce_admin_field_edit_custom_statuses', array($this, 'edit_custom_statuses'));
            add_action('woocommerce_admin_field_wc_sa_google_login', array($this, 'wc_sa_google_login'));
            add_action('woocommerce_admin_field_wc_sa_template_icon', array($this, 'template_icon_picker'));
            add_action('woocommerce_admin_field_order_status_workflow', array($this, 'order_status_workflow'));
            add_action('woocommerce_admin_field_status_action_license', array($this, 'status_action_license'));

            $settings = $this->get_settings($current_section);

            WC_Admin_Settings::output_fields($settings);
        }

        /**
         * Save settings.
         */
        public function save()
        {
            global $current_section;

            $settings = $this->get_settings($current_section);

            unset($settings['edit_existing_status']);
            unset($settings['edit_gateway_order_status']);
            unset($settings['trigger_options']);

            if (isset($_POST['wc_custom_status_edit_existing_status'])) {
                $e_st = is_array($_POST['wc_custom_status_edit_existing_status']) ? $_POST['wc_custom_status_edit_existing_status'] : array();
                update_option('wc_custom_status_edit_existing_status', $e_st);
            }
            if (isset($_POST['wc_sa_payment_gateway_status'])) {
                update_option('wc_sa_payment_gateway_statuses', $_POST['wc_sa_payment_gateway_status']);
            }
            if (isset($_POST['trigger_options'])) {
                update_option('trigger_options', $_POST['trigger_options']);
            }

            if($current_section == 'workflow'){
                update_option('wc_sa_workflow_order', $_POST["wc_sa_workflow_order"]);
                update_option('wc_sa_workflow_name', $_POST['wc_sa_workflow_name']);
                update_option('wc_sa_workflow_template', $_POST['wc_sa_workflow_template']);
                update_option('wc_sa_workflow_active_color', $_POST['wc_sa_workflow_active_color']);
                update_option('wc_sa_workflow_not_active_color', $_POST['wc_sa_workflow_not_active_color']);
            }
            WC_Admin_Settings::save_fields($settings);
        }

        /**
         * Get settings array.
         *
         * @return array
         */
        public function get_settings($current_section = '')
        {
            if ('gateways' == $current_section) {

                $settings = apply_filters('woocommerce_product_settings', array(

                    array(
                        'title' => __('Gateway Options', 'woocommerce_status_actions'),
                        'type' => 'title',
                        'desc' => __('You can define which status is assigned to the order depending on the gateway used to complete payment.', 'woocommerce_status_actions'),
                        'id' => 'gateway_order_status_options'
                    ),
                    'edit_gateway_order_status' => array(
                        'id' => 'wc_sa_payment_gateway_statuses',
                        'type' => 'edit_gateway_order_status',
                    ),

                ));

            } elseif ('print' == $current_section) {
                $settings = apply_filters('wc_sa_general_settings', array(
                    array(
                        'title' => __('Printing Options', 'woocommerce_status_actions'),
                        'desc' => sprintf( __( 'Google Cloud Print is a Google service that lets users print from any Cloud Print application on any device in the network cloud to any printer. Learn more about this <a href="%1$s" target="_blank">here</a>, and get your printer configured.', 'woocommerce_status_actions' ), 'https://support.actualityextensions.com/order-status-actions-manager/automatic-printing/' ),
                        'type' => 'title',
                        'id' => 'status_print_settings'
                    ),
                    array(
                        'title' => __('Enabled / Disabled', 'woocommerce_status_actions'),
                        'desc' => __('Enable Google Cloud Print', 'woocommerce_status_actions'),
                        'type' => 'checkbox',
                        'id' => 'wc_sa_google_cloud_enable'
                    ),
                    array(
                        'title' => __('Client ID', 'woocommerce_status_actions'),
                        'id' => 'wc_sa_google_cloud_print_library_options[clientid]',
                        'desc_tip' => __('Client ID', 'woocommerce'),
                        'type' => 'text',
                    ),
                    array(
                        'title' => __('Client Secret', 'woocommerce_status_actions'),
                        'id' => 'wc_sa_google_cloud_print_library_options[clientsecret]',
                        'desc_tip' => __('Client Secret', 'woocommerce'),
                        'type' => 'password',
                        'css' => 'min-width: 400px; padding: 6px;',
                    ),
                    'wc_sa_google_login' => array(
                        'id' => 'wc_sa_google_login',
                        'type' => 'wc_sa_google_login',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'edit_default_status_options'
                    ),
                ));
            } elseif ('general' == $current_section) {
                $status_list = wc_get_order_statuses();
                $settings = apply_filters('wc_sa_general_settings', array(
                    array(
                        'title' => __('Status Settings', 'woocommerce_status_actions'),
                        'desc' => __('Configure general settings for your order statuses using the options below.', 'woocommerce_status_actions'),
                        'type' => 'title',
                        'id' => 'custom_status_general_settings'
                    ),

                    array(
                        'title' => __('Status Style', 'woocommerce_status_actions'),
                        'id' => 'woocommerce_status_actions_default_style',
                        'default' => '1',
                        'desc_tip' => __('This will effect the default WooCommerce\'s statuses (e.g. Processing, Completed, etc.).', 'woocommerce'),
                        'type' => 'select',
                        'class' => 'chosen_select',
                        'css' => 'min-width: 350px;',
                        'options' => array(
                            '0' => __('Icon', 'woocommerce_status_actions'),
                            '1' => __('Text', 'woocommerce_status_actions'),
                        )
                    ),

                    array(
                        'title' => __('Successful Payment', 'woocommerce_status_actions'),
                        'id' => 'wc_custom_status_payment_complete_status',
                        'default' => 'wc-processing',
                        'desc_tip' => __('Choose what default status to have upon successful payment. Default: Processing.', 'woocommerce'),
                        'type' => 'select',
                        'class' => 'chosen_select',
                        'css' => 'min-width: 350px;',
                        'options' => $status_list
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'edit_default_status_options'
                    ),
                ));
            } elseif ('automation' == $current_section) {
                $status_list = wc_get_order_statuses();
                $settings = apply_filters('wc_sa_general_settings', array(
                    array(
                        'title' => __('Automation Options', 'woocommerce_status_actions'),
                        'desc' => __('Configure and setup the days and dates you do not want automated order assignments to run on your orders', 'woocommerce_status_actions'),
                        'type' => 'title',
                        'id' => 'custom_status_general_settings'
                    ),
                    array(
                        'title' => __('Disable Days', 'woocommerce_status_actions'),
                        'desc' => __('Select the days of the week where you do not want automated order assignments to run on your orders.', 'woocommerce_status_actions'),
                        'type' => 'title',
                        'id' => 'custom_status_trigger_options'
                    ),
                    'wc_sa_trigger_days' => array(
                        'id' => 'wc_sa_trigger_days',
                        'type' => 'edit_wc_sa_trigger_days',
                    ),

                    array(
                        'type' => 'sectionend',
                        'id' => 'edit_default_status_options'
                    ),
                ));
            } elseif ('template' == $current_section) {
                $settings = apply_filters('wc_sa_general_settings', array(
                    array(
                        'title' => __('Template', 'woocommerce_status_actions'),
                        'desc' => __('Configure the template that gets printed using Google Cloud Print.', 'woocommerce_status_actions'),
                        'type' => 'title',
                        'id' => 'custom_statuses'
                    ),
                    array(
                        'title' => __('Header', 'woocommerce_status_actions'),
                        'desc_tip' => __('This will be displayed just after the billing and shipping address.', 'woocommerce_status_actions'),
                        'type' => 'textarea',
                        'id' => 'wc_sa_header'
                    ),
                    array(
                        'title' => __('Returns & Refunds Policy', 'woocommerce_status_actions'),
                        'desc_tip' => __('Enter text that will be displayed at the bottom, before the footer.', 'woocommerce_status_actions'),
                        'type' => 'textarea',
                        'id' => 'wc_sa_returns_policy'
                    ),
                    array(
                        'title' => __('Footer', 'woocommerce_status_actions'),
                        'desc_tip' => __('This will be displayed just after the returns policy at the very bottom of the invoice.', 'woocommerce_status_actions'),
                        'type' => 'textarea',
                        'id' => 'wc_sa_footer'
                    ),
                    array(
                        'title' => __('Store Tax Number', 'woocommerce_status_actions'),
                        'desc_tip' => __('Enter the stores government tax number.', 'woocommerce_status_actions'),
                        'type' => 'text',
                        'id' => 'wc_sa_tax_number'
                    ),
                    'wc_sa_template_icon' => array(
                        'title' => __('Icon', 'woocommerce_status_actions'),
                        'desc_tip' => __('Enter the stores government tax number.', 'woocommerce_status_actions'),
                        'id' => 'wc_sa_template_icon',
                        'type' => 'wc_sa_template_icon',
                    ),
                    array(
                        'type' => 'text',
                        'id' => 'wc_sa_template_image',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'custom_statuses_end'
                    ),
                ));
            } elseif ('workflow' == $current_section){
                $settings = array(
                    'order_status_workflow' => array(
                        'type' => 'order_status_workflow',
                        'id' => 'order_status_workflow'
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'order_status_workflow'
                    ),
                );
            } elseif ('license' == $current_section){
                $GLOBALS['hide_save_button'] = 'yes';
                $settings = array(
                    'status_action_license' => array(
                        'type' => 'status_action_license',
                        'id' => 'status_action_license'
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'status_action_license'
                    ),
                );
            } else{
                $settings = apply_filters('wc_sa_general_settings', array(
                    array(
                        'title' => __('Order Statuses', 'woocommerce_status_actions'),
                        'desc' => __('Order statuses are helpful in defining the workflow of your store. Click on Add Order Status to create your own order status and configure the parameters to suit your workflow.', 'woocommerce_status_actions'),
                        'type' => 'title',
                        'id' => 'custom_statuses'
                    ),
                    'add_new_status' => array(
                        'id' => 'wc_custom_status_add_new_status',
                        'type' => 'add_new_status',
                    ),
                    'edit_custom_statuses' => array(
                        'id' => 'wc_custom_status_edit_custom_statuses',
                        'type' => 'edit_custom_statuses',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'custom_statuses_end'
                    ),
                ));
            }

            return apply_filters('woocommerce_get_settings_' . $this->id, $settings, $current_section);
        }

        public function edit_wc_sa_trigger_days()
        {
            $trigger_options = get_option('trigger_options');
            $trigger_options['days_off'] = json_encode(explode(',', $trigger_options['days_off']));
            $days = array(
                1 => __('Monday', 'woocommerce_status_actions'),
                2 => __('Tuesday', 'woocommerce_status_actions'),
                3 => __('Wednesday', 'woocommerce_status_actions'),
                4 => __('Thursday', 'woocommerce_status_actions'),
                5 => __('Friday', 'woocommerce_status_actions'),
                6 => __('Saturday', 'woocommerce_status_actions'),
                7 => __('Sunday', 'woocommerce_status_actions'),
            );
            ?>
            <div id="weekdays">
                <input type="hidden" id="trigger_options_days_off" name="trigger_options[days_off]"
                       value='<?php echo $trigger_options['days_off'] ?>'>
                <?php foreach ($days as $key => $day) { ?>
                    <div class="automation_days row">
                        <label for="<?php echo $day ?>"><?php echo $day ?></label>
                        <input type="checkbox" class="automation_day_select" id="<?php echo $day ?>"
                               name="trigger_options[weekday][<?php echo $key ?>]"
                            <?php echo (isset($trigger_options['weekday'][$key])) ? 'checked' : '' ?>>
                    </div>
                <?php } ?>
            </div>
            <h2 for="days_off"><?php _e('Disable Specific Dates', 'woocommerce_status_actions') ?></h2>
            <p><?php _e('Select the specific dates where you do not want automated order assignments to run on your orders.', 'woocommerce_status_actions') ?></p>
            <div id="days_off">
            </div>
            <?php
        }

        public function edit_gateway_order_status($val)
        {
            $saved_st = get_option($val['id']);
            $wc_order_statuses = wc_sa_get_default_order_statuses();
            $all_order_statuses = wc_sa_get_statusesList();
            //$all_order_statuses = array_diff_key($all_order_statuses, $wc_order_statuses);

            if (!is_array($saved_st)) {
                $saved_st = array();
            }
            ?>
            <table class="wp-list-table widefat striped posts default_status_payments_methods">
                <thead>
                <tr>
                    <th><?php _e('Gateway', 'woocommerce_status_actions'); ?></th>
                    <th><?php _e('Order Status', 'woocommerce_status_actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach (WC()->payment_gateways->payment_gateways() as $gateway) {
                    $st = isset($saved_st[$gateway->id]) ? $saved_st[$gateway->id] : '';
                    ?>
                    <tr>
                        <td class="payment-gateway">
                            <?php echo $gateway->get_title(); ?>
                        </td>
                        <td>
                            <select name="wc_sa_payment_gateway_status[<?php echo $gateway->id; ?>]"
                                    class="payment_gateways_st">
                                <option value="" <?php selected('', $st, true); ?> ><?php _e('Default status', 'woocommerce_status_actions'); ?></option>
                                <optgroup label="<?php _e('WooCommerce statuses', 'woocommerce_status_actions'); ?>">
                                    <?php foreach ($wc_order_statuses as $key => $name) { ?>
                                        <option value="<?php echo $key; ?>" <?php selected($key, $st, true); ?> ><?php echo $name; ?></option>
                                    <?php } ?>
                                </optgroup>
                                <optgroup label="<?php _e('Custom statuses', 'woocommerce_status_actions'); ?>">
                                    <?php foreach ($all_order_statuses as $key => $name) {
                                        if (isset($wc_order_statuses[$key])) continue; ?>
                                        <option value="<?php echo $key; ?>" <?php selected($key, $st, true); ?> ><?php echo $name; ?></option>
                                    <?php } ?>
                                </optgroup>
                            </select>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="7">
                        <input style="margin: 6px;" type="button"
                               value="<?php _e('Reset Defaults', 'woocommerce_status_actions'); ?>"
                               class="button alignright" id="reset_payment_gateways_st">
                    </th>
                </tr>
                </tfoot>
            </table>
            <?php
        }

        public function edit_existing_status($val)
        {
            $data = get_option($val['id']);
            $order_statuses = wc_sa_get_default_order_statuses();
            $color_statuses = WC_SA()->color_statuses;
            $default_editing = WC_SA()->default_editing;
            if (!is_array($data)) {
                $data = array();
                foreach ($order_statuses as $key => $value) {
                    if (isset($default_editing[$key]))
                        $data[$key]['item_editing'] = $default_editing[$key];
                }
            }
            ?>

            <table class="wp-list-table widefat fixed posts default_status_settings">
                <thead>
                <tr>
                    <th><?php _e('Slug', 'woocommerce_status_actions'); ?></th>
                    <th><?php _e('Name', 'woocommerce_status_actions'); ?></th>
                    <th class="status_colour"><?php _e('Colour', 'woocommerce_status_actions'); ?></th>
                    <th style="text-align: center;"><?php _e('Bulk Actions', 'woocommerce_status_actions'); ?> <img
                                width="16" height="16" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png"
                                class="help_tip"
                                data-tip="<?php _e('Check this box to hide this status from the Bulk Actions menu.', 'woocommerce_status_actions'); ?>">
                    </th>
                    <th style="text-align: center;"><?php _e('Item Editing', 'woocommerce_status_actions'); ?> <img
                                width="16" height="16" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png"
                                class="help_tip"
                                data-tip="<?php _e('Check this box to enable item editing for this status.', 'woocommerce_status_actions'); ?>">
                    </th>
                </tr>
                </thead>
                <tbody>

                <?php foreach ($order_statuses as $key => $value) {
                    $label = 'wc-' === substr($key, 0, 3) ? substr($key, 3) : $key
                    ?>
                    <tr valign="top" data-statuskey="<?php echo $key; ?>" class="wc_order_statuses">
                        <input type="hidden" name="<?php echo $val['id']; ?>[<?php echo $key; ?>][icon]"
                               value="<?php echo isset($data[$key]) && !empty($data[$key]['icon']) ? $data[$key]['icon'] : $value; ?>">
                        <th class="titledesc" scope="row">
                            <label><?php echo $key; ?></label>
                        </th>
                        <td class="forminp status_name">
                            <input type="text" name="<?php echo $val['id']; ?>[<?php echo $key; ?>][name]"
                                   value="<?php echo isset($data[$key]) && !empty($data[$key]['name']) ? $data[$key]['name'] : $value; ?>"
                                   class="statusname">
                        </td>
                        <td class="forminp">
                            <?php
                            $color = '';
                            if (isset($color_statuses[$key])) {
                                $color = $color_statuses[$key];
                            }
                            if (isset($data[$key]) && !empty($data[$key]['color']))
                                $color = $data[$key]['color'];
                            ?>
                            <span class="colorpickpreview" style="background-color: <?php echo $color; ?>"></span>
                            <input type="text"
                                   autocomplete="off"
                                   class="color-picker-field statuscolor"
                                   name="<?php echo $val['id']; ?>[<?php echo $key; ?>][color]"
                                   value="<?php echo $color; ?>">
                        </td>
                        <td style="text-align: center; ">
                            <input type="checkbox" name="<?php echo $val['id']; ?>[<?php echo $key; ?>][hide_bulk]"
                                   value="yes" <?php echo isset($data[$key]) && isset($data[$key]['hide_bulk']) ? 'checked="checked"' : ''; ?>>
                        </td>
                        <td style="text-align: center; ">
                            <input class="default_editing" type="checkbox"
                                   name="<?php echo $val['id']; ?>[<?php echo $key; ?>][item_editing]"
                                   value="yes" <?php echo isset($data[$key]) && isset($data[$key]['item_editing']) ? 'checked="checked"' : ''; ?>>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="5">
                        <input style="margin: 6px;" type="button" value="Reset Defaults" class="button alignright"
                               id="wc_order_statuses_reset_defaults">
                    </th>
                </tr>
                </tfoot>
            </table>
            <?php
        }

        public function edit_custom_statuses()
        {
            global $wpdb, $wp;

            if(!empty($_GET["reset_default"])){
                $this->reset_to_default($_GET["reset_default"]);
            }

            $order_statuses = wc_sa_get_statuses(true);

            ?>
            <table id="order-statuses" class="wp-list-table widefat fixed striped posts default_status_settings">
                <thead>
                <th width="10px"></th>
                <th class="column-order_title"><?php _e('Title', 'woocommerce_status_actions'); ?></th>
                <th class="column-order_status"><?php _e('Style', 'woocommerce_status_actions'); ?></th>
                <th class="column-order_module" style="width: 80px;"><?php _e('Actions', 'woocommerce_status_actions'); ?></th>
                <th class="column-order_module"><?php _e('Reports', 'woocommerce_status_actions'); ?></th>
                <th class="column-order_module"><?php _e('Email', 'woocommerce_status_actions'); ?></th>
                <th class="column-order_module"><?php _e('Editing', 'woocommerce_status_actions'); ?></th>
                <th class="column-order_module"><?php _e('Trigger', 'woocommerce_status_actions'); ?></th>
                <th class="column-order_module"><?php _e('Core', 'woocommerce_status_actions'); ?></th>
                <th class="column-order_module"><?php _e('Orders', 'woocommerce_status_actions'); ?></th>
                </thead>
                <tbody>
                <?php if (isset($order_statuses) && $order_statuses) { ?>
                    <?php foreach ($order_statuses as $id => $st) { ?>
                        <?php $status = new WC_SA_Status($id) ?>
                        <?php
			                $title = $status->title;
			            ?>
                        <tr>
                            <td class="sort column-sort ui-sortable-handle" data-post_id= <?= $id ?>></td>
                            <td class="column-order_title">
                                <a class="row-title" href="<?php echo get_edit_post_link($id) ?>"><?php echo $title ?></a><br>
                                <small class="meta"><?php echo $status->label ?></small>
                            </td>
                            <td class="column-order_status">
                                <mark class="status-<?php echo $st->label ?>"><span><?php echo $status->title ?></span>
                                </mark>
                                </div></td>
                            <td class="column-order_action">
                                <button type="button"
                                        class="button wc-action-button status-<?php echo $st->label ?> tips"
                                        data-tip="<?php echo $status->title ?>"></button>
                            </td>
                            <td><?php if ($status->display_in_reports == 'yes') {
                                    printf('<span class="status-enabled tips" data-tip="%s"></span>', __('Included In Reports', 'woocommerce_status_actions'));
                                } else {
                                    printf('<span class="status-disabled tips" data-tip="%s"></span>', __('Not Included In Reports', 'woocommerce_status_actions'));
                                } ?></td>
                            <td><?php
                                if ($status->email_notification == 'yes' && $status->email_recipients == 'both') {
                                    printf('<span class="status-enabled tips" data-tip="%s"></span>', __('Administrator & Customer', 'woocommerce_status_actions'));
                                } else if ($status->email_notification == 'yes' && $status->email_recipients == 'customer') {
                                    printf('<span class="status-enabled tips" data-tip="%s"></span>', __('Customer', 'woocommerce_status_actions'));
                                } else if ($status->email_notification == 'yes' && $status->email_recipients == 'admin') {
                                    printf('<span class="status-enabled tips" data-tip="%s"></span>', __('Administrator', 'woocommerce_status_actions'));
                                } else if ($status->email_notification == 'yes' && $status->email_recipients == 'custom') {
                                    printf('<span class="status-enabled tips" data-tip="%s"></span>', $status->email_custom_address);
                                } else {
                                    printf('<span class="status-disabled tips" data-tip="%s"></span>', __('No', 'woocommerce_status_actions'));
                                }
                                ?></td>
                            <td><?php
                                if ($status->item_editing == 'yes') {
                                    printf('<span class="status-enabled tips" data-tip="%s"></span>', __('Item Editing Enabled', 'woocommerce_status_actions'));
                                } else {
                                    printf('<span class="status-disabled tips" data-tip="%s"></span>', __('Item Editing Disabled', 'woocommerce_status_actions'));
                                }
                                ?></td>
                            <td><?php
                                if ($status->automatic_trigger == 'yes') {
                                    printf('<span class="status-enabled tips" data-tip="%s %s %s %s %s"></span>', __('Automatic Trigger To', 'woocommerce_status_actions'), ucwords(substr($status->triggered_status, 3)), __('After', 'woocommerce_status_actions'), $status->time_period, ucfirst($status->time_period_type));
                                } else {
                                    printf('<span class="status-disabled tips" data-tip="%s"></span>', __('Automatic Trigger Disabled', 'woocommerce_status_actions'));
                                }
                                ?></td>
                            <td>
			                    <?php
			                    if(wc_sa_is_core_status($status->label)) {
			                      printf('<span class="status-enabled tips" data-tip="%s"></span>', __('Core', 'woocommerce_status_actions'));
			                    } else {
				                  printf('<span class="status-disabled tips" data-tip="%s"></span>', __('Custom', 'woocommerce_status_actions'));
			                    } ?>
                            </td>
                            <td><?php echo wc_sa_get_count_orders_by_status_slug($st->label) ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="9" style="text-align: left;"
                            id="no_"><?php _e('No order statuses found.', 'woocommerce_status_actions') ?></td>
                    </tr>
                <?php } ?>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="9">
                        <a class="button alignleft" style="margin: 6px;"
                           href="<?php echo home_url() . '/wp-admin/post-new.php?post_type=wc_custom_statuses' ?>"><?php _e('Add Order Status', 'woocommerce_status_actions'); ?></a>
                    </th>
                </tr>
                </tfoot>
            </table>
        <?php }

        public function wc_sa_google_login()
        {
            $gcpl = new GoogleCloudPrintLibrary_GCPL_v2();
            $library = new GoogleCloudPrintLibrary_Plugin($gcpl);
            if (!empty($_GET['error'])) {
                $library->show_admin_warning(htmlspecialchars($_GET['error']), 'error');
            }

            $admin_page_url = admin_url('admin.php');

            # This is advisory - so the fact it doesn't match IPv6 addresses isn't important
            if (preg_match('#^(https?://(\d+)\.(\d+)\.(\d+)\.(\d+))/#', $admin_page_url, $matches)) {
                echo '<p><strong>' . htmlspecialchars(sprintf(__("%s does not allow authorisation of sites hosted on direct IP addresses. You will need to change your site's address (%s) before you can use %s for storage.", 'google-cloud-print-library'), __('Google Cloud Print', 'google-cloud-print-library'), $matches[1], __('Google Cloud Print', 'google-cloud-print-library'))) . '</strong></em></p>';
            } else {

                ?>            </tbody>
                </table>
                <hr>
                <table class="form-table">
                    <tbody>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="ae-cloud-create_credentials"><?php _e('Create Credentials', 'woocommerce_status_actions'); ?></label>
                            <span class="woocommerce-help-tip"
                                  data-tip="<?php _e('Create credentials for your Google Cloud Print to begin setup of integration.', 'woocommerce_status_actions'); ?>"></span>
                        </th>
                        <td class="forminp">
                            <p><?php _e('Create and connect to the ', 'woocommerce_status_actions'); ?><a class="button"
                                                                                                          id="ae-cloud-create_credentials"
                                                                                                          target="_blank"
                                                                                                          href="https://console.developers.google.com/apis/credentials/oauthclient"><?php _e('Google API', 'woocommerce_status_actions'); ?></a>
                            </p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc alignright">
                            <label><?php _e('Application Type', 'woocommerce_status_actions'); ?></label>
                            <span class="woocommerce-help-tip"
                                  data-tip="<?php _e('This is the type of application used for the integration with Google Cloud Print.', 'woocommerce_status_actions'); ?>"></span>
                        </th>
                        <td class="forminp">
                            <p><?php _e('Choose ', 'woocommerce_status_actions'); ?>
                                <code><?php _e('Web Application', 'woocommerce_status_actions'); ?></code><?php _e(' as the application type.', 'woocommerce_status_actions'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="ae-cloud_uri"><?php _e('Authorized Redirect URI', 'woocommerce_status_actions'); ?></label>
                            <span class="woocommerce-help-tip"
                                  data-tip="<?php _e('Enter this URI when asked for Authorized Redirect URIs, this would be under Restrictions.', 'woocommerce_status_actions'); ?>"></span>
                        </th>
                        <td class="forminp">
                            <input id="ae-cloud_uri" type="text"
                                   value="<?php echo $admin_page_url . '?action=google-cloud-print-auth&page=wc-settings&tab=wc_sa_settings&section=print'; ?>"
                                   size="100" readonly="readonly">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="ae-cloud-authenticate"><?php _e('Authentication', 'woocommerce_status_actions'); ?></label>
                            <span class="woocommerce-help-tip"
                                  data-tip="<?php _e('This is required to ensure that your store is authorised to send prints to your Google Cloud Print.', 'woocommerce_status_actions'); ?>"></span>
                        </th>
                        <td class="forminp">
                            <?php
                            if (current_user_can('manage_options')) {
                                $opts = get_option('wc_sa_google_cloud_print_library_options');

                                if (empty($opts['clientid']) && !empty($opts['username'])) {
                                    $library->show_admin_warning_changedgoogleauth(true);
                                }

                                $clientid = empty($opts['clientid']) ? '' : $opts['clientid'];
                                $token = empty($opts['token']) ? '' : $opts['token'];
                                if (!empty($clientid) && empty($token)) {
                                    echo '<a class="button" target="_blank" id="ae-cloud-authenticate" href="' . admin_url('options-general.php') . '?page=google_cloud_print_library&action=google-cloud-print-auth&gcpl_googleauth=doit">' . sprintf(__('Authenticate %s', 'google-cloud-print-library'), 'Google Cloud Print', 'Google Cloud Print') . '</a>';
                                } else if (!empty($clientid) && !empty($token)) {
                                    echo '<a class="button disabled" id="ae-cloud-authenticate" style="margin-right: 10px;">' . sprintf(__('Authenticated with Google Cloud Print', 'google-cloud-print-library'), 'Google Cloud Print', 'Google Cloud Print') . '</a><a class="button" href="' . admin_url('admin.php?page=wc-settings&tab=wc_sa_settings&section=print&google_printer_action=disconnect') . '">' . sprintf(__('Disconnect', 'google-cloud-print-library'), 'Google Cloud Print', 'Google Cloud Print') . '</a>';
                                } else {
                                    echo '<p>Enter the client ID and client secret to authenticate your site.</p>';
                                }
                            }
                            ?>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="ae-cloud-printer-jobs"><?php _e('Printer Jobs', 'woocommerce_status_actions'); ?></label>
                            <span class="woocommerce-help-tip"
                                  data-tip="<?php _e('View the print jobs of your Google Cloud Print account. Useful if you want to delete or view missing prints.', 'woocommerce_status_actions'); ?>"></span>
                        </th>
                        <td class="forminp">
                            <a class="button" target="_blank"
                               href="https://www.google.com/cloudprint/#jobs"><?php _e('View Print Jobs', 'woocommerce_status_actions'); ?></a>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="ae-cloud-printer-jobs"><?php _e('Available Printers', 'woocommerce_status_actions'); ?></label>
                        </th>
                        <td class="forminp">
                            <?php if (!empty($clientid) && !empty($token)) { ?>
                                <a class="button"
                                   id="refresh-printers"><?php _e('Refresh', 'woocommerce_status_actions'); ?></a><br>
                                <br>
                                <ul class="available-printers-list">
                                    <?php
                                    $cloudPrint = new GoogleCloudPrintLibrary_GCPL_v2();

                                    foreach ($cloudPrint->get_printers() as $printer) { ?>
                                        <li class="available-printers <?php echo $printer->id; ?>"><?php echo $printer->displayName; ?></li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            <?php } else {
                                echo '<p>Authenticate to view a list of Google Cloud Print connected printers.</p>';
                            } ?>
                        </td>
                    </tr><!--

                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="ae-cloud-printer-jobs"><?php _e('Preview order', 'woocommerce_status_actions'); ?></label>
                        </th>
                        <td class="forminp">
                            <input type="text" name="wc_sa_preview_order_id" id="wc_sa_preview_order_id">
                            <a class="button preview"
                               data-preview="invoice"><?php _e('Invoice', 'woocommerce_status_actions'); ?></a>
                            <a class="button preview"
                               data-preview="packing"><?php _e('Package', 'woocommerce_status_actions'); ?></a>
                            <span class="order_number_error"><?php _e('Enter order number first', 'woocommerce_status_actions'); ?></span>
                        </td>
                    </tr>
-->
                    </tbody>
                </table>
                <?php
            }
        }

        public function template_icon_picker()
        {
            $src = wp_get_attachment_url(get_option('wc_sa_template_image'));
            ?>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="ae-invoice-logo"><?php _e('Store Logo', 'woocommerce_status_actions'); ?></label>
                        <span class="woocommerce-help-tip"
                              data-tip="<?php _e('Set the logo of your templates using your stores logo.', 'woocommerce_status_actions'); ?>"></span>
                    </th>
                    <td>
                        <div class="invoice_preview">
                            <img id='ae-invoice-logo' src='<?php echo $src ?>' style='max-height: 24px;'>
                            <span class="ae-invoice-shop">
							    <?php echo bloginfo('name'); ?><br>
                                <?php $store_address = sprintf('%s<br> %s<br> %s, %s<br>', get_option('woocommerce_store_address'), get_option('woocommerce_store_address_2'), get_option('woocommerce_store_city'), get_option('woocommerce_store_postcode')); ?>
                                <?= $store_address ?>
							</span>
                            <input id="upload_image_button" style="margin-right: 10px;" type="button" class="button" value="<?php _e('Upload Logo'); ?>"/>
                            <input id="remove_image_button" type="button" class="button" value="<?php _e('Remove Logo'); ?>"/>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php
        }

        public function order_status_workflow()
        {
            include_once dirname( __FILE__ ) . '/settings/views/html-admin-page-order-status-workflow.php';
        }

        public function status_action_license()
        {
            include_once WP_PLUGIN_DIR . '/woocommerce-status-actions/updater/pages/index.php';
        }

        public function reset_to_default($status_id)
        {
            $post = get_post($status_id);
            $post_metas = get_post_meta($status_id);
            foreach ($post_metas as $meta => $value){
                if(in_array($meta, array('_hide_bulk_actions', '_action_from_users', '_action_to_users'))){
                    if($meta == "_hide_bulk_actions"){
                        update_post_meta($status_id, $meta, array_key_exists($post->post_name, WC_SA()->default_bulk_action) ? "yes" : "no");
                    }else{
                        update_post_meta($status_id, $meta, '');
                    }
                    continue;
                }
                delete_post_meta($status_id, $meta);
            }

            wp_update_post(array(
                'ID' => $post->ID,
                'post_title' => WC_SA()->default_statuses['wc-' . $post->post_name]
            ));

            wp_redirect(admin_url('admin.php?page=wc-settings&tab=wc_sa_settings'));
        }

    }
endif;
return new WC_SA_Settings();