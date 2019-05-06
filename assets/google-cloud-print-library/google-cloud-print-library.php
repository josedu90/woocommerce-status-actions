<?php
/*
Plugin Name: Google Cloud Print Library
Plugin URI: https://wordpress.org/plugins/google-cloud-print-library
Description: Some routines used for sending simple text files to Google Cloud Print
Author: David Anderson
Version: 0.8.12
License: MIT
Author URI: https://david.dw-perspective.org.uk
Text Domain: google-cloud-print-library
Domain Path: /languages
Donate: https://david.dw-perspective.org.uk/donate
*/

if (!defined('ABSPATH')) die ('No direct access allowed');

define('GOOGLECLOUDPRINTLIBRARY_DIR', dirname(realpath(__FILE__)));
if (!class_exists('GoogleCloudPrintLibrary_GCPL_v2')) require_once(GOOGLECLOUDPRINTLIBRARY_DIR.'/class-gcpl.php');

# Setting this global variable is legacy - there's no reason why there needs to be a global. But, it is used by existing versions of the WooCommerce Print Orders plugin. GoogleCloudPrintLibrary_GCPL_v2 and GoogleCloudPrintLibrary_GCPL are compatible - but we invoke GoogleCloudPrintLibrary_GCPL_v2 specifically here, to prefer our version if it is available (as it will be newer).
if (!isset($googlecloudprintlibrary_gcpl) || !is_a($googlecloudprintlibrary_gcpl, 'GoogleCloudPrintLibrary_GCPL')) $googlecloudprintlibrary_gcpl = new GoogleCloudPrintLibrary_GCPL_v2();

// For PHP 5.3
if (!function_exists('hex2bin')):
function hex2bin($data) {
	$len = strlen($data);
	if (null === $len) {
		return;
	}
	if ($len % 2) {
		trigger_error('hex2bin(): Hexadecimal input string must have an even length', E_USER_WARNING);
		return false;
	}
	return pack('H*', $data);
}
endif;

if (!class_exists('GoogleCloudPrintLibrary_Plugin')):
define('GOOGLECLOUDPRINTLIBRARY_PLUGINVERSION', '0.8.12');
class GoogleCloudPrintLibrary_Plugin {

	public $version;
	
	private $php_required = '5.3';

	public $title = 'Google Cloud Print Library';

	private $option_page = 'google_cloud_print_library';

	private $gcpl;
	private $printers_found = 0;
	private $token;

	public function __construct($gcpl, $option_page = 'google_cloud_print_library') {

		$this->version = GOOGLECLOUDPRINTLIBRARY_PLUGINVERSION;
		$this->gcpl = $gcpl;
		$this->option_page = $option_page;
		
		// Stuff specific to the setup of this plugin
		add_action('plugins_loaded', array($this, 'load_translations'));
		
		if (version_compare(PHP_VERSION, $this->php_required, '<' )) {
			add_action('all_admin_notices', array($this, 'admin_notice_insufficient_php'));
			return;
		}
		
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('admin_init', array($this, 'admin_init'));
		add_filter('plugin_action_links', array($this, 'action_links'), 10, 2 );

		// AJAX actions for our settings page
		add_action('wp_ajax_gcpl_test_print_'.$this->option_page, array($this, 'test_print'));
		add_action('wp_ajax_gcpl_refresh_printers_'.$this->option_page, array($this, 'google_cloud_print_library_options_printer'));

		// Provide default values from this plugin's settings
		add_filter('google_cloud_print_copies', array($this, 'google_cloud_print_copies'));
		add_filter('google_cloud_print_options', array($this, 'google_cloud_print_options'));

		add_filter('woocommerce_printorders_wcpdf_add_body_padding', array($this, 'woocommerce_printorders_wcpdf_add_body_padding'));
		
		add_action('init', array($this, 'handle_url_actions'));
	}

	public function admin_notice_insufficient_php() {
		$this->show_admin_warning('<strong>'.__('Higher PHP version required', 'google-cloud-print-library').'</strong><br> '.sprintf(__('Google Cloud Print interaction requires PHP version %s or higher - your current version is only %s.', 'google-cloud-print-library'), $this->php_required, PHP_VERSION), 'error');
	}
	
	// This is for backward compatibility with previous versions of the "WooCommerce automatic order printing" plugin, which added a margin on PDFs from the WooCommerce PDF Invoicing plugin. Now that in this plugin you can set arbitrary margins, that is unnecessary and unwanted.
	public function woocommerce_printorders_wcpdf_add_body_padding($add) {
		if (!$add || !is_a($this->gcpl, 'GoogleCloudPrintLibrary_GCPL')) return $add;

		$options = get_option('wc_sa_google_cloud_print_library_options');
		
		if (isset($options['printer_options']) && is_array($options['printer_options'])) {
		
			foreach ($options['printer_options'] as $id => $printer) {
				if (isset($printer['printer_margin']['top'])) return false;
			}
		
		}
		
		return $add;
	}
	
