=== Google Cloud Print Library ===
Contributors: DavidAnderson
Tags: google cloud print, woocommerce print orders, print orders
Requires at least: 3.5
Tested up to: 5.0
Stable tag: 0.8.12
Requires PHP: 5.3
Donate link: https://david.dw-perspective.org.uk/donate
License: MIT/GPLv2+

== Description ==

This plugin is mainly for programmers to use. It contains an options page to set up a connection to a Google account, and allows you to choose a Google Cloud Print printer from your account and to test printing to it.

The main use of this plugin is for developers of other plugins to deploy, and integrate with their plugins. For example, it has been integrated into <a title="WooCommerce - automatically print orders, invoices and delivery notes" href="https://www.simbahosting.co.uk/s3/product/woocommerce-automatic-order-printing/">this plugin that can send orders (invoices, delivery and packing notes) from a WooCommerce web shop automatically to Google Cloud Print</a>.

To set up WordPress to have access to Google Cloud Print, you will need to follow this reasonably-simple tutorial: <a href="https://www.simbahosting.co.uk/s3/support/configuring-google-drive-api-access-for-cloud-print/">https://www.simbahosting.co.uk/s3/support/configuring-google-drive-api-access-for-cloud-print/</a>

If you find it useful in your project, then please do consider a donation: <a href="https://david.dw-perspective.org.uk/donate">https://david.dw-perspective.org.uk/donate</a>

= How can a developer use it? =

Here's some example code:

`// Ensure that we can get a Google Cloud Print Library object

if (class_exists('GoogleCloudPrintLibrary_GCPL_v2')) {

	// The first parameter to print_document() is the printer ID. Use false to send to the default. You can use the get_printers() method to get a list of those available.

	$gcpl = new GoogleCloudPrintLibrary_GCPL_v2();

	$printed = $gcpl->print_document(false, get_bloginfo('name').' - test print', '<b>My HTML to print</b>');

	// Parse the results
	if (!isset($printed->success)) {
		trigger_error('Unknown response received from GoogleCloudPrintLibrary_GCPL->print_document()', E_USER_NOTICE);
	} elseif ($printed->success !== true) {
		trigger_error('GoogleCloudPrintLibrary_GCPL->print_document(): printing failed: '.$printed->message, E_USER_NOTICE);
	}

}`

Please respect the plugin's open-source MIT licence, which requires attribution.

== Screenshots ==

1. Options page

== Installation ==

Standard WordPress plugin installation:

1. Search for "Google Cloud Print Library" in the WordPress plugin installer
2. Click 'Install'

== Frequently Asked Questions ==

= What does this plugin do? =

It is a developers' plugin. It provides code to get you connected quickly and easily to Google Cloud Print. A developer can harness it rapidly from within his own plugin to despatch print jobs to a Google Cloud Print-connected printer. For example, its underlying engine is deployed in this plugin for printing automatically WooCommerce orders (great for restaurants, take-aways or anyone who always wants to print a packing slip and wants to save time): <a href="https://www.simbahosting.co.uk/s3/product/woocommerce-automatic-order-printing/">https://www.simbahosting.co.uk/s3/product/woocommerce-automatic-order-printing/</a>

= How do I, as a developer, use it? =

Please see the plugin description.

= Please will you add a new feature? =

Only upon commission. This plugin does what I need - I am sharing it with the community, but not intending to develop it further unless I personally need to. I am happy to accept patches for improvements from the community.

= What support is provided for this plugin? =

None from me. I will accept useful patches that make this plugin more useful for others - please post them in the plugin's forum. This plugin is by a developer, for developers. To understand more of how it works beyond the description here, please read the (short, simple) code.

= Got any other interesting tools? =

Please check out the very popular <a href="https://updraftplus.com">UpdraftPlus backup plugin</a>, my <a href="https://profiles.wordpress.org/DavidAnderson">profile page</a>, and <a href="https://www.simbahosting.co.uk/s3/shop/">Premium WooCommerce extensions</a>.

== Changelog ==

= 0.8.12 - 25/Jan/2019 =

* TWEAK: Update DomPDF to 0.8.3 (better PHP 7.3 compatibiity)

= 0.8.11 - 27/Jun/2018 =

* TWEAK: Display GCPL option name if no custom name exists

= 0.8.10 - 24/May/2018 =

* FIX: 0.8.9's change introduced an error that prevented printer listing. Now fixed.

= 0.8.9 - 08/May/2018 =

* TWEAK: Tweak default timeout when fetching printer list

= 0.8.8 - 01/Mar/2018 =

* TWEAK: When there is no Google OAuth token, make the error more visible

= 0.8.7 - 19/Feb/2018 =

