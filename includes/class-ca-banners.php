<?php
/**
 * Main plugin class
 *
 * @package CA_Banners
 * @since 1.2.7
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main CA Banners class
 */
class CA_Banners {
    
    /**
     * Plugin version
     */
    const VERSION = '2.0.0';
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Admin instance
     */
    public $admin;
    
    /**
     * Frontend instance
     */
    public $frontend;
    
    /**
     * Settings instance
     */
    public $settings;
    
    /**
     * Validator instance
     */
    public $validator;
    
    /**
     * Scheduler instance
     */
    public $scheduler;
    
    /**
     * URL Matcher instance
     */
    public $url_matcher;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->define_constants();
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Define plugin constants
     */
    private function define_constants() {
        // Constants are already defined in main plugin file
        // Just ensure they exist
        if (!defined('CA_BANNERS_VERSION')) {
            define('CA_BANNERS_VERSION', self::VERSION);
        }
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Load core classes
        require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-validator.php';
        require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-url-matcher.php';
        require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-scheduler.php';
        require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-settings.php';
        require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-admin.php';
        require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-frontend.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Activation and deactivation hooks
        register_activation_hook(CA_BANNERS_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(CA_BANNERS_PLUGIN_FILE, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Debug: Check if init is being called
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('CA Banners: init() called');
        }
        
        // Initialize core classes
        $this->validator = new CA_Banners_Validator();
        $this->url_matcher = new CA_Banners_URL_Matcher();
        $this->scheduler = new CA_Banners_Scheduler();
        $this->settings = new CA_Banners_Settings();
        
        // Initialize admin and frontend
        if (is_admin()) {
            $this->admin = new CA_Banners_Admin();
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('CA Banners: Admin class initialized');
            }
        }
        
        // Always initialize frontend for banner display
        $this->frontend = new CA_Banners_Frontend();
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('CA Banners: Frontend class initialized');
        }
        
        // Fire action for other plugins to hook into
        do_action('ca_banners_init', $this);
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('ca-banners', false, dirname(CA_BANNERS_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        $default_options = array(
            'enabled' => false,
            'message' => '',
            'repeat' => 10,
            'background_color' => '#729946',
            'text_color' => '#000000',
            'font_size' => 16,
            'font_family' => 'Arial',
            'border_width' => 0,
            'border_style' => 'solid',
            'border_color' => '#000000',
            'sitewide' => false,
            'disable_mobile' => false,
            'urls' => '',
            'exclude_urls' => '',
            'start_date' => '',
            'end_date' => '',
            'image' => '',
            'image_start_date' => '',
            'image_end_date' => ''
        );
        
        add_option('banner_plugin_settings', $default_options);
        
        // Fire activation action
        do_action('ca_banners_activate');
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Fire deactivation action
        do_action('ca_banners_deactivate');
    }
    
    /**
     * Get plugin version
     */
    public function get_version() {
        return self::VERSION;
    }
    
    /**
     * Get plugin directory path
     */
    public function get_plugin_dir() {
        return CA_BANNERS_PLUGIN_DIR;
    }
    
    /**
     * Get plugin URL
     */
    public function get_plugin_url() {
        return CA_BANNERS_PLUGIN_URL;
    }
}
