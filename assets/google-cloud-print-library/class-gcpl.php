<?php

use Dompdf\Dompdf;
use Dompdf\Options;

if (!defined('ABSPATH')) die('No direct access allowed');

if (!class_exists('GoogleCloudPrintLibrary_GCPL')):
define('GOOGLECLOUDPRINTLIBRARY_VERSION', '0.8.12');
class GoogleCloudPrintLibrary_GCPL {

	public $version;

	public function __construct() {
		$this->version = GOOGLECLOUDPRINTLIBRARY_VERSION;
	}

	/*
		(string)$title - this is sent to GCP as the designated title for the print job (it is not included in the output)
	
		$document can be:
		- A string, which is treated as HTML, and turned into a PDF
		- An array with key 'pdf-file', which indicates a path to a PDF file to use
		- An array with key 'pdf-raw', which is a byte-stream for a PDF
		- An array with keys 'data-raw' and 'mime-type', which indicates data to send to Google Cloud Print with the specified MIME type (e.g. text/plain)
	*/
	public function print_document($printer_id = false, $title, $document, $prepend = false, $copies = false, $options = array()) {

		require_once __DIR__.'/vendor/autoload.php';
	
		// $options should be populated with 'token' (token), 'printer' (if $printer_id is false) and 'header' (if $prepend is false)
		if (empty($options)) $options = apply_filters('google_cloud_print_options', array());

		$copies = apply_filters('google_cloud_print_copies', $copies);

		if (0 == $copies) {
			$x = new stdClass;
			$x->success = false;
			$x->message = 'No copies to print';
			return $x;
		}

		$copies = max(intval($copies), 1);

		$token = empty($options['token']) ? '' : $options['token'];

		if (empty($printer_id) || $printer_id == array('') || empty($token)) {
			// This could be an array
			$printer_id = $options['printer'];
			if (empty($printer_id) || $printer_id == array('')) {
				$x = new stdClass;
				$x->success = false;
				$x->message = "Error: no printer has been configured";
				return $x;
			}
		}

		# Get capabilities of printer
// 		$can_fit_to_page = false;
// 		$get_printer_url = "https://www.google.com/cloudprint/printer"; #?printerid=".urlencode($printer_id)."&use_cdd=true&output=json";
// 		$get_printer_params = array('use_cdd' => 'true', 'printerid' => $printer_id);
// 		$get_printer_result = $this->process_request("https://www.google.com/cloudprint/printer", $get_printer_params, $token);
// 		if (is_string($r)) {
// 			$r = json_decode($r);
// 			if (is_object($r) && isset($r->printers[0]->capabilities->printer->fit_to_page->option)) {
// 				$options = $r->printers[0]->capabilities->printer->fit_to_page->option;
// 				foreach ($options as $num => $opt) {
// 					if (is_object($opt) && isset($opt->type) && $opt->type == 'FIT_TO_PAGE') $can_fit_to_page = $num;
// 				}
// 			}
// 		}

		# http://code.google.com/p/dompdf/wiki/Usage#Usage

		if ($prepend === false) $prepend = (isset($options['header'])) ? $options['header'] : '';

		$mime_type = 'application/pdf';
		
		if (is_string($document)) {

			$dompdf_options = new Options();
			$dompdf_options->setdefaultFont('dejavu sans');
// 			$dompdf_options->setTempDir();
// 			$dompdf_options->setLogOutputFile();
// 			$dompdf_options->setFontDir();
// 			$dompdf_options->setFontCache();
			$dompdf_options->setIsRemoteEnabled(true);
			$dompdf_options->setIsFontSubsettingEnabled(true);
			
			$dompdf_options = apply_filters('google_cloud_print_dompdf_options', $dompdf_options, $options, $this);
		
			$document_with_prepend = $prepend.$document;
			
			// See https://github.com/dompdf/dompdf/issues/1494. At this point, we don't know if the issue will persist after 2.9.5, and for how long.
			if (defined('LIBXML_DOTTED_VERSION') && (LIBXML_DOTTED_VERSION == '2.9.5' || LIBXML_DOTTED_VERSION == '2.9.6' || LIBXML_DOTTED_VERSION == '2.9.7')) {
				// N.B. The bug is triggered by whitespace, not just line-feeds; but this is an attempt to reduce the risk, not eliminate it.
				$document_with_prepend = preg_replace('/>\n</g', '><', $document_with_prepend);
			}
		
			if (false === stripos($prepend, '<html>') && false === stripos($document, '<html>')) {
				$html = '<html><body>'.$document_with_prepend.'</body></html>';
			} else {
				$html = $document_with_prepend;
			}

			try {
			
				$dompdf = new Dompdf($dompdf_options);

				if (isset($options['paper_size'])) {
					$orientation = isset($options['width']) ? $options['paper_orientation'] : 'portrait';
					$dompdf->setPaper($options['paper_size'], $orientation);
				} elseif (isset($options['paper_width']) && isset($options['paper_height'])) {
					$paper_size = array(0, 0, $options['paper_width'], $options['paper_height']);
					// The 'orientation' parameter is really just a proxy for "reverse the width/height if landscape"
					$dompdf->setPaper($paper_size);
				}

				if (!defined('WP_DEBUG') || !WP_DEBUG) $dompdf->set_option('log_output_file', false);
				$dompdf->load_html($html);
				$dompdf->render();
				# Send to browser
				// $dompdf->stream("sample.pdf");
				# Save to file
				// file_put_contents('sample.pdf', $dompdf->output());
				$document_data = $dompdf->output();
			} catch (Exception $e) {
				$x = new stdClass;
				$x->success = false;
				$x->message = 'DOMPDF error ('.get_class($e).', '.$e->getCode().'): '.$e->getMessage().' (line: '.$e->getLine().', file: '.$e->getFile().')';
				return $x;
			}
		} elseif (is_array($document) && isset($document['pdf-raw'])) {
			$document_data = $document['pdf-raw'];
		} elseif (is_array($document) && isset($document['pdf-file'])) {
			$document_data = file_get_contents($document['pdf-file']);
		} elseif (is_array($document) && isset($document['data-raw']) && isset($document['mime-type'])) {
			$document_data = $document['data-raw'];
			$mime_type = $document['mime-type'];
		}

		$printer_ids = is_array($printer_id) ? array_values($printer_id) : array($printer_id);

		sort($printer_ids);

		foreach ($printer_ids as $k => $pid) {

			if (!$pid || !apply_filters('google_cloud_print_proceed', true, $pid, $title, $document, $prepend, $copies, $options)) continue;

			$url = "https://www.google.com/cloudprint/submit?printerid=".urlencode($pid)."&output=json";

			$ticket = null;
			
			if (isset($options['printer_options'][$pid])) {

				// https://developers.google.com/cloud-print/docs/cdd#cjt
				$ticket = array('version' => '1.0', 'print' => array());

				$printer_options = $options['printer_options'][$pid];
				
				// Media size options
				if (isset($printer_options['media_size']) && null !== ($media_size_options = json_decode($printer_options['media_size'], true))) {
				
					$ticket['print']['media_size'] = array(
						"width_microns" => $media_size_options['width_microns'],
						"height_microns" => $media_size_options['height_microns'],
						"is_continuous_feed" => empty($media_size_options['is_continuous_feed']) ? false : true
					);
				}
				
				// Colour options
				if (isset($printer_options['color']) && null !== ($color_options = json_decode($printer_options['color'], true))) {
				
					$color_types = array(
						'STANDARD_MONOCHROME' => 1,
						'STANDARD_COLOR' => 0,
						'AUTO' => 4,
					);
				
					$color_type = isset($color_types[$color_options['type']]) ? $color_types[$color_options['type']] : $color_types['STANDARD_MONOCHROME'];
				
					$ticket['print']['color'] = array(
						'type' => $color_type
					);
					
				}
				
				// Fit to page options
				if (isset($printer_options['fit_to_page']) && null !== ($fit_to_page_options = json_decode($printer_options['fit_to_page'], true))) {

					$fit_to_page_types = array(
						'NO_FITTING' => 0,
						'FIT_TO_PAGE' => 1,
						'GROW_TO_PAGE' => 2,
						'SHRINK_TO_PAGE' => 3,
						'FILL_PAGE' => 4
					);
					
					$fit_to_page_type = isset($fit_to_page_types[$fit_to_page_options['type']]) ? $fit_to_page_types[$fit_to_page_options['type']] : $fit_to_page_types['NO_FITTING'];
				
					$ticket['print']['fit_to_page'] = array(
						'type' => $fit_to_page_type
					);
				}
				
				// Fit to page options
				if (isset($printer_options['page_orientation']) && null !== ($page_orientation_options = json_decode($printer_options['page_orientation'], true))) {

					$page_orientation_types = array(
						'PORTRAIT' => 0,
						'LANDSCAPE' => 1,
						'AUTO' => 2,
					);
					
					$page_orientation_type = isset($page_orientation_types[$page_orientation_options['type']]) ? $page_orientation_types[$page_orientation_options['type']] : $page_orientation_types['AUTO'];
				
					$ticket['print']['page_orientation'] = array(
						'type' => $page_orientation_type
					);
				}
				
				if (isset($printer_options['printer_margin'])) {

					$printer_margin_options = $printer_options['printer_margin'];
				
					// The option is saved in mm; GCP uses microns
					$ticket['print']['margins'] = array(
						'top_microns' => isset($printer_margin_options['top']) ? 1000*$printer_margin_options['top'] : 20000,
						'right_microns' => isset($printer_margin_options['right']) ? 1000*$printer_margin_options['right'] : 20000,
						'bottom_microns' => isset($printer_margin_options['bottom']) ? 1000*$printer_margin_options['bottom'] : 20000,
						'left_microns' => isset($printer_margin_options['left']) ? 1000*$printer_margin_options['left'] : 20000,
					);
				}

				if (empty($ticket['print'])) {
					unset($ticket);
				} else {
					$ticket['print']['vendor_ticket_item'] = array();
				}

			}
			
			$ticket = apply_filters('google_cloud_print_ticket', $ticket);

			$post = array(
				"printerid" => $pid,
	// 			"capabilities" => "",
				"contentType" => "dataUrl",
				"title" => $title,
				"content" => 'data:'.$mime_type.';base64,'. base64_encode($document_data)
			);
			
			if (!empty($ticket)) $post['ticket'] = json_encode($ticket);

			$post = apply_filters('google_cloud_print_post_request', $post);
			
			for ($i=1; $i<=$copies; $i++) {
				$ret = $this->process_request($url, $post, $options);
				if ($i == $copies && is_string($ret) && $k == count($printer_ids)-1) return json_decode($ret);
			}
		}

		$x = new stdClass;
		$x->success = false;
		$x->message = $ret;
		return $x;

	}

