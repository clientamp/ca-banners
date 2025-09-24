<?php
/*
Plugin Name: CA Banners Test
Description: Simple test to check if plugin loads
Version: 2.0.0
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Simple test banner that should always show
add_action('wp_head', function() {
    echo '<script>';
    echo 'console.log("CA Banners Test: Plugin loaded successfully");';
    echo 'var testBanner = document.createElement("div");';
    echo 'testBanner.style.cssText = "position: fixed; top: 0; left: 0; width: 100%; background: red; color: white; padding: 10px; text-align: center; z-index: 999999; font-weight: bold;";';
    echo 'testBanner.innerHTML = "CA BANNERS TEST - Plugin is working!";';
    echo 'document.body.insertBefore(testBanner, document.body.firstChild);';
    echo '</script>';
}, 1);

// Also add to admin
add_action('admin_notices', function() {
    echo '<div class="notice notice-success"><p><strong>CA Banners Test:</strong> Plugin is loaded and working!</p></div>';
});