	public function handle_url_actions() {
		// First, basic security check: must be an admin page, with ability to manage options, with the right parameters
		// Also, only on GET because WordPress on the options page repeats parameters sometimes when POST-ing via the _wp_referer field
		if (isset($_SERVER['REQUEST_METHOD']) && 'GET' == $_SERVER['REQUEST_METHOD'] && isset($_GET['action']) && 'google-cloud-print-auth' == $_GET['action'] && current_user_can('manage_options')) {
			$_GET['page'] = $this->option_page;
			$_REQUEST['page'] = $this->option_page;
			if (isset($_GET['state'])) {
				if ('success' == $_GET['state']) add_action('all_admin_notices', array($this, 'show_authed_admin_success'));
				elseif ('token' == $_GET['state']) $this->googleauth_auth_token();
				elseif ('revoke' == $_GET['state']) $this->googleauth_auth_revoke();
			} elseif (isset($_GET['gcpl_googleauth'])) {
				$this->googleauth_auth_request();
			}
		}
	}

	// Acquire single-use authorization code from Google OAuth 2.0
	public function googleauth_auth_request() {

		$opts = get_option('wc_sa_google_cloud_print_library_options', array());

		// First, revoke any existing token, since Google doesn't appear to like issuing new ones
		if (!empty($opts['token'])) $this->googleauth_auth_revoke();
		// We use 'force' here for the approval_prompt, not 'auto', as that deals better with messy situations where the user authenticated, then changed settings

		$params = array(
			'response_type' => 'code',
			'client_id' => $opts['clientid'],
			'redirect_uri' => $this->redirect_uri(),
			'scope' => 'https://www.googleapis.com/auth/cloudprint',
			'state' => 'token',
			'access_type' => 'offline',
			'approval_prompt' => 'force'
		);
		if (headers_sent()) {
			add_action('all_admin_notices', array($this, 'admin_notice_something_breaking'));
		} else {
			header('Location: https://accounts.google.com/o/oauth2/auth?'.http_build_query($params, null, '&'));
		}
	}

	public function admin_notice_something_breaking() {
		$this->show_admin_warning(sprintf(__('The %s authentication could not go ahead, because something else on your site is breaking it. Try disabling your other plugins and switching to a default theme. (Specifically, you are looking for the component that sends output (most likely PHP warnings/errors) before the page begins. Turning off any debugging settings may also help).', 'google-cloud-print-library'), 'Google Cloud Print'), 'error');
	}

	private function redirect_uri() {
		return admin_url('admin.php') . '?action=google-cloud-print-auth&page=wc-settings&tab=wc_sa_settings&section=print';
	}

	// Revoke a Google account refresh token
	public function googleauth_auth_revoke($token = false, $unsetopt = true) {
		if (empty($token)) {
			$opts = get_option('wc_sa_google_cloud_print_library_options', array());
			$token = empty($opts['token']) ? '' : $opts['token'];
		}
		if ($token) wp_remote_get('https://accounts.google.com/o/oauth2/revoke?token='.$token);
		if ($unsetopt) {
			$opts = get_option('wc_sa_google_cloud_print_library_options', array());
			$opts['token'] = '';
			update_option('wc_sa_google_cloud_print_library_options', $opts);
		}
	}

	// Get a Google account refresh token using the code received from googleauth_auth_request
	public function googleauth_auth_token() {

		$opts = get_option('wc_sa_google_cloud_print_library_options', array());

		if(isset($_GET['code'])) {
			$post_vars = array(
				'code' => $_GET['code'],
				'client_id' => $opts['clientid'],
				'client_secret' => $opts['clientsecret'],
				'redirect_uri' => $this->redirect_uri(),
				'grant_type' => 'authorization_code'
			);

			$googleauth_request_options = apply_filters('google_cloud_print_googleauth_request_options', array('timeout' => 25, 'method' => 'POST', 'body' => $post_vars));
			
			$result = wp_remote_post('https://accounts.google.com/o/oauth2/token', $googleauth_request_options);

			if (is_wp_error($result)) {
				$add_to_url = "Bad response when contacting Google: ";
				foreach ( $result->get_error_messages() as $message ) {
					error_log("Google Drive authentication error: ".$message);
					$add_to_url .= $message.". ";
				}
				header('Location: '.admin_url('options-general.php').'?page='.$this->option_page.'&error='.urlencode($add_to_url));
			} else {
				$json_values = json_decode($result['body'], true);

				if (isset($json_values['refresh_token'])) {

					 // Save token
					$opts['token'] = $json_values['refresh_token'];
					update_option('wc_sa_google_cloud_print_library_options', $opts);

					if (isset($json_values['access_token'])) {
						$opts['tmp_access_token'] = $json_values['access_token'];
						update_option('wc_sa_google_cloud_print_library_options', $opts);
						// We do this to clear the GET parameters, otherwise WordPress sticks them in the _wp_referer in the form and brings them back, leading to confusion + errors
                        header('Location: ' . admin_url('admin.php') . '?page=wc-settings&tab=wc_sa_settings&section=print&state=success');
					}

				} else {

					$msg = __('No refresh token was received from Google. This often means that you entered your client secret wrongly, or that you have not yet re-authenticated (below) since correcting it. Re-check it, then follow the link to authenticate again. Finally, if that does not work, then use expert mode to wipe all your settings, create a new Google client ID/secret, and start again.', 'google-cloud-print-library');

					if (isset($json_values['error'])) $msg .= ' '.sprintf(__('Error: %s', 'google-cloud-print-library'), $json_values['error']);

                    header('Location: ' . admin_url('admin.php') . '?page=wc-settings&tab=wc_sa_settings&section=print&error=' . urlencode($msg));
				}
			}
		} else {
			$err_msg = __('Authorization failed', 'google-cloud-print-library');
			if (!empty($_GET['error'])) $err_msg .= ': '.$_GET['error'];
			header('Location: '.admin_url('options-general.php').'?page='.$this->option_page.'&error='.urlencode($err_msg));
		}
	}

