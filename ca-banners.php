<?php
/*
Plugin Name: Scrolling Banners
Plugin URI: https://github.com/clientamp/ca-banners
Description: Professional WordPress banner plugin with customizable scrolling messages, advanced scheduling, page targeting, and image support. Perfect for promotions, announcements, and site-wide notices.
Version: 1.0.0
Author: clientamp
Author URI: https://clientamp.com/
Requires at least: 4.0
Tested up to: 6.4
Requires PHP: 7.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ca-banners
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load the main plugin class first
require_once plugin_dir_path(__FILE__) . 'includes/class-constants.php';

// Define plugin constants
define('CA_BANNERS_VERSION', CA_Banners_Constants::PLUGIN_VERSION);
define('CA_BANNERS_PLUGIN_FILE', __FILE__);
define('CA_BANNERS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CA_BANNERS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CA_BANNERS_PLUGIN_BASENAME', plugin_basename(__FILE__));
require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-ca-banners.php';
require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-error-handler.php';

/**
 * Initialize the CA Banners plugin
 * 
 * This function is called on the 'plugins_loaded' action hook to initialize
 * the plugin after WordPress has loaded all plugins. It returns the singleton
 * instance of the CA_Banners class.
 * 
 * @since 1.2.7
 * @return CA_Banners The singleton instance of the CA_Banners class
 */
function ca_banners_init() {
    // Debug: Check if plugin is loading
    if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('view_ca_banners')) {
        error_log('CA Banners: Plugin initialization started');
    }
    
    return CA_Banners::get_instance();
}

// Initialize the plugin
add_action('plugins_loaded', 'ca_banners_init');
