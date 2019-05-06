<?php if ( ! defined( 'ABSPATH' ) ) { return; } /*#!-- Do not allow this file to be loaded unless in WP context*/
/**
 * This is the plugin's default page
 */
global $aebaseapi;
$purchase_codes = get_option(AEBaseApi::PURCHASE_CODES_OPTION_KEY, array());
$products = $aebaseapi->get_products();

?>
<style>
    #ae-update-plugins-form table input{ width: 100%; }
    #ae-update-plugins-form .status{
        text-align: center;
        vertical-align: middle;
        width: 30px;
    }
    #ae-update-plugins-form .status .dashicons-dismiss{
        color: #a00;
    }
    #ae-update-plugins-form .status .dashicons-yes{
        color: #73a724;
    }
    
</style>
<div class="wrap about-wrap ae-license">
    <h1><?php _e( 'Welcome', 'wc_point_of_sale' ); ?></h1>
    <p class="about-text"><?php _e( 'Thank you for purchasing from Actuality Extensions! To ensure you get the latest updates for bugs, features and tweaks, please enter the license details for your purchased in the table below.', 'wc_point_of_sale' ); ?></p>
    <hr>
    <div class="feature-section col two-col">
            <div class="col">
                <h4><?php _e( "Step 1 - Subscribe", "wc_point_of_sale" ); ?></h4>
                <p><?php _e( 'Subscribe to our newsletter to get the latest updates on new features and notices.', 'wc_point_of_sale' ); ?></p>
                <a href="http://actualityextensions.us7.list-manage.com/subscribe?u=d360506c406997bb1eb300ec9&id=3a2056f6b4" class="button" target="_blank"><?php esc_html_e( 'Subscribe', 'wc_point_of_sale' ); ?></a>
            </div>
            <div class="col last-feature">
                <h4><?php _e( "Step 2 - CodeCanyon", "wc_point_of_sale" ); ?></h4>
                <p><?php _e( 'Locate your purchase code through logging into ', 'wc_point_of_sale' ); ?><a href="https://codecanyon.net/sign_in" target="_blank"><?php _e( 'CodeCanyon.net', 'wc_point_of_sale' ); ?></a><?php _e( ', then go to Downloads > Plugin Name > Download > License certificate & purchase code. ', 'wc_point_of_sale' ); ?></p>
                <a href="https://codecanyon.net/sign_in/" class="button" target="_blank"><?php esc_html_e( 'Retrieve Purchase Code', 'wc_point_of_sale' ); ?></a>
            </div>
    </div>
    <h4><?php _e( 'Step 3 - Installed Products', 'wc_point_of_sale' ); ?></h4>
</div>
<div class="wrap" style="margin: 25px 40px 0 20px;">
<?php
    $rm = strtoupper($_SERVER['REQUEST_METHOD']);
    if('POST' == $rm)
    {
        if (! isset( $_POST['ae_save_credentials'] )|| ! wp_verify_nonce( $_POST['ae_save_credentials'], 'ae_save_credentials_action' )) { ?>
            <div class="error below-h2">
                <p><?php _e('Invalid request.', 'envato-update-plugins');?></p>
            </div>
        <?php }
        else if(isset($_POST['envato-update-plugins_purchase_code']) ){
            $purchase_codes = array_map('trim', $_POST['envato-update-plugins_purchase_code']);
            update_option(AEBaseApi::PURCHASE_CODES_OPTION_KEY, $purchase_codes);
        }
    }
?>
</div>
<div class="wrap about-wrap" id="ae-update-plugins-form">
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'Product', 'wc_point_of_sale' ); ?></th>
                <th><?php _e( 'Purchase Code', 'wc_point_of_sale' ); ?></th>
                <th class="status"></th>
            </tr>
        </thead>
        <tbody>
        <?php 
        foreach ($products as $file ) {
            $plugin_slug = basename($file, '.php');
            $pluginData = get_plugin_data($file);
            $purchase_code = isset($purchase_codes[$plugin_slug]) ? $purchase_codes[$plugin_slug] : '';
            if( $pluginData ){
                ?>
                <tr>
                    <th scope="row"><strong><?php echo $pluginData['Name']; ?></strong></th>
                    <td><input type="text" placeholder="<?php _e( 'Place your purchase code here', 'wc_point_of_sale' ); ?>" class="regular-text" name="envato-update-plugins_purchase_code[<?php echo $plugin_slug;?>]"
                                value="<?php echo $purchase_code;?>" /></td>
                        <td class="status">
                            <?php
                        $code_validation = ae_updater_validate_code( $plugin_slug, $purchase_code );

                            if( !isset($code_validation->error) &&  ae_updater_validate_code( $plugin_slug, $purchase_code ) ){
                                ?>
                                <span class="dashicons dashicons-yes"></span>
                                <?php
                            }else{
                                ?>
                                <span class="dashicons dashicons-dismiss" title="<?php echo $code_validation->error ?>"></span>
                                <?php
                            }
                            ?>
                        </td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>
    <p class="submit">
        <input type="submit"class="button button-large button-primary" id="envato-update-plugins_submit"
                value="<?php _e( 'Save Settings', 'envato-update-plugins');?>" />
    </p>
    <?php wp_nonce_field( 'ae_save_credentials_action', 'ae_save_credentials');?>
</div>