	public function show_authed_admin_success() {

// 		global $updraftplus_admin;

		$opts = get_option('wc_sa_google_cloud_print_library_options', array());

		if (empty($opts['tmp_access_token'])) return;
		$tmp_access_token = $opts['tmp_access_token'];

		$message = '';

		$this->show_admin_warning(__('Success', 'google-cloud-print-library').': '.sprintf(__('you have authenticated your %s account.', 'google-cloud-print-library'),__('Google Cloud Print','google-cloud-print-library')).' ');

		unset($opts['tmp_access_token']);
		update_option('wc_sa_google_cloud_print_library_options', $opts);

	}

	public function load_translations() {
		load_plugin_textdomain('google-cloud-print-library', false, dirname(__FILE__).'/languages/');
	}

	public function google_cloud_print_options($options) {
		if (!empty($options)) return $options;
		$options = get_option('wc_sa_google_cloud_print_library_options', array());
		
		if (empty($options['timeout'])) $options['timeout'] = 15;
		
		return $options;
	}

	public function google_cloud_print_copies($copies) {
		if (false !== $copies) return $copies;
		$options = get_option('wc_sa_google_cloud_print_library_options');
		return (int)$options['copies'];
	}

	public function admin_init() {
		register_setting( 'google_cloud_print_library_options', 'google_cloud_print_library_options' , array($this, 'options_validate') );

		add_settings_section ( 'google_cloud_print_library_options', 'Google Cloud Print', array($this, 'options_header') , 'google_cloud_print_library');

		add_settings_field ( 'google_cloud_print_library_options_clientid', __('Google Client ID', 'google-cloud-print-library'), array($this, 'google_cloud_print_library_options_clientid'), 'google_cloud_print_library' , 'google_cloud_print_library_options' );
		add_settings_field ( 'google_cloud_print_library_options_clientsecret', __('Google Client Secret', 'google-cloud-print-library'), array($this, 'google_cloud_print_library_options_clientsecret'), 'google_cloud_print_library' , 'google_cloud_print_library_options' );
		add_settings_field ( 'google_cloud_print_library_options_printer', __('Printer', 'google-cloud-print-library'), array($this, 'google_cloud_print_library_options_printer'), 'google_cloud_print_library' , 'google_cloud_print_library_options' );
		add_settings_field ( 'google_cloud_print_library_options_copies', __('Copies', 'google-cloud-print-library'), array($this, 'google_cloud_print_library_options_copies'), 'google_cloud_print_library' , 'google_cloud_print_library_options' );
		add_settings_field ( 'google_cloud_print_library_options_header', __('Print job header', 'google-cloud-print-library'), array($this, 'google_cloud_print_library_options_header'), 'google_cloud_print_library' , 'google_cloud_print_library_options' );

//		if (current_user_can('manage_options')) {
//			$opts = get_option('google_cloud_print_library_options');
//
//			if (empty($opts['clientid']) && !empty($opts['username'])) {
//				if (empty($_GET['page']) || 'google_cloud_print_library' != $_GET['page']) add_action('all_admin_notices', array($this,'show_admin_warning_changedgoogleauth'));
//			} else {
//
//				$clientid = empty($opts['clientid']) ? '' : $opts['clientid'];
//				$token = empty($opts['token']) ? '' : $opts['token'];
//				if (!empty($clientid) && empty($token)) add_action('all_admin_notices', array($this,'show_admin_warning_googleauth'));
//			}
//		}

	}

	public function show_admin_warning_googleauth($suppress_title = false) {
		$warning = ($suppress_title) ? '' : '<strong>'.__('Google Cloud Print notice:','google-cloud-print-library').'</strong> ';
		$warning .= '<a href="'.admin_url('options-general.php').'?page='.$this->option_page.'&action=google-cloud-print-auth&gcpl_googleauth=doit">'.sprintf(__('Click here to authenticate your %s account (you will not be able to print via %s without it).','google-cloud-print-library'),'Google Cloud Print','Google Cloud Print').'</a>';
		$this->show_admin_warning($warning);
	}

	public function show_admin_warning_changedgoogleauth($suppress_title = false) {
		$warning = ($suppress_title) ? '' : '<strong>'.__('Google Cloud Print notice:','google-cloud-print-library').'</strong> ';
		if ($suppress_title) {
			$warning .= sprintf(__('Google have recently abolished the previous authentication method for Google Cloud Print. You should enter new credentials to authenticate your %s account.','google-cloud-print-library'),'Google Cloud Print','Google Cloud Print');
		} else {
			$warning .= '<a href="'.admin_url('options-general.php').'?page='.$this->option_page.'">'.sprintf(__('Google have recently abolished the previous authentication method for Google Cloud Print. Go here and enter new credentials to authenticate your %s account.','google-cloud-print-library'),'Google Cloud Print','Google Cloud Print').'</a>';
		}
		$this->show_admin_warning($warning);
	}

