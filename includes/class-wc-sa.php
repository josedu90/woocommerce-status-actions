<?php

if (!defined('ABSPATH')) exit;

class WC_SA
{

    /**
     * The single instance of WC_SA.
     * @var     object
     * @access  private
     * @since   1.0.0
     */
    private static $_instance = null;

    /**
     * Settings class object
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $settings = null;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_version;

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin uploads directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $uploads_dir;

    /**
     * The plugin uploads URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $uploads_url;

    /**
     * The plugin templates directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $templates_dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $script_suffix;

    /**
     * Default order statuses
     * @var     array
     * @access  public
     * @since   1.0.0
     */
    public $default_statuses;

    /**
     * Default order statuses
     * @var     array
     * @access  public
     * @since   1.0.0
     */
    public $color_statuses;

    /**
     * Default order statuses
     * @var     array
     * @access  public
     * @since   1.0.0
     */
    public $default_editing;

    public $default_reports;

    public $default_icons;

    public $default_action_icons;

    public $default_pay;

    public $default_bulk_action;

    public $default_widget;

    public $default_customer_cancel;

    public $default_customer_reviews;


    private $default_wc_order_statuses = array(
        'wc-pending',
        'wc-processing',
        'wc-on-hold',
        'wc-completed',
        'wc-refunded',
        'wc-failed'
    );

