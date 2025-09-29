<?php
/**
 * Constants class for CA Banners Plugin
 *
 * @package CA_Banners
 * @since 2.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CA Banners Constants class
 * 
 * Defines all configurable values and magic numbers used throughout the plugin
 * to improve maintainability and reduce hardcoded values.
 */
class CA_Banners_Constants {
    
    // =============================================================================
    // PLUGIN CONFIGURATION CONSTANTS
    // =============================================================================
    
    /**
     * Plugin version
     */
    const PLUGIN_VERSION = '1.2.0';
    
    /**
     * Plugin text domain
     */
    const TEXT_DOMAIN = 'ca-banners';
    
    /**
     * Plugin option name
     */
    const OPTION_NAME = 'banner_plugin_settings';
    
    /**
     * Plugin capability prefix
     */
    const CAPABILITY_PREFIX = 'ca_banners';
    
    // =============================================================================
    // DEFAULT SETTINGS VALUES
    // =============================================================================
    
    /**
     * Default banner settings
     */
    const DEFAULT_ENABLED = false;
    const DEFAULT_MESSAGE = '';
    const DEFAULT_REPEAT = 10;
    const DEFAULT_SPEED = 60;
    const DEFAULT_MOBILE_SPEED_MULTIPLIER = 0.5;
    const DEFAULT_TABLET_SPEED_MULTIPLIER = 0.75;
    const DEFAULT_BACKGROUND_COLOR = '#729946';
    const DEFAULT_TEXT_COLOR = '#000000';
    const DEFAULT_FONT_SIZE = 16;
    const DEFAULT_FONT_FAMILY = 'Arial';
    const DEFAULT_FONT_WEIGHT = '600';
    const DEFAULT_BORDER_WIDTH = 0;
    const DEFAULT_BORDER_STYLE = 'solid';
    const DEFAULT_BORDER_COLOR = '#000000';
    const DEFAULT_SITEWIDE = true;
    const DEFAULT_DISABLE_MOBILE = false;
    const DEFAULT_URLS = '';
    const DEFAULT_EXCLUDE_URLS = '';
    const DEFAULT_START_DATE = '';
    const DEFAULT_END_DATE = '';
    const DEFAULT_IMAGE = '';
    const DEFAULT_IMAGE_START_DATE = '';
    const DEFAULT_IMAGE_END_DATE = '';
    const DEFAULT_STICKY = false;
    
    // Button defaults
    const DEFAULT_BUTTON_ENABLED = false;
    const DEFAULT_BUTTON_TEXT = '';
    const DEFAULT_BUTTON_LINK = '';
    const DEFAULT_BUTTON_COLOR = '#ce7a31';
    const DEFAULT_BUTTON_TEXT_COLOR = '#ffffff';
    const DEFAULT_BUTTON_BORDER_WIDTH = 0;
    const DEFAULT_BUTTON_BORDER_COLOR = '#ce7a31';
    const DEFAULT_BUTTON_BORDER_RADIUS = 4;
    const DEFAULT_BUTTON_PADDING = 8;
    const DEFAULT_BUTTON_FONT_SIZE = 14;
    const DEFAULT_BUTTON_FONT_WEIGHT = '600';
    const DEFAULT_BUTTON_MARGIN_LEFT = 20;
    const DEFAULT_BUTTON_MARGIN_RIGHT = 20;
    
    // =============================================================================
    // VALIDATION LIMITS AND CONSTRAINTS
    // =============================================================================
    
    /**
     * Input validation limits
     */
    const MAX_MESSAGE_LENGTH = 2000;
    const MAX_URL_LIST_LENGTH = 5000;
    const MAX_BUTTON_TEXT_LENGTH = 100;
    const MAX_BUTTON_LINK_LENGTH = 500;
    const MAX_IMAGE_URL_LENGTH = 500;
    const MAX_INDIVIDUAL_URL_LENGTH = 200;
    const MAX_DATE_LENGTH = 10;
    
