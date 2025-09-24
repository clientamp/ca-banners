<?php
/**
 * Uninstall file for CA Banners plugin
 *
 * @package CA_Banners
 * @since 1.2.7
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load the main plugin class to access capability methods
require_once plugin_dir_path(__FILE__) . 'includes/class-constants.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-ca-banners.php';

// Remove plugin options
delete_option(CA_Banners_Constants::OPTION_NAME);

// Remove any transients
delete_transient(CA_Banners_Constants::get_settings_cache_key());

// Clear any scheduled hooks
wp_clear_scheduled_hook(CA_Banners_Constants::HOOK_CLEANUP);

// Remove custom capabilities
$ca_banners = new CA_Banners();
$ca_banners->remove_custom_capabilities();

// Fire uninstall action for other plugins to hook into
do_action(CA_Banners_Constants::HOOK_UNINSTALL);
