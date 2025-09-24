<?php
/*
Plugin Name: CA Banners
Plugin URI: https://github.com/clientamp/ca-banners
Description: Professional WordPress banner plugin with customizable scrolling messages, advanced scheduling, page targeting, and image support. Perfect for promotions, announcements, and site-wide notices.
Version: 2.0.0
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

// Define plugin constants
define('CA_BANNERS_VERSION', '2.0.0');
define('CA_BANNERS_PLUGIN_FILE', __FILE__);
define('CA_BANNERS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CA_BANNERS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CA_BANNERS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Load the main plugin class
require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-ca-banners.php';

/**
 * Initialize the plugin
 */
function ca_banners_init() {
    // Debug: Check if plugin is loading
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('CA Banners: Plugin initialization started');
    }
    
    return CA_Banners::get_instance();
}

// Initialize the plugin
add_action('plugins_loaded', 'ca_banners_init');