    /**
     * Numeric value ranges
     */
    const MIN_REPEAT_VALUE = 1;
    const MAX_REPEAT_VALUE = 100;
    const MIN_SPEED_VALUE = 10;
    const MAX_SPEED_VALUE = 100;
    const MIN_SPEED_MULTIPLIER = 0.1;
    const MAX_SPEED_MULTIPLIER = 2.0;
    const MIN_FONT_SIZE = 10;
    const MAX_FONT_SIZE = 40;
    const MIN_BORDER_WIDTH = 0;
    const MAX_BORDER_WIDTH = 10;
    const MIN_BUTTON_BORDER_WIDTH = 0;
    const MAX_BUTTON_BORDER_WIDTH = 10;
    const MIN_BUTTON_BORDER_RADIUS = 0;
    const MAX_BUTTON_BORDER_RADIUS = 50;
    const MIN_BUTTON_PADDING = 0;
    const MAX_BUTTON_PADDING = 50;
    const MIN_BUTTON_FONT_SIZE = 8;
    const MAX_BUTTON_FONT_SIZE = 24;
    const MIN_BUTTON_MARGIN = 0;
    const MAX_BUTTON_MARGIN = 200;
    
    /**
     * Date validation ranges
     */
    const MIN_YEAR = 2020;
    const MAX_YEAR = 2030;
    const MIN_MONTH = 1;
    const MAX_MONTH = 12;
    const MIN_DAY = 1;
    const MAX_DAY = 31;
    
    // =============================================================================
    // CACHE CONFIGURATION
    // =============================================================================
    
    /**
     * Cache timeouts (in seconds)
     */
    const CACHE_SETTINGS_TIMEOUT = 3600; // 1 hour
    const CACHE_BANNER_TIMEOUT = 1800;    // 30 minutes
    
    /**
     * Cache key prefixes
     */
    const CACHE_SETTINGS_PREFIX = 'ca_banners_settings_';
    const CACHE_BANNER_PREFIX = 'ca_banners_html_';
    const CACHE_TRANSIENT_PREFIX = '_transient_ca_banners_';
    const CACHE_TIMEOUT_PREFIX = '_transient_timeout_ca_banners_';
    
    /**
     * Maximum cached items
     */
    const MAX_CACHED_ITEMS = 50;
    
    // =============================================================================
    // ERROR HANDLING CONSTANTS
    // =============================================================================
    
    /**
     * Error severity levels
     */
    const ERROR_SEVERITY_LOW = 'low';
    const ERROR_SEVERITY_MEDIUM = 'medium';
    const ERROR_SEVERITY_HIGH = 'high';
    const ERROR_SEVERITY_CRITICAL = 'critical';
    
    /**
     * Error types
     */
    const ERROR_TYPE_VALIDATION = 'validation';
    const ERROR_TYPE_SECURITY = 'security';
    const ERROR_TYPE_PERMISSION = 'permission';
    const ERROR_TYPE_SYSTEM = 'system';
    const ERROR_TYPE_USER = 'user';
    const ERROR_TYPE_CACHE = 'cache';
    const ERROR_TYPE_DATABASE = 'database';
    
    /**
     * Maximum errors to keep in memory
     */
    const MAX_ERROR_LOG_ENTRIES = 50;
    
    // =============================================================================
    // SECURITY CONSTANTS
    // =============================================================================
    
    /**
     * Dangerous protocols to block
     */
    const DANGEROUS_PROTOCOLS = array('javascript:', 'data:', 'vbscript:', 'file:', 'ftp:');
    
    /**
     * Dangerous HTML attributes to remove
     */
    const DANGEROUS_ATTRIBUTES = array('onload', 'onerror', 'onclick', 'onmouseover', 'onfocus', 'onblur');
    
    /**
     * Potentially problematic colors (for monitoring)
     */
    const PROBLEMATIC_COLORS = array('#000000', '#ffffff', '#ff0000', '#00ff00', '#0000ff');
    
    // =============================================================================
    // UI AND DISPLAY CONSTANTS
    // =============================================================================
    
    /**
     * Mobile breakpoint
     */
    const MOBILE_BREAKPOINT = 768;
    
    /**
     * Banner styling constants
     */
    const BANNER_Z_INDEX = 999999;
    const BANNER_MIN_HEIGHT = 40;
    const BANNER_PADDING = 10;
    const BANNER_BORDER_RADIUS = 4;
    const BANNER_BUTTON_MARGIN_LEFT = 20;
    
