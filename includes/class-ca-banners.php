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
    const VERSION = CA_Banners_Constants::PLUGIN_VERSION;
    
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
     * Error Handler instance
     */
    public $error_handler;
    
    /**
     * Get the singleton instance of the CA_Banners class
     * 
     * This method implements the singleton pattern to ensure only one instance
     * of the CA_Banners class exists throughout the plugin lifecycle.
     * 
     * @since 1.2.7
     * @return CA_Banners The singleton instance of the CA_Banners class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Private constructor for singleton pattern
     * 
     * Initializes the plugin by defining constants, loading dependencies,
     * and setting up WordPress hooks. This constructor is private to enforce
     * the singleton pattern.
     * 
     * @since 1.2.7
     */
    private function __construct() {
        $this->define_constants();
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Define plugin constants
     * 
     * Ensures that all required plugin constants are defined. If constants
     * are not already defined in the main plugin file, they are defined here
     * as a fallback.
     * 
     * @since 1.2.7
     */
    private function define_constants() {
        // Constants are already defined in main plugin file
        // Just ensure they exist
        if (!defined('CA_BANNERS_VERSION')) {
            define('CA_BANNERS_VERSION', self::VERSION);
        }
    }
    
    /**
     * Load all required plugin dependencies
     * 
     * Loads all core classes required for the plugin to function properly.
     * This includes validator, URL matcher, scheduler, settings, admin,
     * frontend, and error handler classes.
     * 
     * @since 1.2.7
     */
    private function load_dependencies() {
        // Load core classes (error handler already loaded in main plugin file)
        require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-validator.php';
        require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-url-matcher.php';
        require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-scheduler.php';
        require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-settings.php';
        require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-admin.php';
        require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-frontend.php';
    }
    
    /**
     * Initialize WordPress hooks and actions
     * 
     * Sets up all WordPress hooks including init, plugins_loaded, activation,
     * deactivation, custom capabilities, error handling, and cache invalidation.
     * 
     * @since 1.2.7
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Activation and deactivation hooks
        register_activation_hook(CA_BANNERS_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(CA_BANNERS_PLUGIN_FILE, array($this, 'deactivate'));
        
        // Add custom capabilities
        add_action('init', array($this, 'add_custom_capabilities'));
        
        // Add error handling
        register_shutdown_function(array($this, 'handle_fatal_error'));
        
        // Add cache invalidation hooks
        add_action('update_option_banner_plugin_settings', array($this, 'invalidate_settings_cache'));
        add_action('ca_banners_settings_updated', array($this, 'invalidate_banner_cache'));
    }
    
    /**
     * Initialize the plugin components
     * 
     * This method is called on the WordPress 'init' action hook. It initializes
     * all core classes including error handler, validator, URL matcher, scheduler,
     * settings, admin (if in admin area), and frontend classes.
     * 
     * @since 1.2.7
     */
    public function init() {
        try {
            // Initialize error handler first
            $this->error_handler = CA_Banners_Error_Handler::get_instance();
            
            // Debug: Check if init is being called
            if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('view_ca_banners')) {
                error_log('CA Banners: init() called');
            }
            
            // Initialize core classes with error handling
            $this->validator = $this->safe_instantiate('CA_Banners_Validator');
            $this->url_matcher = $this->safe_instantiate('CA_Banners_URL_Matcher');
            $this->scheduler = $this->safe_instantiate('CA_Banners_Scheduler');
            $this->settings = $this->safe_instantiate('CA_Banners_Settings');
            
            // Initialize admin and frontend
            if (is_admin()) {
                $this->admin = $this->safe_instantiate('CA_Banners_Admin');
                if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('view_ca_banners')) {
                    error_log('CA Banners: Admin class initialized');
                }
            }
            
            // Always initialize frontend for banner display
            $this->frontend = $this->safe_instantiate('CA_Banners_Frontend');
            if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('view_ca_banners')) {
                error_log('CA Banners: Frontend class initialized');
            }
            
            // Fire action for other plugins to hook into
            do_action('ca_banners_init', $this);
            
        } catch (Exception $e) {
            $this->log_error('Plugin initialization failed', $e);
            // Don't break the site, just log the error
        }
    }
    
    /**
     * Load plugin textdomain for internationalization
     * 
     * Loads the plugin's text domain for translation support. The text domain
     * is loaded from the languages directory relative to the plugin file.
     * 
     * @since 1.2.7
     */
    public function load_textdomain() {
        load_plugin_textdomain('ca-banners', false, dirname(CA_BANNERS_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Handle plugin activation
     * 
     * Called when the plugin is activated. Sets up custom capabilities,
     * default options, and fires activation hooks for other plugins
     * to hook into.
     * 
     * @since 1.2.7
     */
    public function activate() {
        // Add custom capabilities
        $this->add_custom_capabilities();
        
        // Set default options using constants
        $default_options = CA_Banners_Constants::get_default_settings();
        
        add_option('banner_plugin_settings', $default_options);
        
        // Fire activation action
        do_action('ca_banners_activate');
    }
    
    /**
     * Handle plugin deactivation
     * 
     * Called when the plugin is deactivated. Fires deactivation hooks
     * for other plugins to hook into for cleanup operations.
     * 
     * @since 1.2.7
     */
    public function deactivate() {
        // Fire deactivation action
        do_action('ca_banners_deactivate');
    }
    
    /**
     * Add custom capabilities to WordPress roles
     * 
     * Adds custom capabilities (manage, edit, view) to the administrator role
     * and allows other plugins to add capabilities to other roles via hooks.
     * 
     * @since 1.2.7
     */
    public function add_custom_capabilities() {
        // Get administrator role
        $admin_role = get_role('administrator');
        if ($admin_role) {
        // Add custom capabilities to administrator role
        $admin_role->add_cap(CA_Banners_Constants::CAPABILITY_MANAGE);
        $admin_role->add_cap(CA_Banners_Constants::CAPABILITY_EDIT);
        $admin_role->add_cap(CA_Banners_Constants::CAPABILITY_VIEW);
        }
        
        // Allow other plugins to add capabilities to other roles
        do_action('ca_banners_add_capabilities');
    }
    
    /**
     * Remove custom capabilities from all WordPress roles
     * 
     * Removes all custom CA Banners capabilities from all WordPress roles.
     * This is typically called during plugin uninstallation for cleanup.
     * 
     * @since 1.2.7
     */
    public function remove_custom_capabilities() {
        // Get all roles
        $roles = wp_roles()->roles;
        
        foreach ($roles as $role_name => $role_data) {
            $role = get_role($role_name);
            if ($role) {
                $role->remove_cap(CA_Banners_Constants::CAPABILITY_MANAGE);
                $role->remove_cap(CA_Banners_Constants::CAPABILITY_EDIT);
                $role->remove_cap(CA_Banners_Constants::CAPABILITY_VIEW);
            }
        }
        
        // Allow other plugins to remove capabilities
        do_action('ca_banners_remove_capabilities');
    }
    
    /**
     * Get the current plugin version
     * 
     * @since 1.2.7
     * @return string The plugin version number
     */
    public function get_version() {
        return self::VERSION;
    }
    
    /**
     * Get the plugin directory path
     * 
     * @since 1.2.7
     * @return string The absolute path to the plugin directory
     */
    public function get_plugin_dir() {
        return CA_BANNERS_PLUGIN_DIR;
    }
    
    /**
     * Safely instantiate a class with error handling
     * 
     * Attempts to instantiate a class by name with proper error handling.
     * If the class doesn't exist or instantiation fails, it logs the error
     * and returns null instead of breaking the site.
     * 
     * @since 1.2.7
     * @param string $class_name The name of the class to instantiate
     * @return object|null The instantiated class object or null on failure
     */
    private function safe_instantiate($class_name) {
        try {
            if (!class_exists($class_name)) {
                throw new Exception("Class {$class_name} does not exist");
            }
            return new $class_name();
        } catch (Exception $e) {
            $this->log_error("Failed to instantiate {$class_name}", $e);
            return null;
        }
    }
    
    /**
     * Log errors with proper formatting and context
     * 
     * Centralized error logging method that uses the error handler if available,
     * or falls back to basic WordPress error logging.
     * 
     * @since 1.2.7
     * @param string $message The error message to log
     * @param Exception|null $exception Optional exception object
     * @param string $type Error type (use CA_Banners_Error_Handler constants)
     * @param string $severity Error severity (use CA_Banners_Error_Handler constants)
     */
    public function log_error($message, $exception = null, $type = CA_Banners_Error_Handler::TYPE_SYSTEM, $severity = CA_Banners_Error_Handler::SEVERITY_MEDIUM) {
        if ($this->error_handler) {
            $this->error_handler->log_error($message, $type, $severity, $exception);
        } else {
            // Fallback to basic logging if error handler not available
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('CA Banners Error: ' . $message . ($exception ? ' - ' . $exception->getMessage() : ''));
            }
        }
    }
    
    /**
     * Handle fatal errors gracefully
     * 
     * Called on PHP shutdown to catch and log fatal errors that would
     * otherwise crash the site. Only handles critical error types.
     * 
     * @since 1.2.7
     */
    public function handle_fatal_error() {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
            $this->log_error('Fatal error occurred', new Exception($error['message']), CA_Banners_Error_Handler::TYPE_SYSTEM, CA_Banners_Error_Handler::SEVERITY_CRITICAL);
        }
    }
    
    /**
     * Get cached settings with fallback to database
     * 
     * Retrieves settings from WordPress transient cache. If cache miss occurs,
     * loads settings from database and caches them for future requests.
     * 
     * @since 1.2.7
     * @return array The settings array
     */
    public function get_cached_settings() {
        $cache_key = CA_Banners_Constants::get_settings_cache_key();
        $cached_settings = get_transient($cache_key);
        
        if ($cached_settings === false) {
            // Cache miss - get from database
            $settings = get_option(CA_Banners_Constants::OPTION_NAME, array());
            
            // Cache for configured timeout
            set_transient($cache_key, $settings, CA_Banners_Constants::CACHE_SETTINGS_TIMEOUT);
            
            if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('view_ca_banners')) {
                error_log('CA Banners: Settings cache miss - loaded from database');
            }
            
            return $settings;
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('view_ca_banners')) {
            error_log('CA Banners: Settings cache hit');
        }
        
        return $cached_settings;
    }
    
    /**
     * Invalidate the settings cache
     * 
     * Removes cached settings from WordPress transients, forcing the next
     * request to load fresh settings from the database.
     * 
     * @since 1.2.7
     */
    public function invalidate_settings_cache() {
        $cache_key = CA_Banners_Constants::get_settings_cache_key();
        delete_transient($cache_key);
        
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('view_ca_banners')) {
            error_log('CA Banners: Settings cache invalidated');
        }
    }
    
    /**
     * Get cached banner HTML output
     * 
     * Retrieves cached banner HTML based on settings hash. Returns false
     * if no cached HTML is found.
     * 
     * @since 1.2.7
     * @param string $settings_hash Hash of settings used to generate the banner
     * @return string|false Cached HTML or false if not found
     */
    public function get_cached_banner_html($settings_hash) {
        $cache_key = CA_Banners_Constants::get_banner_cache_key($settings_hash);
        return get_transient($cache_key);
    }
    
    /**
     * Cache banner HTML output
     * 
     * Stores generated banner HTML in WordPress transients for future requests
     * with the same settings.
     * 
     * @since 1.2.7
     * @param string $settings_hash Hash of settings used to generate the banner
     * @param string $html The HTML to cache
     */
    public function set_cached_banner_html($settings_hash, $html) {
        $cache_key = CA_Banners_Constants::get_banner_cache_key($settings_hash);
        
        // Cache for configured timeout
        set_transient($cache_key, $html, CA_Banners_Constants::CACHE_BANNER_TIMEOUT);
        
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('view_ca_banners')) {
            error_log('CA Banners: Banner HTML cached');
        }
    }
    
    /**
     * Invalidate all banner HTML cache entries
     * 
     * Removes all cached banner HTML from WordPress transients by deleting
     * all options that match the banner cache prefix.
     * 
     * @since 1.2.7
     */
    public function invalidate_banner_cache() {
        global $wpdb;
        
        // Delete all banner HTML transients
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                CA_Banners_Constants::CACHE_TRANSIENT_PREFIX . CA_Banners_Constants::CACHE_BANNER_PREFIX . '%'
            )
        );
        
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('view_ca_banners')) {
            error_log('CA Banners: Banner HTML cache invalidated');
        }
    }
    
    /**
     * Get cache statistics
     * 
     * Retrieves statistics about cached items including total cached items
     * and various cache hit/miss counters.
     * 
     * @since 1.2.7
     * @return array Array of cache statistics
     */
    public function get_cache_stats() {
        global $wpdb;
        
        $stats = array(
            'settings_cache_hits' => 0,
            'settings_cache_misses' => 0,
            'banner_cache_hits' => 0,
            'banner_cache_misses' => 0,
            'total_cached_items' => 0
        );
        
        // Count cached items
        $cached_items = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
                CA_Banners_Constants::CACHE_TRANSIENT_PREFIX . '%'
            )
        );
        
        $stats['total_cached_items'] = intval($cached_items);
        
        return $stats;
    }
    
    /**
     * Clear all plugin caches
     * 
     * Removes all cached data including settings cache, banner HTML cache,
     * and expired transients.
     * 
     * @since 1.2.7
     */
    public function clear_all_caches() {
        global $wpdb;
        
        // Delete all plugin transients
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                CA_Banners_Constants::CACHE_TRANSIENT_PREFIX . '%'
            )
        );
        
        // Delete expired transients
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
                CA_Banners_Constants::CACHE_TIMEOUT_PREFIX . '%',
                time()
            )
        );
        
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('view_ca_banners')) {
            error_log('CA Banners: All caches cleared');
        }
    }
    
    /**
     * Get the plugin URL
     * 
     * @since 1.2.7
     * @return string The URL to the plugin directory
     */
    public function get_plugin_url() {
        return CA_BANNERS_PLUGIN_URL;
    }
}

