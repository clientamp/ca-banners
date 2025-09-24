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

// Simple banner function (like original)
function ca_banners_render_banner() {
    $settings = get_option('banner_plugin_settings');
    
    if (!$settings || !isset($settings['enabled']) || !$settings['enabled'] || empty($settings['message'])) {
        return;
    }
    
    $message = $settings['message'];
    $repeat = isset($settings['repeat']) ? intval($settings['repeat']) : 10;
    $background_color = isset($settings['background_color']) ? $settings['background_color'] : '#729946';
    $text_color = isset($settings['text_color']) ? $settings['text_color'] : '#000000';
    $font_size = isset($settings['font_size']) ? intval($settings['font_size']) : 16;
    $font_family = isset($settings['font_family']) ? $settings['font_family'] : 'Arial';
    $border_width = isset($settings['border_width']) ? intval($settings['border_width']) : 0;
    $border_style = isset($settings['border_style']) ? $settings['border_style'] : 'solid';
    $border_color = isset($settings['border_color']) ? $settings['border_color'] : '#000000';
    $disable_mobile = isset($settings['disable_mobile']) ? $settings['disable_mobile'] : false;
    
    // Create repeated message
    $repeated_message = '';
    for ($i = 0; $i < $repeat; $i++) {
        $repeated_message .= $message . ' &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; ';
    }
    
    echo '<script>';
    echo 'var caBannerConfig = {';
    echo 'message: ' . json_encode($repeated_message) . ',';
    echo 'backgroundColor: "' . esc_js($background_color) . '",';
    echo 'textColor: "' . esc_js($text_color) . '",';
    echo 'fontSize: ' . $font_size . ',';
    echo 'fontFamily: "' . esc_js($font_family) . '",';
    echo 'borderWidth: ' . $border_width . ',';
    echo 'borderStyle: "' . esc_js($border_style) . '",';
    echo 'borderColor: "' . esc_js($border_color) . '",';
    echo 'disableMobile: ' . ($disable_mobile ? 'true' : 'false');
    echo '};';
    ?>
    
    (function() {
        'use strict';
        
        // Mobile check
        if (caBannerConfig.disableMobile && window.matchMedia && window.matchMedia("(max-width: 768px)").matches) {
            return;
        }
        
        function createBanner() {
            // Check if banner already exists
            if (document.querySelector('.ca-banner-container')) {
                return;
            }
            
            var banner = document.createElement("div");
            banner.className = "ca-banner-container";
            banner.setAttribute('data-ca-banner', 'true');
            
            var bannerContent = document.createElement("div");
            bannerContent.className = "ca-banner-content";
            bannerContent.innerHTML = caBannerConfig.message;
            
            // Apply inline styles
            banner.style.cssText = [
                'position: relative !important',
                'top: 0 !important',
                'left: 0 !important',
                'width: 100% !important',
                'background-color: ' + caBannerConfig.backgroundColor + ' !important',
                'color: ' + caBannerConfig.textColor + ' !important',
                'padding: 10px !important',
                'text-align: center !important',
                'z-index: 999999 !important',
                'overflow: hidden !important',
                'font-weight: 600 !important',
                'font-size: ' + caBannerConfig.fontSize + 'px !important',
                'font-family: "' + caBannerConfig.fontFamily + '", sans-serif !important',
                'border-top: ' + caBannerConfig.borderWidth + 'px ' + caBannerConfig.borderStyle + ' ' + caBannerConfig.borderColor + ' !important',
                'border-bottom: ' + caBannerConfig.borderWidth + 'px ' + caBannerConfig.borderStyle + ' ' + caBannerConfig.borderColor + ' !important',
                'margin: 0 !important',
                'box-shadow: none !important'
            ].join('; ');
            
            bannerContent.style.display = 'inline-block';
            bannerContent.style.whiteSpace = 'nowrap';
            bannerContent.style.margin = '0';
            bannerContent.style.padding = '0';
            
            // Add CSS animation
            if (!document.querySelector('#ca-banner-animation-style')) {
                var style = document.createElement('style');
                style.id = 'ca-banner-animation-style';
                style.textContent = '@keyframes ca-banner-marquee { 0% { transform: translateX(0%); } 100% { transform: translateX(-100%); } } .ca-banner-content { animation: ca-banner-marquee 120s linear infinite !important; }';
                document.head.appendChild(style);
            }
            
            banner.appendChild(bannerContent);
            
            // Insert banner at the beginning of body
            if (document.body) {
                document.body.insertBefore(banner, document.body.firstChild);
                console.log('CA Banners: Banner displayed successfully');
            } else {
                // Fallback: wait for body to be ready
                setTimeout(createBanner, 100);
            }
        }
        
        // Try multiple initialization methods
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', createBanner);
        } else {
            createBanner();
        }
        
        // Fallback for themes that might interfere
        setTimeout(createBanner, 100);
        setTimeout(createBanner, 500);
        
        // Also try on window load as final fallback
        window.addEventListener('load', createBanner);
    })();
    
    <?php
    echo '</script>';
}

// Hook the banner rendering
add_action('wp_head', 'ca_banners_render_banner', 1);