    /**
     * Preview settings
     */
    const PREVIEW_MAX_REPEATS = 5;
    const PREVIEW_ANIMATION_DURATION_MULTIPLIER = 10;
    
    /**
     * Admin interface constants
     */
    const ADMIN_MAX_WIDTH = 1200;
    const ADMIN_CARD_MARGIN = 24;
    const ADMIN_CARD_PADDING = 24;
    const ADMIN_CARD_BORDER_RADIUS = 8;
    
    // =============================================================================
    // FONT AND STYLING OPTIONS
    // =============================================================================
    
    /**
     * Allowed font families
     */
    const ALLOWED_FONT_FAMILIES = array(
        'Arial', 'Helvetica', 'Times New Roman', 'Georgia', 'Courier New',
        'Verdana', 'Tahoma', 'Trebuchet MS', 'Impact', 'Comic Sans MS',
        'Raleway', 'Open Sans', 'Roboto', 'Lato', 'Montserrat'
    );
    
    /**
     * Allowed font weights
     */
    const ALLOWED_FONT_WEIGHTS = array(
        'normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900'
    );
    
    /**
     * Allowed border styles
     */
    const ALLOWED_BORDER_STYLES = array(
        'solid', 'dashed', 'dotted', 'double', 'none', 'groove', 'ridge', 'inset', 'outset'
    );
    
    // =============================================================================
    // QUICK OPTIONS FOR ADMIN INTERFACE
    // =============================================================================
    
    /**
     * Quick include options
     */
    const QUICK_INCLUDE_OPTIONS = array(
        'all_pages' => 'All Pages',
        'all_posts' => 'All Posts', 
        'all_products' => 'All Products (WooCommerce)',
        'home_page' => 'Home Page Only',
        'blog_page' => 'Blog Page Only'
    );
    
    /**
     * Quick exclude options
     */
    const QUICK_EXCLUDE_OPTIONS = array(
        'checkout' => 'Checkout Pages',
        'cart' => 'Cart Pages',
        'my_account' => 'My Account Pages',
        'admin' => 'Admin Pages',
        'login' => 'Login/Register Pages'
    );
    
    // =============================================================================
    // THEME COMPATIBILITY
    // =============================================================================
    
    /**
     * Problematic themes that may need special handling
     */
    const PROBLEMATIC_THEMES = array(
        'avada', 'enfold', 'the7', 'beaver builder', 'elementor', 'astra', 'generatepress', 
        'oceanwp', 'storefront', 'flatsome', 'woodmart', 'porto', 'bridge', 'salient'
    );
    
    // =============================================================================
    // AJAX AND NONCE CONSTANTS
    // =============================================================================
    
    /**
     * AJAX action names
     */
    const AJAX_CLEAR_SETTINGS_CACHE = 'ca_banners_clear_settings_cache';
    const AJAX_CLEAR_BANNER_CACHE = 'ca_banners_clear_banner_cache';
    const AJAX_CLEAR_ALL_CACHE = 'ca_banners_clear_all_cache';
    const AJAX_CLEAR_ERROR_LOG = 'ca_banners_clear_error_log';
    
    /**
     * Nonce action names
     */
    const NONCE_SETTINGS_ACTION = 'ca_banners_settings';
    const NONCE_CACHE_ACTION = 'ca_banners_cache_nonce';
    const NONCE_ERROR_LOG_ACTION = 'ca_banners_error_log_nonce';
    
    // =============================================================================
    // WORDPRESS INTEGRATION CONSTANTS
    // =============================================================================
    
    /**
     * WordPress capability names
     */
    const CAPABILITY_MANAGE = 'manage_ca_banners';
    const CAPABILITY_EDIT = 'edit_ca_banners';
    const CAPABILITY_VIEW = 'view_ca_banners';
    
    /**
     * WordPress hook priorities
     */
    const HOOK_PRIORITY_ENQUEUE_SCRIPTS = 5;
    const HOOK_PRIORITY_RENDER_BANNER = 1;
    const HOOK_PRIORITY_THEME_FIXES = 999;
    
    /**
     * WordPress hook names
     */
    const HOOK_CLEANUP = 'ca_banners_cleanup';
    const HOOK_UNINSTALL = 'ca_banners_uninstall';
    