	/**
	 * Get a list of printers
	 *
	 */
	public function get_printers($force_allow_cache = false) {

		if ($force_allow_cache || !defined('DOING_AJAX') || !DOING_AJAX) {
			$printers = get_transient('wc_sa_google_cloud_print_library_printers');
			if (is_array($printers)) return $printers;
		}

		// Wanted key: access token
		$options = apply_filters('google_cloud_print_options', array());

		// This should only be set if authenticated
		if (isset($options['token'])) {

			$post = array();

			$printers = $this->process_request('https://www.google.com/cloudprint/interface/search', $post, $options);

			if (is_wp_error($printers)) return $printers;

			if (is_string($printers)) $printers = json_decode($printers);

			if (is_object($printers) && isset($printers->success) && $printers->success == true && isset($printers->printers) && is_array($printers->printers)) {

				foreach ($printers->printers as $index => $printer) {
				
					$get_printer_result = $this->process_request(
						'https://www.google.com/cloudprint/printer',
						array('use_cdd' => 'true', 'printerid' => $printer->id),
						$options
					);
					
					if (false !== $get_printer_result && null !== ($printer_result = json_decode($get_printer_result))) {
						if (isset($printer_result->success) && true == $printer_result->success && isset($printer_result->printers) && is_array($printer_result->printers)) {

							$printer = $printer_result->printers[0];
						
// 							$hashed_id = md5($printer->id);
// 							set_transient('gcpl_popts_'.$hashed_id, $printer_result, 86400);
							
							$printers->printers[$index]->_gcpl_printer_opts = $printer;
						}
					}
					
				
				}
			
				if (false !== $get_printer_result) {
					set_transient('wc_sa_google_cloud_print_library_printers', $printers->printers, 86400);
				}

				return $printers->printers;

			}

		}

		return array();

	}

