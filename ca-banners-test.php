<?php
/*
Plugin Name: CA Banners Test
Description: Minimal test version to debug banner display
Version: 1.0.0
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Simple test banner
add_action('wp_head', function() {
    $settings = get_option('banner_plugin_settings');
    
    if ($settings && isset($settings['enabled']) && $settings['enabled'] && !empty($settings['message'])) {
        echo '<script>';
        echo 'console.log("CA Banners Test: Banner should display");';
        echo 'var testBanner = document.createElement("div");';
        echo 'testBanner.style.cssText = "position: fixed; top: 0; left: 0; width: 100%; background: red; color: white; padding: 10px; text-align: center; z-index: 999999;";';
        echo 'testBanner.innerHTML = "TEST BANNER: ' . esc_js($settings['message']) . '";';
        echo 'document.body.insertBefore(testBanner, document.body.firstChild);';
        echo '</script>';
    } else {
        echo '<script>console.log("CA Banners Test: No banner settings found");</script>';
    }
}, 1);