    /**
     * WordPress admin page slug
     */
    const ADMIN_PAGE_SLUG = 'banner-plugin';
    const ADMIN_ERROR_LOG_SLUG = 'ca-banners-errors';
    
    // =============================================================================
    // CSS AND JAVASCRIPT CONSTANTS
    // =============================================================================
    
    /**
     * CSS class names
     */
    const CSS_BANNER_CONTAINER = 'ca-banner-container';
    const CSS_BANNER_CONTENT = 'ca-banner-content';
    const CSS_BANNER_MESSAGE = 'ca-banner-message';
    const CSS_BANNER_BUTTON = 'ca-banner-button';
    const CSS_BANNER_IMAGE = 'ca-banner-image';
    const CSS_BANNER_ACTIVE = 'ca-banner-active';
    const CSS_BANNER_PREVIEW = 'ca-banner-preview';
    const CSS_BANNER_TOGGLE = 'ca-banner-toggle';
    const CSS_BANNER_CARD = 'ca-banner-card';
    
    /**
     * CSS animation names
     */
    const CSS_ANIMATION_SCROLL = 'ca-banner-preview-scroll';
    const CSS_ANIMATION_STYLE_ID = 'ca-banner-animation-style';
    
    /**
     * JavaScript variable names
     */
    const JS_CONFIG_VAR = 'caBannerConfig';
    const JS_BANNER_OBJECT = 'caBanner';
    
    // =============================================================================
    // TIME AND DATE CONSTANTS
    // =============================================================================
    
    /**
     * Time conversion constants
     */
    const SECONDS_PER_MINUTE = 60;
    const SECONDS_PER_HOUR = 3600;
    const SECONDS_PER_DAY = 86400;
    
    /**
     * Date format constants
     */
    const DATE_FORMAT_DISPLAY = 'Y-m-d H:i';
    const DATE_FORMAT_INPUT = 'Y-m-d';
    
    // =============================================================================
    // FILE AND PATH CONSTANTS
    // =============================================================================
    
    /**
     * File extensions
     */
    const CSS_FILE_EXTENSION = 'frontend.css';
    const JS_FILE_EXTENSION = '.js';
    const PHP_FILE_EXTENSION = '.php';
    
    /**
     * Directory names
     */
    const DIR_INCLUDES = 'includes';
    const DIR_ADMIN = 'admin';
    const DIR_PUBLIC = 'public';
    const DIR_LANGUAGES = 'languages';
    const DIR_CSS = 'css';
    const DIR_JS = 'js';
    
    // =============================================================================
    // DEBUG AND DEVELOPMENT CONSTANTS
    // =============================================================================
    
    /**
     * Debug settings
     */
    const DEBUG_LOG_PREFIX = 'CA Banners';
    const DEBUG_COMMENT_PREFIX = 'CA Banners:';
    
    /**
     * Development timeouts
     */
    const DEV_INIT_DELAY = 100;
    const DEV_PREVIEW_DELAY = 200;
    const DEV_AUTO_SAVE_DELAY = 2000;
    const DEV_MESSAGE_DISPLAY_TIME = 3000;
    const DEV_WARNING_DISPLAY_TIME = 5000;
    
    // =============================================================================
    // UTILITY METHODS
    // =============================================================================
    
