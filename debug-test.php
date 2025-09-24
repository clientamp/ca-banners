<?php
/**
 * Simple test to check if CA Banners plugin is working
 * Add this to your WordPress theme's functions.php temporarily to test
 */

// Test if CA Banners is loaded
add_action('wp_head', function() {
    if (class_exists('CA_Banners')) {
        echo '<!-- CA Banners plugin is loaded -->';
        
        // Test if we can get settings
        $settings = get_option('banner_plugin_settings');
        if ($settings) {
            echo '<!-- CA Banners settings found: ' . print_r($settings, true) . ' -->';
        } else {
            echo '<!-- CA Banners settings NOT found -->';
        }
        
        // Test if frontend class exists
        if (class_exists('CA_Banners_Frontend')) {
            echo '<!-- CA Banners Frontend class exists -->';
        } else {
            echo '<!-- CA Banners Frontend class does NOT exist -->';
        }
        
    } else {
        echo '<!-- CA Banners plugin is NOT loaded -->';
    }
}, 1);