	public function test_print() {

		if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'gcpl-nonce') || empty($_POST['printer']) || empty($_POST['printtext'])) die;

		$printers = is_array($_POST['printer']) ? $_POST['printer'] : array($_POST['printer']);

		$success = true;
		$message = false;

		foreach ($printers as $printer) {

			if (!$printer) continue;

			$printed = $this->gcpl->print_document($printer, __('Google Cloud Print Test', 'google-cloud-print-library'), '<p>'.nl2br($_POST['printtext']).'</p>', $_POST['prependtext'], (int)$_POST['copies']);

			if (!isset($printed->success) || !$printed->success) {
				$success = false;
				if (isset($printed->message)) {
					$message = $printed->message;
				}
			}

		}

		if ($success) {
			echo json_encode(array('result' => 'ok'));
			die;
		} elseif ($message) {
			echo json_encode(array('result' => $message));
			die;
		}

		echo json_encode(array('result' => __("Non-understood response:", 'google-cloud-print-library')." ".serialize($printed)));
		die;

	}

	public function show_admin_warning($message, $class = "updated") {
		echo '<div class="'.$class.'">'."<p>$message</p></div>";
	}

	public function google_cloud_print_library_options_clientid() {
		$options = get_option('wc_sa_google_cloud_print_library_options', array());
		$clientid = (empty($options['clientid'])) ? '' : $options['clientid'];
		echo '<input id="google_cloud_print_library_options_clientid" name="google_cloud_print_library_options[clientid]" size="72" type="text" value="'.esc_attr($clientid).'" />';
		echo '<br><em>'.__('See the instructions above to learn how to get this', 'google-cloud-print-library').'</em>';
	}

	public function google_cloud_print_library_options_clientsecret() {
		$options = get_option('wc_sa_google_cloud_print_library_options', array());
		$clientsecret = (empty($options['clientsecret'])) ? '' : $options['clientsecret'];
		echo '<input id="google_cloud_print_library_options_clientsecret" name="google_cloud_print_library_options[clientsecret]" size="72" type="password" value="'.esc_attr($clientsecret).'" /><br><em>';
	}

	public function google_cloud_print_library_options_header() {
		$options = get_option('wc_sa_google_cloud_print_library_options');
		echo '<textarea id="google_cloud_print_library_options_header" name="google_cloud_print_library_options[header]" rows="10" cols="60" />'.htmlspecialchars(isset($options['header']) ? $options['header'] : "").'</textarea><br>';
		echo '<em>'.__('Anything you enter here will be pre-pended to the print job. Use any valid HTML (including &lt;style&gt; tags)', 'google-cloud-print-library').'</em>';
	}

	public function google_cloud_print_library_options_copies() {
		$options = get_option('wc_sa_google_cloud_print_library_options');
		$copies = max(intval(isset($options['copies']) ? $options['copies'] : 0), 1);
		echo '<input id="google_cloud_print_library_options_copies" name="google_cloud_print_library_options[copies]" size="2" type="text" value="'.$copies.'" maxlength="3" /><br>';
	}

	// This function is both an options field printer, and called via AJAX
	public function google_cloud_print_library_options_printer() {

		if (defined('DOING_AJAX') && DOING_AJAX == true && (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'gcpl-nonce'))) die;

		$options = get_option('wc_sa_google_cloud_print_library_options');

		$printers = $this->gcpl->get_printers();

		$this->printers_found = is_wp_error($printers) ? 0 : count($printers);

		$this->debug_output = '';
		
		if (is_wp_error($printers)) {

			echo '<input type="hidden" name="google_cloud_print_library_options[printer]" value=""><em>'.strip_tags($printers->get_error_message()).'</em>';

		} elseif (count($printers) == 0) {

			echo '<input type="hidden" name="google_cloud_print_library_options[printer]" value=""><em>('.__('Account either not connected, or no printers available)', 'google-cloud-print-library').'</em>';

		} else {

			echo '<div id="google_cloud_print_library_options_printer_container">';

			// Make sure the option gets saved
			echo '<input name="google_cloud_print_library_options[printer][]" type="hidden" value="">';

			foreach ($printers as $printer) {
			
				echo '<div style="float: left; clear: both; width:100%;">';
			
				echo '<input onchange="google_cloud_print_confirm_unload = true;" class="google_cloud_print_library_options_printer" id="google_cloud_print_library_options_printer_'.esc_attr($printer->id).'" name="google_cloud_print_library_options[printer][]" type="checkbox" '.((isset($options['printer']) && ((is_array($options['printer']) && in_array($printer->id, $options['printer'])) || (!is_array($options['printer']) && $options['printer'] == $printer->id))) ? 'checked="checked"' : '').'value="'.htmlspecialchars($printer->id).'"><label for="google_cloud_print_library_options_printer_'.esc_attr($printer->id).'">'.htmlspecialchars($printer->displayName).'</label><br>';
				
				if (isset($printer->_gcpl_printer_opts)) {
				
					$this->debug_output .= '<h4>'.htmlspecialchars($printer->displayName).'</h4><pre>'.htmlspecialchars(print_r($printer->_gcpl_printer_opts, true)).'</pre>';
					
					if (isset($printer->_gcpl_printer_opts->capabilities) && isset($printer->_gcpl_printer_opts->capabilities->printer)) {

						$capabilities = (array)$printer->_gcpl_printer_opts->capabilities->printer;
						
						// Interesting ones: media_size, fit_to_page
						
// 						foreach ($capabilities as $key => $cap) {
// 						}

						if (isset($capabilities['color'])) {
							echo '<div style="margin-left:30px; clear:both;">';
							echo $this->generate_selector($printer->id, 'color', $capabilities, __('Color', 'google-cloud-print-library'), $options);
							echo '</div>';
						}

						if (isset($capabilities['media_size'])) {
							echo '<div style="margin-left:30px; clear:both;">';
							echo $this->generate_selector($printer->id, 'media_size', $capabilities, __('Media size', 'google-cloud-print-library'), $options);
							echo '</div>';
						}
						
						if (isset($capabilities['fit_to_page'])) {
							echo '<div style="margin-left:30px; clear:both;">';
							echo $this->generate_selector($printer->id, 'fit_to_page', $capabilities, __('Fit to page', 'google-cloud-print-library'), $options);
							echo '</div>';
						}
						
						// These are always available, and thus isn't in the printer capabilities sent by Google.
						$capabilities['page_orientation'] = new stdClass;
						$orient_portrait = new stdClass;
						$orient_portrait->type = 'PORTRAIT';
						$orient_landscape = new stdClass;
						$orient_landscape->type = 'LANDSCAPE';
						$orient_auto = new stdClass;
						$orient_auto->type = 'AUTO';
						$orient_auto->is_default = true;

						$capabilities['page_orientation']->option = array(
							$orient_portrait,
							$orient_landscape,
							$orient_auto
						);
						
						echo '<div style="margin-left:30px; clear:both;">';
						echo $this->generate_selector($printer->id, 'page_orientation', $capabilities, __('Page orientation', 'google-cloud-print-library'), $options);
						echo '</div>';
						
						if ('__google__docs' != $printer->id) {

							?><div style="margin-left:30px; clear:both;">
								<?php _e('Margins:', 'google-cloud-print-library');?><br>
							
								<div style="margin-left:30px; clear:both;">
							
									<?php echo $this->margin_selector('top', $printer->id, __('Top', 'google-cloud-print-library'), $options); ?>
									<?php echo $this->margin_selector('right', $printer->id, __('Right', 'google-cloud-print-library'), $options); ?>
									<?php echo $this->margin_selector('bottom', $printer->id, __('Bottom', 'google-cloud-print-library'), $options); ?>
									<?php echo $this->margin_selector('left', $printer->id, __('Left', 'google-cloud-print-library'), $options); ?>
								</div>
							
						</div>
						<?php
						
						}
					
					}
					
				}
				
				echo '</div>';
			}
// 			echo '</select>';
			echo '</div>';

			if (defined('DOING_AJAX') && DOING_AJAX == true) die;

			echo '<div style="clear:both;"> <a href="#" id="gcpl_refreshprinters">('.__('refresh', 'google-cloud-print-library').')</a></div>';

		}

	}

	private function margin_selector($id, $printer_id, $label, $current_options) {
		$ret = '';
		
		$ret .= "<div style=\"width: 50px; float:left; clear: left; padding: 4px 2px 0 0;\">$label: </div>";
		
		$value = isset($current_options['printer_options'][$printer_id]['printer_margin'][$id]) ? $current_options['printer_options'][$printer_id]['printer_margin'][$id] : 20;
		
		if (!is_numeric($value)) $value = 20;
		
		$ret .= '<div style="float: left;"><input type="number" name="google_cloud_print_library_options[printer_options]['.esc_attr($printer_id).'][printer_margin]['.$id.']" min="0" step="1" style="width: 50px;" value="'.esc_attr($value).'">';
		
		$ret .= ' '.__('mm', 'google-cloud-print-library').' </div>';
		
		return $ret;
	}

	private function generate_selector($printer_id, $cap_id, $capabilities, $title = '', $current_options) {
	
		if ('__google__docs' == $printer_id || !is_array($capabilities) || empty($capabilities[$cap_id])) return '';

		$cap = $capabilities[$cap_id];
		
		if (!is_object($cap) || !isset($cap->option) || !is_array($cap->option)) return '';
		
		$option = $cap->option;
		
		$selector_id = esc_attr('gcpl_capability_'.$printer_id.'_'.$cap_id);
		
		$output = '<label for="'.$selector_id.'">'.htmlspecialchars($title).':</label> ';
		
		$output .= '<select name="google_cloud_print_library_options[printer_options]['.esc_attr($printer_id).']['.esc_attr($cap_id).']" onchange="google_cloud_print_confirm_unload = true;" id="'.$selector_id.'">'."\n";
		
		$selected = null;
		if (isset($current_options['printer_options'][$printer_id][$cap_id])) {
			if (null !== ($decode_current_option = json_decode($current_options['printer_options'][$printer_id][$cap_id]))) {
				if (isset($decode_current_option->label)) $selected = $decode_current_option->label;
			}
		}

		foreach ($option as $opt) {

			if ('fit_to_page' == $cap_id) {
				$values_to_save = array('type');
// 				$name = $opt->type;
				if ('NO_FITTING' == $opt->type) {
					$label = __('Do not fit to page', 'google-cloud-print-library');
				} elseif ('FIT_TO_PAGE' == $opt->type) {
					$label = __('Fit to page', 'google-cloud-print-library');
				} else {
					$label = $opt->type;
				}
			} elseif ('page_orientation' == $cap_id) {
				$values_to_save = array('type');
				
				if ('PORTRAIT' == $opt->type) {
					$label = __('Portrait', 'google-cloud-print-library');
				} elseif ('LANDSCAPE' == $opt->type) {
					$label = __('Landscape', 'google-cloud-print-library');
				} elseif ('AUTO' == $opt->type) {
					$label = __('Automatic', 'google-cloud-print-library');
				} else {
					$label = $opt->type;
				}
			} elseif ('color' == $cap_id) {

				$values_to_save = array('type');
				
				if ('STANDARD_MONOCHROME' == $opt->type) {
					$label = __('Monochrome', 'google-cloud-print-library');
				} elseif ('STANDARD_COLOR' == $opt->type) {
					$label = __('Color', 'google-cloud-print-library');
				} elseif ('AUTO' == $opt->type) {
					$label = __('Automatic', 'google-cloud-print-library');
				} else {
					// Drop CUSTOM_COLOR, CUSTOM_MONOCHROME
					continue;
				}
				
			} else {
				$values_to_save = array('width_microns', 'height_microns', 'is_continuous_feed');
// 				$name = $opt->name;
				if (empty($opt->custom_display_name)) {
					if (empty($opt->name)) {
						$label = "(Option has no display name)";
					} else {
						$label = $opt->name;
					}
				} else {
					$label = $opt->custom_display_name;
				}
			}
		
			$value = array();
			foreach ($values_to_save as $key) {
				$value[$key] = isset($opt->$key) ? $opt->$key : false;
			}
			
			$value['label'] = $label;
		
			$output .= '<option value="'.esc_attr(json_encode($value)).'"';
			
			if ((null === $selected && !empty($opt->is_default)) || (null !== $selected && $label == $selected)) $output .= ' selected="selected"';
			$output .= '>';
			$output .= htmlspecialchars($label);
			$output .= '</option>';
		
		}
		
		$output .= "</select><br>\n";
		
		return $output;
		
	}

	public function options_validate($google) {

			$opts = get_option('wc_sa_google_cloud_print_library_options', array());

			// Remove legacy options
			unset($opts['username']);
			unset($opts['password']);

			if (!is_array($google)) return $opts;

			$old_client_id = (empty($opts['clientid'])) ? '' : $opts['clientid'];
			if (!empty($opts['token']) && $old_client_id != $google['clientid']) {
				$this->googleauth_auth_revoke($opts['token'], false);
				$google['token'] = '';
				delete_transient('wc_sa_google_cloud_print_library_printers');
			}

			foreach ($google as $key => $value) {
				// Trim spaces - I got support requests from users who didn't spot the spaces they introduced when copy/pasting
				$opts[$key] = ('clientid' == $key || 'clientsecret' == $key) ? trim($value) : $value;
			}

			return $opts;
	}