    /**
     * Get all default settings as an array
     * 
     * Returns a complete array of all default settings with their values.
     * This is used for initializing new settings and providing fallbacks.
     * 
     * @since 2.1.0
     * @return array Complete default settings array
     */
    public static function get_default_settings() {
        return array(
            'enabled' => self::DEFAULT_ENABLED,
            'message' => self::DEFAULT_MESSAGE,
            'repeat' => self::DEFAULT_REPEAT,
            'speed' => self::DEFAULT_SPEED,
            'mobile_speed_multiplier' => self::DEFAULT_MOBILE_SPEED_MULTIPLIER,
            'tablet_speed_multiplier' => self::DEFAULT_TABLET_SPEED_MULTIPLIER,
            'background_color' => self::DEFAULT_BACKGROUND_COLOR,
            'text_color' => self::DEFAULT_TEXT_COLOR,
            'font_size' => self::DEFAULT_FONT_SIZE,
            'font_family' => self::DEFAULT_FONT_FAMILY,
            'font_weight' => self::DEFAULT_FONT_WEIGHT,
            'border_width' => self::DEFAULT_BORDER_WIDTH,
            'border_style' => self::DEFAULT_BORDER_STYLE,
            'border_color' => self::DEFAULT_BORDER_COLOR,
            'sitewide' => self::DEFAULT_SITEWIDE,
            'disable_mobile' => self::DEFAULT_DISABLE_MOBILE,
            'urls' => self::DEFAULT_URLS,
            'exclude_urls' => self::DEFAULT_EXCLUDE_URLS,
            'start_date' => self::DEFAULT_START_DATE,
            'end_date' => self::DEFAULT_END_DATE,
            'image' => self::DEFAULT_IMAGE,
            'image_start_date' => self::DEFAULT_IMAGE_START_DATE,
            'image_end_date' => self::DEFAULT_IMAGE_END_DATE,
            'sticky' => self::DEFAULT_STICKY,
            'button_enabled' => self::DEFAULT_BUTTON_ENABLED,
            'button_text' => self::DEFAULT_BUTTON_TEXT,
            'button_link' => self::DEFAULT_BUTTON_LINK,
            'button_color' => self::DEFAULT_BUTTON_COLOR,
            'button_text_color' => self::DEFAULT_BUTTON_TEXT_COLOR,
            'button_border_width' => self::DEFAULT_BUTTON_BORDER_WIDTH,
            'button_border_color' => self::DEFAULT_BUTTON_BORDER_COLOR,
            'button_border_radius' => self::DEFAULT_BUTTON_BORDER_RADIUS,
            'button_padding' => self::DEFAULT_BUTTON_PADDING,
            'button_font_size' => self::DEFAULT_BUTTON_FONT_SIZE,
            'button_font_weight' => self::DEFAULT_BUTTON_FONT_WEIGHT,
            'button_margin_left' => self::DEFAULT_BUTTON_MARGIN_LEFT,
            'button_margin_right' => self::DEFAULT_BUTTON_MARGIN_RIGHT
        );
    }
    
    /**
     * Get input length limits as an array
     * 
     * Returns validation limits for all text inputs to prevent DoS attacks
     * and ensure reasonable data sizes.
     * 
     * @since 2.1.0
     * @return array Array of field names and their maximum lengths
     */
    public static function get_length_limits() {
        return array(
            'message' => self::MAX_MESSAGE_LENGTH,
            'urls' => self::MAX_URL_LIST_LENGTH,
            'exclude_urls' => self::MAX_URL_LIST_LENGTH,
            'button_text' => self::MAX_BUTTON_TEXT_LENGTH,
            'button_link' => self::MAX_BUTTON_LINK_LENGTH,
            'image' => self::MAX_IMAGE_URL_LENGTH,
            'start_date' => self::MAX_DATE_LENGTH,
            'end_date' => self::MAX_DATE_LENGTH,
            'image_start_date' => self::MAX_DATE_LENGTH,
            'image_end_date' => self::MAX_DATE_LENGTH
        );
    }
    