// Add admin menu (simplified)
function ca_banners_admin_menu() {
    add_menu_page(
        'CA Banners',
        'CA Banners',
        'manage_options',
        'ca-banners',
        'ca_banners_settings_page',
        'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h18c1.1 0 2 .9 2 2v14c0 1.1-.9 2-2 2H3c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2zm0 2v12h18V5H3zm2 2h14v2H5V7zm0 4h14v2H5v-2zm0 4h10v2H5v-2z"/></svg>')
    );
}
add_action('admin_menu', 'ca_banners_admin_menu');

// Settings page (simplified)
function ca_banners_settings_page() {
    if (isset($_POST['submit'])) {
        $settings = array(
            'enabled' => isset($_POST['enabled']) ? 1 : 0,
            'message' => wp_kses_post($_POST['message']), // Allow HTML/CSS
            'repeat' => intval($_POST['repeat']),
            'background_color' => sanitize_hex_color($_POST['background_color']),
            'text_color' => sanitize_hex_color($_POST['text_color']),
            'font_size' => intval($_POST['font_size']),
            'font_family' => sanitize_text_field($_POST['font_family']),
            'border_width' => intval($_POST['border_width']),
            'border_style' => sanitize_text_field($_POST['border_style']),
            'border_color' => sanitize_hex_color($_POST['border_color']),
            'disable_mobile' => isset($_POST['disable_mobile']) ? 1 : 0,
        );
        update_option('banner_plugin_settings', $settings);
        echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
    }
    
    $settings = get_option('banner_plugin_settings', array());
    $enabled = isset($settings['enabled']) ? $settings['enabled'] : 0;
    $message = isset($settings['message']) ? $settings['message'] : '';
    $repeat = isset($settings['repeat']) ? $settings['repeat'] : 10;
    $background_color = isset($settings['background_color']) ? $settings['background_color'] : '#729946';
    $text_color = isset($settings['text_color']) ? $settings['text_color'] : '#000000';
    $font_size = isset($settings['font_size']) ? $settings['font_size'] : 16;
    $font_family = isset($settings['font_family']) ? $settings['font_family'] : 'Arial';
    $border_width = isset($settings['border_width']) ? $settings['border_width'] : 0;
    $border_style = isset($settings['border_style']) ? $settings['border_style'] : 'solid';
    $border_color = isset($settings['border_color']) ? $settings['border_color'] : '#000000';
    $disable_mobile = isset($settings['disable_mobile']) ? $settings['disable_mobile'] : 0;
    
    ?>
    <div class="wrap">
        <h1>CA Banners Settings</h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row">Enable Banner</th>
                    <td><input type="checkbox" name="enabled" value="1" <?php checked($enabled, 1); ?>></td>
                </tr>
                <tr>
                    <th scope="row">Banner Message</th>
                    <td><textarea name="message" rows="4" cols="50"><?php echo esc_html($message); ?></textarea>
                    <p class="description">Supports HTML tags and inline CSS. Examples: <code>&lt;strong&gt;Bold&lt;/strong&gt;</code>, <code>&lt;span style="color: red;"&gt;Red text&lt;/span&gt;</code></p></td>
                </tr>
                <tr>
                    <th scope="row">Repeat Count</th>
                    <td><input type="number" name="repeat" value="<?php echo esc_attr($repeat); ?>" min="1" max="100"></td>
                </tr>
                <tr>
                    <th scope="row">Background Color</th>
                    <td><input type="color" name="background_color" value="<?php echo esc_attr($background_color); ?>"></td>
                </tr>
                <tr>
                    <th scope="row">Text Color</th>
                    <td><input type="color" name="text_color" value="<?php echo esc_attr($text_color); ?>"></td>
                </tr>
                <tr>
                    <th scope="row">Font Size</th>
                    <td><input type="number" name="font_size" value="<?php echo esc_attr($font_size); ?>" min="10" max="40"></td>
                </tr>
                <tr>
                    <th scope="row">Font Family</th>
                    <td>
                        <select name="font_family">
                            <option value="Arial" <?php selected($font_family, 'Arial'); ?>>Arial</option>
                            <option value="Helvetica" <?php selected($font_family, 'Helvetica'); ?>>Helvetica</option>
                            <option value="Times New Roman" <?php selected($font_family, 'Times New Roman'); ?>>Times New Roman</option>
                            <option value="Georgia" <?php selected($font_family, 'Georgia'); ?>>Georgia</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Border Width</th>
                    <td><input type="number" name="border_width" value="<?php echo esc_attr($border_width); ?>" min="0" max="10"></td>
                </tr>
                <tr>
                    <th scope="row">Border Style</th>
                    <td>
                        <select name="border_style">
                            <option value="solid" <?php selected($border_style, 'solid'); ?>>Solid</option>
                            <option value="dashed" <?php selected($border_style, 'dashed'); ?>>Dashed</option>
                            <option value="dotted" <?php selected($border_style, 'dotted'); ?>>Dotted</option>
                            <option value="none" <?php selected($border_style, 'none'); ?>>None</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Border Color</th>
                    <td><input type="color" name="border_color" value="<?php echo esc_attr($border_color); ?>"></td>
                </tr>
                <tr>
                    <th scope="row">Disable on Mobile</th>
                    <td><input type="checkbox" name="disable_mobile" value="1" <?php checked($disable_mobile, 1); ?>></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