    /**
     * Constructor function.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function __construct($file = '', $version = '1.0.0')
    {

        #Disable the compression of pages.
        ini_set('zlib.output_compression', 'off');

        $this->default_statuses = array(
            'wc-pending' => _x('Pending Payment', 'Order status', 'woocommerce'),
            'wc-processing' => _x('Processing', 'Order status', 'woocommerce'),
            'wc-on-hold' => _x('On Hold', 'Order status', 'woocommerce'),
            'wc-completed' => _x('Completed', 'Order status', 'woocommerce'),
            'wc-cancelled' => _x('Cancelled', 'Order status', 'woocommerce'),
            'wc-refunded' => _x('Refunded', 'Order status', 'woocommerce'),
            'wc-failed' => _x('Failed', 'Order status', 'woocommerce'),
        );
        $this->color_statuses = array(
            'wc-pending' => '#828282',
            'wc-processing' => '#559f55',
            'wc-on-hold' => '#eda411',
            'wc-completed' => '#386b98',
            'wc-cancelled' => '#828282',
            'wc-refunded' => '#828282',
            'wc-failed' => '#c62828',
        );

        $this->default_editing = array(
            'wc-pending' => 'yes',
            'wc-on-hold' => 'yes'
        );

        $this->default_pay = array(
            'pending' => 'yes',
            'failed' => 'yes'
        );

        $this->default_bulk_action = array(
            'pending' => 'yes',
            'failed'   => 'yes',
            'refunded'  => 'yes',
            'cancelled'   => 'yes'
        );

        $this->default_widget = array(
            'processing' => 'yes',
            'on-hold' => 'yes',
        );

        $this->default_customer_cancel = array(
            'pending' => 'yes',
            'failed' => 'yes',
        );

        $this->default_customer_reviews = array(
            'completed' => 'yes',
            'processing' => 'yes'
        );

        $this->default_reports = array(
            'processing' => 'yes',
            'on-hold' => 'yes',
            'completed' => 'yes',
            'refunded' => 'yes'
        );

        $this->default_icons = array(
            'pending' => 'f6ea',
            'processing' => 'f6e9',
            'on-hold' => 'f70b',
            'completed' => 'f6ed',
            'cancelled' => 'f6eb',
            'refunded' => 'f6ec',
            'failed' => 'f6ee',
        );

        $this->default_action_icons = array(
            'pending' => 'ec68',
            'processing' => 'ecd8',
            'on-hold' => 'ed04',
            'completed' => 'f6ef',
            'cancelled' => 'eb80',
            'refunded' => 'ed52',
            'failed' => 'ed3f',
        );

        $this->_version = $version;
        $this->_token = 'wc_sa';

        // Load plugin environment variables
        $upload_dir = wp_upload_dir();

        $this->uploads_dir = trailingslashit($upload_dir['basedir']) . 'wc_sa_uploads';
        $this->uploads_url = esc_url(trailingslashit($upload_dir['baseurl']) . 'wc_sa_uploads');
        if (is_ssl()) {
            $this->uploads_url = str_replace('http://', 'https://', $this->uploads_url);
        }

        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->templates_dir = trailingslashit($this->dir) . 'templates';
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));

        $this->script_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '';

        register_activation_hook($this->file, array($this, 'install'));

        // Load frontend JS & CSS
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 10);

        // Load admin JS & CSS
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_styles'), 10, 1);

        $this->define_constants();
        $this->includes();
        $this->init_hooks();

        add_action('init', array($this, 'init'));

        do_action('wc_sa_init');

        // Handle localisation
        $this->load_plugin_textdomain();
        add_action('init', array($this, 'load_localisation'), 0);
        //Google printer actions
        add_action('woocommerce_after_register_post_type', array($this, 'google_printer_actions'), 0);
    } // End __construct ()

    public function define_constants()
    {
        define('WC_CUSTOM_STATUS_PLUGIN_PATH', plugin_dir_url($this->file));
        define('WC_SA_PLUGIN_BASENAME', plugin_basename($this->file));
        define('WC_SA_FILE', $this->file);
        define('WC_SA_DIR', $this->dir);
        define('WC_SA_VERSION', $this->_version);
        define('WC_SA_TOKEN', $this->_token);
    }

    private function includes()
    {
        include_once('functions.php');
        include_once('class-wc-sa-ajax.php');
        include_once('class-wc-sa-post-types.php');
        include_once('class-wc-sa-status.php');
        include_once('class-wc-sa-emails.php');
        include_once('class-wc-sa-order.php');
        include_once('class-wc-sa-bulk.php');
        include_once('class-wc-sa-install.php');
        include_once($this->assets_dir . '/google-cloud-print-library/google-cloud-print-library.php');
        include_once($this->assets_dir . '/google-cloud-print-library/class-gcpl.php');

        // Load API for generic admin functions
        if (is_admin()) {
            include_once('admin/class-wc-sa-admin-post-types.php');
            include_once('admin/meta-boxes/class-wc-sa-meta-box-status-data.php');
            include_once('admin/class-wc-sa-delete.php');
        } else {
            include_once('class-wc-sa-frontend.php');
        }
    }

    public function init()
    {
        $this->register_post_status();
        $this->add_options();
    }

    public function init_hooks()
    {
        add_action('admin_head', array($this, 'message_tc_button'));
        add_filter('wc_order_statuses', array($this, 'add_order_statuses'), 10, 1);
        add_filter('woocommerce_get_settings_pages', array($this, 'add_settings_pages'), 10, 1);
        add_filter('wc_sa_run_automatic_trigger', array($this, 'automatic_trigger'), 10, 3);
        add_filter('woocommerce_reports_order_statuses', array($this, 'reports_order_statuses'), 10, 1);
        add_filter('woocommerce_after_dashboard_status_widget', array($this, 'dashboard_status_widget'), 10, 1);
        add_filter('views_edit-shop_order', array($this, 'remove_empty_order_statuses'));
        add_action('admin_notices', array($this, 'sa_update_notice'));
        add_action('wp_insert_post_data', array($this, 'check_sa_status_permission'), 10, 2);
        if (is_array(get_option('wc_sa_update_orders_status_error_message'))) {
            add_action('admin_notices', array($this, 'change_status_message_denied_notice'), 10);
        }
        add_filter('woocommerce_admin_order_actions', array($this, 'wc_sa_admin_order_actions'), 99 ,2);
        //add_action('query', array($this, 'add_wc_statuses'));
    }

    public function remove_empty_order_statuses($statuses)
    {
        foreach ($statuses as $key => $value) {
            if (!$value) {
                unset($statuses[$key]);
            }
        }
        return $statuses;
    }

    public function message_tc_button()
    {
        global $typenow;
        // check user permissions
        if (!current_user_can('manage_woocommerce')) {
            return;
        }
        $screen = get_current_screen();

        if ($screen->id != 'wc_custom_statuses')
            return;
        // check if WYSIWYG is enabled
        if (get_user_option('rich_editing') == 'true') {
            add_filter("mce_external_plugins", array($this, "message_add_tinymce_plugin"));
            add_filter('mce_buttons', array($this, 'message_register_tc_button'));
        }
    }

    public function message_add_tinymce_plugin($plugin_array)
    {
        $plugin_array['wc_sa_tc_button'] = esc_url($this->assets_url) . 'js/tc-button.js';
        return $plugin_array;
    }

    public function message_register_tc_button($buttons)
    {
        array_push($buttons, "wc_sa_tc_button");
        return $buttons;
    }

    public function add_options()
    {
        $trigger_options = array(
            'weekdays' => array(
                1 => 'on',
                2 => 'on',
                3 => 'on',
                4 => 'on',
                5 => 'on',
                6 => 'on',
                7 => 'on',
            ),
            'days_off' => ''
        );
        update_option('trigger_options', $trigger_options);
    }

    public function register_post_status()
    {
        $statuses = wc_sa_get_statuses();

        foreach ($statuses as $status) {

            register_post_status('wc-' . $status->label, array(
                'label' => $status->title,
                'public' => true,
                'exclude_from_search' => false,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop($status->title . ' <span class="count"> (%s)</span>', $status->title . ' <span class="count"> (%s)</span>', 'woocommerce_status_actions')
            ));
        }

    }

    public function add_settings_pages($settings)
    {
        $settings[] = include('admin/class-wc-sa-admin-settings.php');
        return $settings;
    }

    public function add_order_statuses($statuses)
    {
        $sa_status = wc_sa_get_statusesList();

        if(count($sa_status)){
            $statuses = array_merge($statuses, $sa_status);
        }

        return $statuses;
    }

    public function automatic_trigger($order_id, $new_status, $old_status)
    {
        $order = wc_get_order($order_id);
        $all_st = wc_get_order_statuses();

        if (isset($all_st[$new_status]) && $order->has_status($old_status)) {
            $new_status = substr($new_status, 3);
            $note = apply_filters('bulk_handler_custom_action_note', '', $new_status, $order);
            $note = apply_filters('automatic_trigger_handler_custom_action_note', $note, $new_status, $order);
            $order->update_status($new_status, $note);
        }
    }

    public function reports_order_statuses($order_status)
    {
        if (!is_array($order_status))
            return $order_status;

        if (in_array('refunded', $order_status) && sizeof($order_status) == 1)
            return $order_status;

        return wc_sa_get_display_in_reports_statuses();
    }

    public function dashboard_status_widget($reports)
    {
        $counts = array();
        foreach (wc_get_order_types('order-count') as $type) {
            $_counts = (array)wp_count_posts($type);
            if (empty($counts)) {
                $counts = $_counts;
            } else {
                foreach ($_counts as $key => $value) {
                    if (isset($counts[$key])) {
                        $counts[$key] += (int)$value;
                    } else {
                        $counts[$key] = (int)$value;
                    }
                }
            }
        }
        $statuses = wc_sa_get_statuses(true);
        foreach ($statuses as $st_id => $st) {
            if(in_array($st->label, array('processing', 'on-hold'))){
                continue;
            }
            $show = get_post_meta($st_id, '_dashboard_widget', true);
            if (isset($counts['wc-' . $st->label]) && $show === 'yes') {
                $count = $counts['wc-' . $st->label];
                ?>
                <li class="<?php echo $st->label; ?>-orders dashboard_status">
                    <a href="<?php echo admin_url('edit.php?post_status=wc-' . $st->label . '&post_type=shop_order'); ?>">
                        <?php printf(_n("<strong>%s order</strong> %s", "<strong>%s orders</strong> %s", $count, 'woocommerce_status_actions'), $count, strtolower($st->title)); ?>
                    </a>
                </li>
                <?php
            }
        }
    }

    /**
     * Load frontend CSS.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function enqueue_styles()
    {
        wp_register_style($this->_token . '-frontend', esc_url($this->assets_url) . 'css/frontend.css', array(), $this->_version);

        wp_enqueue_style($this->_token . '-font-icons', esc_url($this->assets_url) . 'css/font-icons.css', array());
        wp_enqueue_style($this->_token . '-frontend');
    } // End enqueue_styles ()

    /**
     * Load frontend Javascript.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function enqueue_scripts()
    {

        wp_register_script($this->_token . '-frontend', esc_url($this->assets_url) . 'js/frontend' . $this->script_suffix . '.js', array('jquery'), $this->_version);
        wp_register_script('flot', esc_url($this->assets_url) . 'plugins/Flot/jquery.flot.min.js', array('jquery'), $this->_version);
        wp_register_script('flot-pie', esc_url($this->assets_url) . 'plugins/Flot/jquery.flot.pie.min.js', array('jquery'), $this->_version);

        wp_enqueue_script('flot');
        wp_enqueue_script('flot-pie');
        wp_enqueue_script($this->_token . '-frontend');

        $options = array(
            'i18_prompt_cancel' => __('Are you sure you want to cancel this order?', 'woocommerce_status_actions'),
            'i18_prompt_change' => __('Are you sure you want to change the status of this order?', 'woocommerce_status_actions'),
        );
        wp_localize_script($this->_token . '-frontend', 'wc_sa_opt', $options);
    } // End enqueue_scripts ()

    /**
     * Load admin CSS.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function admin_enqueue_styles($hook = '')
    {

        $screen = get_current_screen();
        $allowed_screens = wc_sa_get_allowed_screens();
        if (in_array($screen->id, array('edit-shop_order'))) {
            wp_register_style($this->_token . '-admin', esc_url($this->assets_url) . 'css/admin.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-admin');
        }

        if ($screen->id == 'woocommerce_page_wc_bulk_change_status') {
            wp_enqueue_style($this->_token . '-bulk_change_status', esc_url($this->assets_url) . 'css/bulk_change_status.css', array('woocommerce_admin_styles'), $this->_version);
        }

        if (in_array($screen->id, array('edit-wc_custom_statuses', 'wc_custom_statuses'))) {
            wp_enqueue_style('fonticonpicker_styles', esc_url($this->assets_url) . 'css/fontpicker/jquery.fonticonpicker.min.css');
            wp_enqueue_style($this->_token . '-admin-metabox-statuses', esc_url($this->assets_url) . 'css/metabox-statuses.css', array(), $this->_version);
        }

        if ($screen->id == 'woocommerce_page_wc-settings') {
            wp_enqueue_style('wc_sa_settings_style', esc_url($this->assets_url) . 'css/settings.css');
            wp_enqueue_style($this->_token . '-font-icons', esc_url($this->assets_url) . 'css/font-icons.css', array());
        }

        //register_post_type
        if (in_array($screen->id, $allowed_screens)) {

            $f_version = $this->_version;
            $file_path = $this->assets_dir . '/css/font-icons.css';
            if (file_exists($file_path)) {
                $f_version = filemtime($file_path);
            }
            wp_enqueue_style($this->_token . '-font-icons', esc_url($this->assets_url) . 'css/font-icons.css', array(), $f_version);
        }

        add_action('admin_head', 'WC_SA_Meta_Box_Status_Data::generate_styles');

    } // End admin_enqueue_styles ()

    /**
     * Load admin Javascript.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function admin_enqueue_scripts($hook = '')
    {

        wp_register_script('jquery-iconpicker', esc_url($this->assets_url) . 'js/jquery.fonticonpicker.js');
        wp_register_script($this->_token . '_font_icons', esc_url($this->assets_url) . 'js/font-icons.js');

        $screen = get_current_screen();
        if (in_array($screen->id, array('wc_custom_statuses', 'edit-wc_custom_statuses'))) {
            $depth = array(
                'jquery', 'jquery-ui-sortable', 'serializejson',
                $this->_token . '_font_icons',
                'jquery-iconpicker',
                'jquery-tiptip',
                'wp-color-picker',
                'wc-admin-meta-boxes',
            );
            wp_enqueue_script($this->_token . '-admin-meta-boxes', esc_url($this->assets_url) . 'js/meta-boxes' . $this->script_suffix . '.js', $depth, $this->_version);


            $editor_btns = get_option('wc_fields_additional');
            $acf_btns = wc_sa_get_acf_editor_btns();
            $error_validation = array(
                'name' => __('<b>Status Name</b> is a required field.', 'woocommerce_status_actions'),
                'label' => __('<b>Status Label</b> is a required field.', 'woocommerce_status_actions'),
                'fromname' => __('<b>"From" Name</b> is a required field.', 'woocommerce_status_actions'),
                'fromemail' => __('<b>From" Email Address</b> is a required field.', 'woocommerce_status_actions'),
                'email' => __('<b>Email Subject</b> is a required field.', 'woocommerce_status_actions'),
                'emailhead' => __('<b>Email Heading</b> is a required field.', 'woocommerce_status_actions'),
                'visibility_rule' => __('<b>Visibility Rule</b> is a required field.', 'woocommerce_status_actions'),
                'buttonlabel' => __('<b>Button Label</b> is a required field.', 'woocommerce_status_actions'),
                'triggeredstatus' => __('<b>Triggered Status</b> is a required field.', 'woocommerce_status_actions'),
                'timeperiod' => __('<b>Time Period</b> is a required field.', 'woocommerce_status_actions')
            );
            $i18n = array(
                'go_back' => __('Go Back', 'woocommerce_status_actions'),
                'delete_status' => __('Delete status', 'woocommerce_status_actions'),
                'order_status_sctions' => __('Order Status Actions', 'woocommerce_status_actions'),
                'update' => __('Update Status', 'woocommerce_status_actions'),
                'save' => __('Save Status', 'woocommerce_status_actions'),
            );

            wp_localize_script($this->_token . '-admin-meta-boxes', 'wc_sa_error_validation', $error_validation);
            wp_localize_script($this->_token . '-admin-meta-boxes', 'wc_sa_editor_btns', $editor_btns);
            wp_localize_script($this->_token . '-admin-meta-boxes', 'i18n', $i18n);

            if ($acf_btns) {
                wp_localize_script($this->_token . '-admin-meta-boxes', 'wc_sa_acf_editor_btns', $acf_btns);
            }

            if ($screen->id == 'edit-wc_custom_statuses') {
                wp_enqueue_script($this->_token . '-sortable', esc_url($this->assets_url) . 'js/sortable.js', array('jquery'));
                wp_localize_script($this->_token . '-sortable', 'wc_sa_sortable_opt', array('ajax_url' => WC()->ajax_url()));
            }
        }

        if ($screen->id == 'woocommerce_page_wc-settings' && isset($_GET['tab']) && $_GET['tab'] == 'wc_sa_settings') {

            $defaults = array(
                'labels' => $this->default_statuses,
                'colors' => $this->color_statuses,
                'editing' => $this->default_editing
            );

            wp_enqueue_script($this->_token . '-admin-settings', esc_url($this->assets_url) . 'js/admin-settings' . $this->script_suffix . '.js', array('iris'));
            wp_localize_script($this->_token . '-admin-settings', 'wc_sa_defaults', $defaults);
        }

        if ($screen->id == 'edit-shop_order') {
            wp_enqueue_media();
            wp_enqueue_script($this->_token . '-note_promt', esc_url($this->assets_url) . 'js/note_promt.js', array('jquery'));
        }

        if ($screen->id == 'woocommerce_page_wc_bulk_change_status') {
            wp_register_script($this->_token . '-anysearch', esc_url($this->assets_url) . 'js/anysearch.js', array());
            wp_enqueue_script($this->_token . '-bulk_change_status', esc_url($this->assets_url) . 'js/bulk_change_status.js', array('jquery', 'jquery-blockui', $this->_token . '-anysearch'));
            $opts = array(
                'ajax_url' => WC()->ajax_url(),
                'error_i18n' => __('There was an error scanning barcode. Please try again later.', '')
            );
            wp_localize_script($this->_token . '-bulk_change_status', 'wc_sa_opt', $opts);
        }

        if ($screen->id == 'woocommerce_page_wc-settings') {
            $opts = array(
                'ajax_url' => WC()->ajax_url()
            );
            wp_enqueue_media();
            wp_enqueue_script('wc_sa_multidate_plugin', esc_url($this->assets_url) . 'plugins/MultiDatesPicker/jquery-ui.multidatespicker.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'));
            wp_enqueue_script('wc_sa_settings_script', esc_url($this->assets_url) . 'js/settings.js', array('jquery', 'jquery-ui-core'), true);
            wp_localize_script('wc_sa_settings_script', 'wc_sa_opt', $opts);
            if (isset($_GET['tab'])) {
                if ($_GET['tab'] == 'wc_sa_settings') {
                    wp_enqueue_script($this->_token . '-sortable', esc_url($this->assets_url) . 'js/sortable.js', array('jquery'));
                    wp_localize_script($this->_token . '-sortable', 'wc_sa_sortable_opt', array('ajax_url' => WC()->ajax_url()));
                }
            }
        }

    } // End admin_enqueue_scripts ()

    /**
     * Load plugin localisation
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function load_localisation()
    {
        load_plugin_textdomain('woocommerce_status_actions', false, dirname(plugin_basename($this->file)) . '/lang/');
    } // End load_localisation ()

    /**
     * Load plugin textdomain
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function load_plugin_textdomain()
    {
        $domain = 'woocommerce_status_actions';

        $locale = apply_filters('plugin_locale', get_locale(), $domain);

        load_textdomain($domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo');
        load_plugin_textdomain($domain, false, dirname(plugin_basename($this->file)) . '/lang/');
    } // End load_plugin_textdomain ()

    /**
     * Main WC_SA Instance
     *
     * Ensures only one instance of WC_SA is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see WC_SA()
     * @return WC_SA instance
     */
    public static function instance($file = '', $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    } // End instance ()

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    } // End __clone ()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    } // End __wakeup ()

    /**
     * Installation. Runs on activation.
     * @access  public
     * @since    2.0.0
     * @return  void
     */
    public function install($networkwide)
    {
        global $wpdb;

        if (function_exists('is_multisite') && is_multisite()) {
            // check if it is a network activation - if so, run the activation function for each blog id
            if ($networkwide) {
                $old_blog = $wpdb->blogid;
                // Get all blog ids
                $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
                foreach ($blogids as $blog_id) {
                    switch_to_blog($blog_id);
                    WC_SA_Install::install();
                }
                switch_to_blog($old_blog);
                return;
            }
        } else {
            WC_SA_Install::install();
        }
    } // End install ()

    /**
     * Log the plugin version number.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    private function _log_version_number()
    {
        update_option($this->_token . '_version', $this->_version);
    } // End _log_version_number ()

    public function add_wc_statuses($query)
    {
        if (strpos($query, "wp_posts.post_type = 'wc_custom_statuses'")) {
            $query = str_replace("wp_posts.post_type = 'wc_custom_statuses'", "wp_posts.post_type IN ( 'wc_order_status', 'wc_custom_statuses')", $query);
        }
        return $query;
    }

    public static function sa_update_notice()
    {
        if (get_transient('wc_sa_db_show_notice')) {
            ?>
            <div class="updated notice is-dismissible">
                <p>Thanks for installing Order Status & Actions Manager! To get started, configure your order statuses
                    by going to <a
                            href="<?php echo home_url() . '/wp-admin/admin.php?page=wc-settings&tab=wc_sa_settings' ?>">
                        WooCommerce > Settings > Order Statuses.</a>
                </p>
            </div>
            <?php
        }
        delete_transient('wc_sa_db_show_notice');
    }

    public function check_sa_status_permission($data, $postarr)
    {
        $post = get_post($postarr['ID']);

        if (!$post || $post->post_type != 'shop_order') {
            return $data;
        }

        $user_id = get_current_user_id();
        $old_status = $post->post_status;

        $new_status = '';
        if (isset($_GET['status'])) {
            $new_status = 'wc-' . $_GET['status'];
        } elseif (isset($_GET['status'])) {
            $new_status = $_POST['order_status'];
        } else {
            $new_status = $data['post_status'];
        }

        if (!in_array($old_status, $this->default_wc_order_statuses)) {
            $wc_sa_from_status = wc_sa_get_status_by_name(str_replace('wc-', '', $old_status));
        }

        if (!in_array($new_status, $this->default_wc_order_statuses)) {
            $wc_sa_to_status = wc_sa_get_status_by_name(str_replace('wc-', '', $new_status));
        }


        if (!isset($wc_sa_to_status) OR !isset($wc_sa_from_status)) {

            return $data;

        } elseif (($wc_sa_from_status->action_from_users && !in_array($user_id, $wc_sa_from_status->action_from_users))
            || ($wc_sa_to_status->action_to_users && !in_array($user_id, $wc_sa_to_status->action_to_users))
        ) {

            $statuses = wc_get_order_statuses();
            $error_messages = get_option('wc_sa_update_orders_status_error_message');
            $message = __("You do not have permissions to change an order <b>#{$post->ID}</b> from <b>{$statuses[$post->post_status]}</b> to <b>{$statuses[$new_status]}</b>", "woocommerce_status_actions");

            if (is_array($error_messages)) {
                array_push($error_messages, $message);
                update_option('wc_sa_update_orders_status_error_message', $error_messages);
            } else {
                add_option('wc_sa_update_orders_status_error_message', array($message));
            }

            $data['post_status'] = $old_status;
            if (isset($_POST['order_status'])) {
                $_POST['order_status'] = $old_status;
            }
        }
        return $data;
    }

    public function change_status_message_denied_notice()
    {
        $messages = get_option('wc_sa_update_orders_status_error_message');
        $class = 'notice notice-error';
        foreach ($messages as $message) {
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
        }
        delete_option('wc_sa_update_orders_status_error_message');
    }

    public function google_printer_actions()
    {
        if (!current_user_can('manage_options') || !isset($_GET['google_printer_action']) || empty($_GET['google_printer_action'])) {
            return;
        }
        switch ($_GET['google_printer_action']) {
            case 'disconnect':
                $opts = get_option('wc_sa_google_cloud_print_library_options');
                unset($opts['token']);
                update_option('wc_sa_google_cloud_print_library_options', $opts);
                delete_transient('wc_sa_google_cloud_print_library_printers');
                break;
            case 'order_preview':
                if (!isset($_GET['order_id']) || !$_GET['order_id']
                    || !isset($_GET['type']) || !$_GET['type']
                ) {
                    return;
                }
                $order = wc_get_order(intval($_GET['order_id']));
                if (!$order) {
                    return;
                }
                $hidden_order_itemmeta = apply_filters('woocommerce_hidden_order_itemmeta', array(
                    '_qty',
                    '_tax_class',
                    '_product_id',
                    '_variation_id',
                    '_line_subtotal',
                    '_line_subtotal_tax',
                    '_line_total',
                    '_line_tax',
                    'method_id',
                    'cost',
                ));
                ob_start();
                include_once 'templates/html-' . $_GET['type'] . '.php';
                $html = ob_get_clean();
                wp_die($html);
                break;
        }
    }

    public function wc_sa_admin_order_actions($actions, $order)
    {
        $sa_statuses = wc_sa_get_statusesList();
        foreach ($actions as $status => $action){
            $_status = $status == 'complete' ? 'wc-completed' : 'wc-' . $status;
            if(array_key_exists($_status, $sa_statuses) && isset($sa_statuses[$_status])){
                $actions[$status]['name'] = $sa_statuses[$_status];
            }
        }
        return $actions;
    }
}