    /**
     * Get numeric validation ranges as an array
     * 
     * Returns min/max/default values for all numeric inputs to ensure
     * they fall within acceptable ranges.
     * 
     * @since 2.1.0
     * @return array Array of field names and their numeric ranges
     */
    public static function get_numeric_ranges() {
        return array(
            'repeat' => array('min' => self::MIN_REPEAT_VALUE, 'max' => self::MAX_REPEAT_VALUE, 'default' => self::DEFAULT_REPEAT),
            'speed' => array('min' => self::MIN_SPEED_VALUE, 'max' => self::MAX_SPEED_VALUE, 'default' => self::DEFAULT_SPEED),
            'mobile_speed_multiplier' => array('min' => self::MIN_SPEED_MULTIPLIER, 'max' => self::MAX_SPEED_MULTIPLIER, 'default' => self::DEFAULT_MOBILE_SPEED_MULTIPLIER),
            'tablet_speed_multiplier' => array('min' => self::MIN_SPEED_MULTIPLIER, 'max' => self::MAX_SPEED_MULTIPLIER, 'default' => self::DEFAULT_TABLET_SPEED_MULTIPLIER),
            'font_size' => array('min' => self::MIN_FONT_SIZE, 'max' => self::MAX_FONT_SIZE, 'default' => self::DEFAULT_FONT_SIZE),
            'border_width' => array('min' => self::MIN_BORDER_WIDTH, 'max' => self::MAX_BORDER_WIDTH, 'default' => self::DEFAULT_BORDER_WIDTH),
            'button_border_width' => array('min' => self::MIN_BUTTON_BORDER_WIDTH, 'max' => self::MAX_BUTTON_BORDER_WIDTH, 'default' => self::DEFAULT_BUTTON_BORDER_WIDTH),
            'button_border_radius' => array('min' => self::MIN_BUTTON_BORDER_RADIUS, 'max' => self::MAX_BUTTON_BORDER_RADIUS, 'default' => self::DEFAULT_BUTTON_BORDER_RADIUS),
            'button_padding' => array('min' => self::MIN_BUTTON_PADDING, 'max' => self::MAX_BUTTON_PADDING, 'default' => self::DEFAULT_BUTTON_PADDING),
            'button_font_size' => array('min' => self::MIN_BUTTON_FONT_SIZE, 'max' => self::MAX_BUTTON_FONT_SIZE, 'default' => self::DEFAULT_BUTTON_FONT_SIZE)
        );
    }
    
    /**
     * Check if a font family is allowed
     * 
     * Validates whether a font family is in the allowed list to prevent
     * security issues and ensure compatibility.
     * 
     * @since 2.1.0
     * @param string $font Font family name to validate
     * @return bool True if the font family is allowed
     */
    public static function is_allowed_font_family($font) {
        return in_array($font, self::ALLOWED_FONT_FAMILIES);
    }
    
    /**
     * Check if a font weight is allowed
     * 
     * Validates whether a font weight is in the allowed list to ensure
     * proper CSS rendering.
     * 
     * @since 2.1.0
     * @param string $weight Font weight value to validate
     * @return bool True if the font weight is allowed
     */
    public static function is_allowed_font_weight($weight) {
        return in_array($weight, self::ALLOWED_FONT_WEIGHTS);
    }
    
    /**
     * Check if a border style is allowed
     * 
     * Validates whether a border style is in the allowed list to ensure
     * proper CSS rendering and prevent security issues.
     * 
     * @since 2.1.0
     * @param string $style Border style value to validate
     * @return bool True if the border style is allowed
     */
    public static function is_allowed_border_style($style) {
        return in_array($style, self::ALLOWED_BORDER_STYLES);
    }
    
    /**
     * Check if a protocol is dangerous
     * 
     * Validates URLs to detect potentially dangerous protocols that could
     * be used for security attacks.
     * 
     * @since 2.1.0
     * @param string $url URL to check for dangerous protocols
     * @return bool True if the URL contains a dangerous protocol
     */
    public static function is_dangerous_protocol($url) {
        foreach (self::DANGEROUS_PROTOCOLS as $protocol) {
            if (stripos($url, $protocol) === 0) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get cache key for settings
     * 
     * Generates a unique cache key for settings based on blog ID to support
     * multisite installations.
     * 
     * @since 2.1.0
     * @param int|null $blog_id Blog ID (uses current blog if null)
     * @return string Unique cache key for settings
     */
    public static function get_settings_cache_key($blog_id = null) {
        if ($blog_id === null) {
            $blog_id = get_current_blog_id();
        }
        return self::CACHE_SETTINGS_PREFIX . $blog_id;
    }
    
    /**
     * Get cache key for banner HTML
     * 
     * Generates a unique cache key for banner HTML based on settings hash
     * and blog ID to support multisite installations.
     * 
     * @since 2.1.0
     * @param string $settings_hash Hash of settings used to generate banner
     * @param int|null $blog_id Blog ID (uses current blog if null)
     * @return string Unique cache key for banner HTML
     */
    public static function get_banner_cache_key($settings_hash, $blog_id = null) {
        if ($blog_id === null) {
            $blog_id = get_current_blog_id();
        }
        return self::CACHE_BANNER_PREFIX . $settings_hash . '_' . $blog_id;
    }
}
