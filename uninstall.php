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

// Remove plugin options
delete_option('banner_plugin_settings');

// Remove any transients
delete_transient('ca_banners_cache');

// Clear any scheduled hooks
wp_clear_scheduled_hook('ca_banners_cleanup');

// Fire uninstall action for other plugins to hook into
do_action('ca_banners_uninstall');
