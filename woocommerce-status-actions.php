<?php
/**
 * Plugin Name: WooCommerce Order Status & Actions Manager
 * Plugin URI: http://codecanyon.net/item/woocommerce-customer-relationship-manager/6392174&ref=actualityextensions
 * Description: Enhance your workflow and create  order statuses with actions. Manage emails, permissions and other order related features.
 * Version: 2.4.2
 * Author: Actuality Extensions
 * Author URI: http://actualityextensions.com/
 * Tested up to: 5.1.1
 *
 * Text Domain: woocommerce_status_actions
 * Domain Path: /lang/
 *
 * Copyright: (c) 2013-2019 Actuality Extensions (info@actualityextensions.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package     WC-Custom-Status
 * @author      ActualityExtensions
 * @category    Plugin
 * @copyright   Copyright (c) 2013-2019, Actuality Extensions
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 * WC requires at least: 3.5.0
 * WC tested up to: 3.5.7
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
if (function_exists('is_multisite') && is_multisite()) {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) )
        return;
}else{
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
        return; // Check if WooCommerce is active    
}

require 'updater/updater.php';
global $aebaseapi;
$aebaseapi->add_product(__FILE__);

// Load plugin class files
require_once( 'includes/class-wc-sa.php' );

/**
 * Returns the main instance of WC_SA to prevent the need to use globals.
 *
 * @since    1.4.9
 * @return WC_SA $instance
 */

function WC_SA () {
    $instance = WC_SA::instance( __FILE__, '2.3.7' );
    return $instance;
}
// Global for backwards compatibility.
$GLOBALS['woocommercestatusactions'] = WC_SA();