* TWEAK: Prevent a PHP notice
* TWEAK: Mark as supported from WP 3.5+ (nothing particular stops it working on earlier versions, but if a problem is found, we're unlikely to fix it)

= 0.8.6 - 26/Jan/2018 =

* TWEAK: Allow the caller to get_printers() to indicate that the transient cache can be used

= 0.8.5 - 25/Jan/2018 =

* TWEAK: Add LibXML 2.9.7 to the list of known-buggy versions that require a work-around

= 0.8.4 - 24/Jan/2018 =

* TWEAK: Update DomPDF library to 0.8.2
* TWEAK: Add a polyfill for hex2bin, used by DomPDF, for PHP 5.3

= 0.8.3 - 06/Oct/2017 =

* TWEAK: Attempt to mitigate a parsing bug in LibXML 2.9.5

= 0.8.2 - 03/Oct/2017 =

* TWEAK: Update DomPDF library to 0.8.1

= 0.8.1 - 14/Sep/2017 =

* TWEAK: Handle an unexpected HTTP call result more elegantly

= 0.8.0 - 17/Aug/2017 =

* UPDATE: Update to DOMPDF 0.8 series
* COMPATIBILITY: PHP 5.3+ required (required by DOMPDF 0.8+)
* COMPATIBILITY: Bumped WP requirement to 3.4+ (likely to implement a "one in, one out" system with WP versions from now on)

= 0.7.6 - 24/Apr/2017 =

* FIX: 0.7.5 had a mangled plugin header, leading to unwanted output

= 0.7.5 - 22/Apr/2017 = 

* TWEAK: When an authorisation token is not accepted, make sure information on this is passed on

= 0.7.4 - 07/Mar/2017 =

* TWEAK: Expose the monochrome/colour option in the settings

= 0.7.3 - 14/Nov/2016 =

* TWEAK: Avoid a harmless PHP notice

= 0.7.2 - 20/Oct/2016 =

* TWEAK: Added google_cloud_print_ticket and google_cloud_print_post_request to allow easier developer customisation of print options

= 0.7.1 - 11/Aug/2016 =

* FEATURE: Add options in the UI for page orientation and page margins

= 0.7.0 - 27/Jul/2016 =

* FEATURE: When retrieving the list of printers, also retrieve the detailed information for each printer. Select options (currently page size and fit-to-page) are displayed in the UI (if present) and passed on when printing.
* COMPATIBILITY: Tested on the forthcoming WP 4.6

= 0.6.0 - 06/Jul/2016 =

* FEATURE: Add more flexibility to the 'document' parameter, allowing data of arbitrary MIME types to be sent to Google Cloud Print
* TWEAK: Update bundled DOMPDF library to version 0.6.2 (this is a security update, if your application allows arbitrary user-supplied content)

= 0.5.3 - 18/Apr/2016 =

* TWEAK: Pass the '&' parameter explicitly to http_build_query(), in case the PHP setup has an unhelpful configuration

= 0.5.2 - 28/Mar/2016 =
* TWEAK: Add a filter to allow cancelling of print jobs before sending

= 0.5.1 - 18/Mar/2016 =
* TWEAK: Give a clearer error when no printers have been set up

= 0.5.0 - 28/Sep/2015 =
* TWEAK: Support the passing of more options, including paper size

= 0.4.3 - 20/Aug/2015 =
* TWEAK: Remove a CSS class from the admin notice that caused some themes to not show it.

= 0.4.2 - 01/Aug/2015 =
* Tested/compatible with WordPress 4.3
* Bumped internal version number that should have been bumped last time

= 0.4.1 - 24/Jun/2015 =
* FEATURE: Now allows sending to multiple printers (the UI now provides check-boxes, instead of a drop-down)

= 0.4.0 - 30/May/2015 =
* TWEAK: Now uses OAuth2 for authentication with Google (since Google have removed ClientLogin). As a result, the settings page now has entries for a client ID + secret (and a link to instructions), and the previous settings for your Google username are now gone.

= 0.3.2 - 30/Jan/2015 =
* TWEAK: Add a message with instructions for users who fall foul of Google's recent changes to the ClientLogin method

= 0.3.1 - 05/Nov/2014 =
* FIX: Fix bug that meant you had to enter your password twice (first time always failed)
* TWEAK: Stop the page jumping when you click on the 'refresh' link for the list of printers

= 0.3.0 - 04/Nov/2014 =
* TWEAK: Update to DomPDF 0.6
* TWEAK: Minor changes to code internals

= 0.2.2 - 01/Nov/2014 =
* TWEAK: Minor changes to code internals

= 0.2.1 - 22/Oct/2014 =
* TWEAK: Code re-factored and brought up to date with best practices. Also now uses wp_remote_post() instead of Curl directly.
* FEATURE: Internationalised (i.e. ready for translation)

= 0.1.6 - 07/Sep/2013 =
* FEATURE: Allow printing of multiple copies (compatible with all printers - job is sent multiple times)

= 0.1.5 - 29/July/2013 =
* FIX: display saved printer preference on options page

= 0.1.4 - 10/May/2013 =
* First version

== License ==

Copyright 2013- David Anderson / Simba Hosting

MIT License:

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

The authors of the DomPDF library (https://github.com/dompdf/dompdf) are gratefully acknowledged. The DomPDF library is used under the Lesser GNU Public Licence (LGPL, version 2.1).

== Upgrade Notice ==
0.8.12 : Update DomPDF to 0.8.3 (better PHP 7.3 compatibiity)