	// Old deprecated + removed ClientLogin method - https://developers.google.com/identity/protocols/AuthForInstalledApps
// 	public function authorize($clientid, $password) {
// 
// 		$post = array(
// 			"accountType" => "HOSTED_OR_GOOGLE",
// 			"Email" => $username,
// 			"Passwd" => $password,
// 			"service" => "cloudprint",
// 			"source" => "google-cloud-print-library-for-wordpress"
// 		);
// 
// 		$resp = $this->process_request("https://www.google.com/accounts/ClientLogin", $post);
// 
// 		if (is_wp_error($resp)) return $resp;
// 
// 		if (preg_match("/Error=([a-z0-9_\-]+)/i", $resp, $ematches)) return new WP_Error('bad_auth','Authentication failed: Google replied with: '.$ematches[1]);
// 
// 		preg_match("/Auth=([a-z0-9_\-]+)/i", $resp, $matches);
// 
// 		if (isset($matches[1])) {
// 			return $matches[1];
// 		} else {
// 			return false;
// 		}
// 
// 	}

	// Get a Google account access token using the refresh token
	// Expected keys in options: clientid, clientsecret, (refresh)token
	private function access_token($options) {

		$refresh_token = $options['token'];

		$query_body = array(
			'refresh_token' => $refresh_token,
			'client_id' => $options['clientid'],
			'client_secret' => $options['clientsecret'],
			'grant_type' => 'refresh_token'
		);

		$result = wp_remote_post('https://accounts.google.com/o/oauth2/token',
			array(
				'timeout' => 15,
				'method' => 'POST',
				'body' => $query_body
			)
		);

		if (is_wp_error($result)) {
			return $result;
		} else {
			$json_values = json_decode( wp_remote_retrieve_body($result), true );
			
			if ( isset( $json_values['access_token'] ) ) {
// 				error_log("Google Drive: successfully obtained access token");
				return $json_values['access_token'];
			} else {
// 				error_log("Google Drive error when requesting access token: response does not contain access_token");

				if (!empty($json_values['error']) && !empty($json_values['error_description'])) {
					return new WP_Error($json_values['error'], $json_values['error_description']);
				}

				return false;
			}
		}
	}