/*
	private function old_unused_and_now_mangled_options_validate($options) {
			// Authenticate
			$authed = $this->gcpl->authorize(
				$input['clientid'],
				$input['clientsecret']
			);

			$existing_options = get_option('google_cloud_print_library_options');

		if (1) {
			// We don't actually store the password - that's not needed
			$input['password'] = (isset($existing_options['password'])) ? $existing_options['password'] : '';

			if ($authed === false || is_wp_error($authed)) {
				if ($authed === false) {
					$msg = __('We did not understand the response from Google.', 'google-cloud-print-library');
					add_settings_error("google_cloud_print_library_options_clientid", 'google_cloud_print_library_options_clientid', $msg);
				} else {
					foreach ($authed->get_error_messages() as $msg) {
						add_settings_error("google_cloud_print_library_options_clientid", 'google_cloud_print_library_options_clientid', $msg);
					}
				}
			} else {
				// For reasons unknown, recent versions of WP call through options_validate() again with the updated value, which then leads to an authorisation failure when we pass it to Google. To avoid that, we store it to detect the double-run.
				$this->token = $authed;
				$input['password'] = $authed;
			}
			
		} else {
			$existing_options = get_option('google_cloud_print_library_options');

			if (!empty($this->token)) {
				$input['password'] = $this->token;
			} else {
				// We don't actually store the password - that's not needed
				$input['password'] = (isset($existing_options['password'])) ? $existing_options['password'] : '';
			}
		}

		return $input;
	}*/

	public function options_header() {

		if (!empty($_GET['error'])) {
			$this->show_admin_warning(htmlspecialchars($_GET['error']), 'error');
		}

		echo __('Google Cloud Print links:', 'google-cloud-print-library').' ';
		echo '<a href="https://www.google.com/cloudprint/learn/">'.__('Learn about Google Cloud Print', 'google-cloud-print-library').'</a>';
		echo ' | ';
		echo '<a href="https://www.google.com/cloudprint/#printers">'.__('Your printers', 'google-cloud-print-library').'</a>';
		echo ' | ';
		echo '<a href="https://www.google.com/cloudprint/#jobs">'.__('Your print jobs', 'google-cloud-print-library').'</a>';

		if (current_user_can('manage_options')) {
			$opts = get_option('wc_sa_google_cloud_print_library_options');

			if (empty($opts['clientid']) && !empty($opts['username'])) {
				$this->show_admin_warning_changedgoogleauth(true);
			}

			$clientid = empty($opts['clientid']) ? '' : $opts['clientid'];
			$token = empty($opts['token']) ? '' : $opts['token'];
			if (!empty($clientid) && empty($token)) {
				$this->show_admin_warning_googleauth(true);
			} elseif (!empty($clientid) && !empty($token)) {
				echo '<p><a href="'.admin_url('options-general.php').'?page='.$this->option_page.'&action=google-cloud-print-auth&gcpl_googleauth=doit">'.sprintf(__('You appear to be authenticated with Google Cloud Print, but if you are seeing authorisation errors, then you can click here to authenticate your %s account again.','google-cloud-print-library'),'Google Cloud Print','Google Cloud Print').'</a></p>';
			}
		}

	}

	public function admin_menu() {
		# http://codex.wordpress.org/Function_Reference/add_options_page
        add_submenu_page(false, 'Google Cloud Print', 'Google Cloud Print', 'manage_options', $this->option_page, array($this, 'options_printpage'));
	}

	public function action_links($links, $file) {
		$us = basename(dirname(__FILE__)).'/'.basename(__FILE__);
		if ( $file == $us ){
			array_unshift( $links, 
				'<a href="options-general.php?page='.$this->option_page.'">'.__('Settings').'</a>',
				'<a href="https://updraftplus.com/">'.__('UpdraftPlus WordPress backups', 'google-cloud-print-library').'</a>'
			);
		}
		return $links;
	}

	# This is the function outputing the HTML for our options page
	public function options_printpage() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}

		wp_enqueue_script('jquery-ui-spinner', false, array('jquery'));

		$pver = GOOGLECLOUDPRINTLIBRARY_PLUGINVERSION;

		$title = htmlspecialchars($this->title);

		echo <<<ENDHERE
	<div style="clear: left;width:950px; float: left; margin-right:20px;">

		<h1>$title (version $pver)</h1>
ENDHERE;

		echo '<p>Authored by <strong>David Anderson</strong> (<a href="http://david.dw-perspective.org.uk">Homepage</a> | <a href="https://updraftplus.com">UpdraftPlus - Best WordPress Backup</a> | <a href="https://www.simbahosting.co.uk/s3/shop/">Other WordPress / WooCommerce plugins</a>)</p>';

		#  | <a href="http://wordpress.org/plugins/google-cloud-print-library">'.__('Instructions', 'google-cloud-print-library').'</a>)

		echo "<div>\n";

		echo '<div style="margin:4px; padding:6px; border: 1px dotted;">';

		echo '<p><em><strong>'.__('Instructions', 'google-cloud-print-library').':</strong></em></p> ';

		?><a href="https://www.simbahosting.co.uk/s3/support/configuring-google-drive-api-access-for-cloud-print/"><strong><?php _e('For longer help, including screenshots, follow this link. The description below is sufficient for more expert users.', 'google-cloud-print-library');?></strong></a><?php

		$admin_page_url = admin_url('options-general.php');

		# This is advisory - so the fact it doesn't match IPv6 addresses isn't important
		if (preg_match('#^(https?://(\d+)\.(\d+)\.(\d+)\.(\d+))/#', $admin_page_url, $matches)) {
			echo '<p><strong>'.htmlspecialchars(sprintf(__("%s does not allow authorisation of sites hosted on direct IP addresses. You will need to change your site's address (%s) before you can use %s for storage.", 'google-cloud-print-library'), __('Google Cloud Print', 'google-cloud-print-library'), $matches[1], __('Google Cloud Print', 'google-cloud-print-library'))).'</strong></em></p>';
		} else {

			?>

			<p></p>

			<p><em><a href="https://console.developers.google.com"><?php _e('Follow this link to your Google API Console, and there create a Client ID in the API Access section.','google-cloud-print-library');?></a> <?php _e("Select 'Web Application' as the application type. Then enter the client ID and secret below and save your settings.",'google-cloud-print-library');?></p><p><?php echo htmlspecialchars(__('You must add the following as the authorised redirect URI (under "More Options") when asked','google-cloud-print-library'));?>: <kbd><?php echo $admin_page_url.'?action=google-cloud-print-auth'; ?></kbd> <?php _e('N.B. If you install this plugin on several WordPress sites, then you might have problems in re-using your project (depending on whether Google have fixed issues at their end yet); if so, then  create a new project from your Google API console for each site.','google-cloud-print-library');?>
			</em></p>

			<p>
				<em>
				<?php echo __('After completing authentication, a list of printers will appear.', 'google-cloud-print-library').' <strong>'.__('Choose one, and then save the settings for the second time.', 'google-cloud-print-library').'</strong></p>'; ?>
				</em>
			</p>
			<?php
		}

		echo '</div>';

		echo '<form action="options.php" method="post" onsubmit="google_cloud_print_confirm_unload=null; return true;">';
		settings_fields('google_cloud_print_library_options');
		do_settings_sections('google_cloud_print_library');

		echo '<table class="form-table"><tbody>';
		echo '<td><input class="button-primary" name="Submit" type="submit" value="'.esc_attr(__('Save Changes', 'google-cloud-print-library')).'" /></td>';
		echo '</table></form>';

		echo '<h3>'.__('Test Printing', 'google-cloud-print-library').'</h3>';

		echo '<table class="form-table"><tbody>';

		echo '<tr valign="top">
				<th scope="row">'.__('Enter some text to print:', 'google-cloud-print-library').'</th>
				<td><textarea id="google_cloud_print_library_testprinttext" cols="60" rows="15"></textarea></td>
			</tr>
			<tr>
			<th>&nbsp;</th>';

		echo '<td><em>'.__('N.B. The values used for any printer-specific options configured above are those that were most recently saved (if any).', 'google-cloud-print-library').'</em><br><button id="gcpl-testprint" class="button-primary" name="Print" type="submit">'.__('Print', 'google-cloud-print-library').'</button></td>';

		$nonce = wp_create_nonce("gcpl-nonce");

		$youneed = esc_js(__('You need to enter some text to print.', 'google-cloud-print-library'));
		$printing = esc_js(__('Printing...', 'google-cloud-print-library'));
		$success = esc_js(__('The print job was sent successfully.', 'google-cloud-print-library'));
		$response = esc_js(__('Response:', 'google-cloud-print-library'));
		$notchosen = esc_js(__('No printer is yet chosen/available', 'google-cloud-print-library'));
		$refreshing = esc_js(__('refreshing...', 'google-cloud-print-library'));
		$refresh = esc_js(__('refresh', 'google-cloud-print-library'));
		$print = esc_js(__('Print', 'google-cloud-print-library'));

		$option_page = $this->option_page;

		
		
		echo <<<ENDHERE
			</tr>

		</tbody>
		</table>