	public function process_request($url, $post_fields, $options = array(), $referer = '' ) {  

		$ret = "";

		$wp_post_opts = array(
			'user-agent' => "Google Cloud Print Library For WordPress/".$this->version,
			'headers' => array(
				'X-CloudPrint-Proxy' => "google-cloud-print-library-for-wordpress",
				'Referer' => $referer
			),
			'sslverify' => true,
			'redirection' => 5,
			'body' => $post_fields,
			'timeout' => 25
		);

		if (!empty($options['username']) && empty($options['clientid'])) {
			// Legacy/deprecated - tokens from the now-removed ClientLogin API
			$wp_post_opts['headers']['Authorization'] = "GoogleLogin auth=$token";
		} else {
			$access_token = $this->access_token($options);
			if (is_wp_error($access_token) || empty($access_token)) return $access_token;
			$wp_post_opts['headers']['Authorization'] = "Bearer $access_token";
		}

		$wp_post_opts = apply_filters('google_cloud_print_process_request_options', $wp_post_opts);
		
		$post = wp_remote_post($url, $wp_post_opts);

		if (is_wp_error($post)) {
 			error_log('POST error: '.$post->get_error_code().': '.$post->get_error_message());
			return $post;
		}

		if (!is_array($post['response']) || !isset($post['response']['code'])) {
 			error_log('POST error: Unexpected response: '.serialize($post));
			return false;
		}

		if ($post['response']['code'] >=400 && $post['response']['code']<500) {

			$extra = '';

		// This pertains only to the old ClientLogin authentication mechanism during its deprecation stage (it is now entirely gone)
// 			if (403 == $post['response']['code'] && !empty($post['body']) && false !== strpos($post['body'], 'Info=WebLoginRequired') && preg_match('/Url=(\S+)/', $post['body'], $umatch)) {
// 				$extra = 'Due to recent Google API changes, you will need to <a href="https://www.google.com/settings/security/lesssecureapps">go to your Google account, and enable &quot;less secure&quot; apps</a>. (N.B. This app does not store your password after using it once, so is not actually insecure). Or, <a href="https://www.google.com/landing/2step/">enable two-factor authentication on your Google account</a> and then <a href="http://support.google.com/accounts/bin/answer.py?hl=en&answer=185833">obtain an application-specific password</a>.';
// 			}

  			error_log('POST error: Unexpected response (code '.$post['response']['code'].'): '.serialize($post));
			return new WP_Error('http_badauth', $extra."Authentication failed (".$post['response']['code']."): ".$post['body']);
		}

		if ($post['response']['code'] >=400) {
 			error_log('POST error: Unexpected response (code '.$post['response']['code'].'): '.serialize($post));
			return new WP_Error('http_error', 'POST error: Unexpected response (code '.$post['response']['code'].'): '.serialize($post));
		}

		return $post['body'];

	}

}
endif;

if (!class_exists('GoogleCloudPrintLibrary_GCPL_v2')):
class GoogleCloudPrintLibrary_GCPL_v2 extends GoogleCloudPrintLibrary_GCPL {
}
endif;