ENDHERE;

		if (!empty($this->debug_output)) {
			echo '<h3>'.__('Printer debugging information', 'google-cloud-print-library').'</h3>';
			echo '<a href="#" id="google_cloud_print_library_debuginfo_show">'.__('Show...', 'google-cloud-print-library').'</a>';
			echo '<div id="google_cloud_print_library_debuginfo" style="display:none;">'.$this->debug_output.'</div>';
		}
		

		echo <<<ENDHERE
</div>

	</div>

	<script>

		var google_cloud_print_confirm_unload = null;

		window.onbeforeunload = function() { return google_cloud_print_confirm_unload; }

		jQuery(document).ready(function($) {

			$('#google_cloud_print_library_debuginfo_show').click(function(e) {
				e.preventDefault();
				$('#google_cloud_print_library_debuginfo_show').slideUp();
				$('#google_cloud_print_library_debuginfo').slideDown();
			});
		
			$('#google_cloud_print_library_options_copies').spinner({ numberFormat: "n" });

			$('#gcpl_refreshprinters').click(function(e) {
				e.preventDefault();
				$('.google_cloud_print_library_options_printer').css('opacity','0.3');
				$('#gcpl_refreshprinters').html('($refreshing)');
				$.post(ajaxurl, {
					action: 'gcpl_refresh_printers_${option_page}',
					_wpnonce: '$nonce'
				}, function(response) {
					$('#google_cloud_print_library_options_printer_container').html(response);
					$('.google_cloud_print_library_options_printer').css('opacity','1');
					$('#gcpl_refreshprinters').html('($refresh)');
				});
			});

			$('#gcpl-testprint').click(function() {
				var whichprint = $('.google_cloud_print_library_options_printer').serialize();
				var whichprint = [];
				$('.google_cloud_print_library_options_printer').each(function (index) {
					if ($(this).is(':checked')) { whichprint.push($(this).val()); }
				});
				var whatprint = $('#google_cloud_print_library_testprinttext').val();
				if (whatprint == '') {
					alert('$youneed');
					return;
				}
				if (whichprint.length > 0) {
					$('#gcpl-testprint').html('$printing');
					$.post(ajaxurl, {
						action: 'gcpl_test_print_${option_page}',
						printtext: whatprint,
						printer: whichprint,
						copies: $('#google_cloud_print_library_options_copies').val(),
						prependtext: $('#google_cloud_print_library_options_header').val(),
						_wpnonce: '$nonce'
					}, function(response) {
						try {
							resp = $.parseJSON(response);
							if (resp.result == 'ok') {
								alert('$success');
							} else {
								alert('$response '+resp.result);
							}
						} catch(err) {
							alert('$response '+response);
							console.log(response);
							console.log(err);
						}
						$('#gcpl-testprint').html('$print');
					});

				} else {
					alert("$notchosen");
				}
			});
		});
	</script>

ENDHERE;

	}
}
endif;

if (!isset($googlecloudprintlibrary_plugin) || !is_a($googlecloudprintlibrary_plugin, 'GoogleCloudPrintLibrary_Plugin'))
$googlecloudprintlibrary_plugin = new GoogleCloudPrintLibrary_Plugin($googlecloudprintlibrary_gcpl);
