<?php
/*
Plugin Name: CA Banners
Plugin URI: https://github.com/clientamp/ca-banners
Description: Professional WordPress banner plugin with customizable scrolling messages, advanced scheduling, page targeting, and image support. Perfect for promotions, announcements, and site-wide notices.
Version: 1.2.6
Author: clientamp
Author URI: https://clientamp.com/
Requires at least: 4.0
Tested up to: 6.4
Requires PHP: 7.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ca-banners
*/

// Add menu item to WordPress admin panel
/**
 * Add CA Banners menu item to WordPress admin
 * @return void
 */
function banner_plugin_menu() {
    add_menu_page('CA Banners', 'CA Banners', 'manage_options', 'banner-plugin', 'banner_plugin_settings_page', 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h18c1.1 0 2 .9 2 2v14c0 1.1-.9 2-2 2H3c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2zm0 2v12h18V5H3zm2 2h14v2H5V7zm0 4h14v2H5v-2zm0 4h10v2H5v-2z"/></svg>'));
}
add_action('admin_menu', 'banner_plugin_menu');

// Display settings page
/**
 * Display the plugin settings page
 * @return void
 */
function banner_plugin_settings_page() {
    // Add help tab
    $screen = get_current_screen();
    $help_content = '        <h3>Quick Start Guide</h3>
        <ol>
            <li><strong>Enable Banner:</strong> Toggle the "Enable Banner" switch</li>
            <li><strong>Add Message:</strong> Enter your banner message in the text area (supports HTML/CSS formatting)</li>
            <li><strong>Choose Display:</strong> Select "Display sitewide" or "Display on specific pages only"</li>
            <li><strong>Set Pages:</strong> Use Include Pages and/or Exclude Pages as needed</li>
            <li><strong>Customize Style:</strong> Adjust colors, fonts, and borders</li>
            <li><strong>Save Settings:</strong> Click "Save Changes"</li>
        </ol>
        <h3>Common Use Cases</h3>
        <ul>
            <li><strong>Promotional Banner:</strong> Choose "Display sitewide", add your promotion message, exclude checkout pages</li>
            <li><strong>Page-Specific Notice:</strong> Choose "Display on specific pages only", add specific URLs like /about-us/, /contact/</li>
            <li><strong>Call-to-Action Banner:</strong> Enable the button feature to add clickable CTAs like "Shop Now", "Learn More", or "Get Started"</li>
            <li><strong>Rich Text Banner:</strong> Use HTML tags and inline CSS for advanced formatting like <code>&lt;strong&gt;Bold&lt;/strong&gt;</code>, <code>&lt;span style="color: red;"&gt;Colored text&lt;/span&gt;</code>, or <code>&lt;em&gt;Italic&lt;/em&gt;</code></li>
            <li><strong>Complex Targeting:</strong> Use both Include and Exclude pages for precise control</li>
            <li><strong>Mobile-Friendly:</strong> Check "Disable on Mobile" if banner does not work well on phones</li>
        </ul>';
    
    $screen->add_help_tab(array(
        'id' => 'ca-banners-help',
        'title' => 'Quick Start Guide',
        'content' => $help_content
    ));
    
    $troubleshooting_content = '<h3>Troubleshooting</h3>
        <h4>Banner Not Showing?</h4>
        <ul>
            <li>Make sure "Enable Banner" is checked</li>
            <li>Check if you have a message entered</li>
            <li>Verify your display settings (sitewide or specific pages)</li>
            <li>Check if start/end dates are set correctly</li>
        </ul>
        <h4>Banner Showing on Wrong Pages?</h4>
        <ul>
            <li>Add unwanted pages to "Exclude Pages" to hide the banner</li>
            <li>Check your "Include Pages" URLs if using specific page mode</li>
            <li>URLs should start with / and end with / (e.g., /about-us/)</li>
            <li>Exclude Pages always takes precedence over Include Pages</li>
        </ul>
        <h4>Button Not Working?</h4>
        <ul>
            <li>Make sure "Enable Button" is checked</li>
            <li>Enter both button text and button link</li>
            <li>Check that the link URL is valid (starts with http:// or https://)</li>
            <li>Button will only appear if banner is enabled and has a message</li>
        </ul>';
    
    $screen->add_help_tab(array(
        'id' => 'ca-banners-troubleshooting',
        'title' => 'Troubleshooting',
        'content' => $troubleshooting_content
    ));
    
    ?>
    <div class="wrap">
        <h1>CA Banners Settings</h1>
        <div class="notice notice-info">
            <p><strong>Need help?</strong> Check the "Quick Start Guide" tab above for step-by-step instructions.</p>
        </div>
        <form method="post" action="options.php">
            <?php
            settings_fields('banner_plugin_settings');
            do_settings_sections('banner-plugin');
            submit_button();
            echo '</form>';
            echo '</div>';
}

// Register plugin settings
/**
 * Register all plugin settings with WordPress
 * @return void
 */
function banner_plugin_register_settings() {
    register_setting('banner_plugin_settings', 'banner_plugin_settings', array(
        'sanitize_callback' => 'banner_plugin_sanitize_settings'
    ));

    // Basic Settings Section
    add_settings_section('banner_basic_section', 'Basic Settings', 'banner_basic_section_callback', 'banner-plugin');
    add_settings_field('banner_enabled', 'Enable Banner', 'banner_plugin_enabled_callback', 'banner-plugin', 'banner_basic_section');
    add_settings_field('banner_message', 'Banner Message', 'banner_plugin_message_callback', 'banner-plugin', 'banner_basic_section');
    add_settings_field('banner_repeat', 'Message Repeats', 'banner_plugin_repeat_callback', 'banner-plugin', 'banner_basic_section');

    // Display Settings Section
    add_settings_section('banner_display_section', 'Display Settings', 'banner_display_section_callback', 'banner-plugin');
    add_settings_field('banner_sitewide', 'Display Sitewide', 'banner_plugin_sitewide_callback', 'banner-plugin', 'banner_display_section');
    add_settings_field('banner_urls', 'Display on Pages', 'banner_plugin_urls_callback', 'banner-plugin', 'banner_display_section');
    add_settings_field('banner_exclude_urls', 'Exclude on Pages', 'banner_plugin_exclude_urls_callback', 'banner-plugin', 'banner_display_section');
    add_settings_field('banner_disable_mobile', 'Disable on Mobile', 'banner_plugin_disable_mobile_callback', 'banner-plugin', 'banner_display_section');

    // Styling Settings Section
    add_settings_section('banner_styling_section', 'Styling Settings', 'banner_styling_section_callback', 'banner-plugin');
    add_settings_field('banner_background_color', 'Background Color', 'banner_plugin_background_color_callback', 'banner-plugin', 'banner_styling_section');
    add_settings_field('banner_text_color', 'Text Color', 'banner_plugin_text_color_callback', 'banner-plugin', 'banner_styling_section');
    add_settings_field('banner_font_size', 'Font Size', 'banner_plugin_font_size_callback', 'banner-plugin', 'banner_styling_section');
    add_settings_field('banner_font_family', 'Font Family', 'banner_plugin_font_family_callback', 'banner-plugin', 'banner_styling_section');
    add_settings_field('banner_border_width', 'Border Width', 'banner_plugin_border_width_callback', 'banner-plugin', 'banner_styling_section');
    add_settings_field('banner_border_style', 'Border Style', 'banner_plugin_border_style_callback', 'banner-plugin', 'banner_styling_section');
    add_settings_field('banner_border_color', 'Border Color', 'banner_plugin_border_color_callback', 'banner-plugin', 'banner_styling_section');

    // Scheduling Settings Section
    add_settings_section('banner_scheduling_section', 'Scheduling Settings', 'banner_scheduling_section_callback', 'banner-plugin');
    add_settings_field('banner_start_date', 'Banner Start Date', 'banner_plugin_start_date_callback', 'banner-plugin', 'banner_scheduling_section');
    add_settings_field('banner_end_date', 'Banner End Date', 'banner_plugin_end_date_callback', 'banner-plugin', 'banner_scheduling_section');

    // Image Banner Settings Section
    add_settings_section('banner_image_section', 'Image Banner Settings', 'banner_image_section_callback', 'banner-plugin');
    add_settings_field('banner_image', 'Banner Image', 'banner_plugin_image_callback', 'banner-plugin', 'banner_image_section');
    add_settings_field('banner_image_start_date', 'Image Start Date', 'banner_plugin_image_start_date_callback', 'banner-plugin', 'banner_image_section');
    add_settings_field('banner_image_end_date', 'Image End Date', 'banner_plugin_image_end_date_callback', 'banner-plugin', 'banner_image_section');

}
add_action('admin_init', 'banner_plugin_register_settings');

/**
 * Sanitize plugin settings
 * @param array $input Raw input data
 * @return array Sanitized data
 */
function banner_plugin_sanitize_settings($input) {
    $sanitized = array();
    
    // Boolean fields
    $boolean_fields = array('enabled', 'sitewide', 'disable_mobile');
    foreach ($boolean_fields as $field) {
        $sanitized[$field] = isset($input[$field]) ? (bool) $input[$field] : false;
    }
    
    // Text fields - Allow HTML for banner message
    $sanitized['message'] = isset($input['message']) ? wp_kses_post($input['message']) : '';
    $sanitized['urls'] = isset($input['urls']) ? sanitize_textarea_field($input['urls']) : '';
    $sanitized['exclude_urls'] = isset($input['exclude_urls']) ? sanitize_textarea_field($input['exclude_urls']) : '';
    $sanitized['image'] = isset($input['image']) ? esc_url_raw($input['image']) : '';
    
    // Button fields
    $sanitized['button_text'] = isset($input['button_text']) ? sanitize_text_field($input['button_text']) : '';
    $sanitized['button_link'] = isset($input['button_link']) ? esc_url_raw($input['button_link']) : '';
    
    // Numeric fields with validation
    $sanitized['repeat'] = isset($input['repeat']) ? max(1, min(100, intval($input['repeat']))) : 10;
    $sanitized['font_size'] = isset($input['font_size']) ? max(10, min(40, intval($input['font_size']))) : 16;
    $sanitized['border_width'] = isset($input['border_width']) ? max(0, min(10, intval($input['border_width']))) : 0;
    
    // Button numeric fields
    $sanitized['button_border_width'] = isset($input['button_border_width']) ? max(0, min(10, intval($input['button_border_width']))) : 0;
    $sanitized['button_border_radius'] = isset($input['button_border_radius']) ? max(0, min(50, intval($input['button_border_radius']))) : 4;
    $sanitized['button_padding'] = isset($input['button_padding']) ? max(0, min(50, intval($input['button_padding']))) : 8;
    $sanitized['button_font_size'] = isset($input['button_font_size']) ? max(8, min(24, intval($input['button_font_size']))) : 14;
    
    // Color fields
    $sanitized['background_color'] = isset($input['background_color']) ? sanitize_hex_color($input['background_color']) : '#729946';
    $sanitized['text_color'] = isset($input['text_color']) ? sanitize_hex_color($input['text_color']) : '#000000';
    $sanitized['border_color'] = isset($input['border_color']) ? sanitize_hex_color($input['border_color']) : '#000000';
    
    // Button color fields
    $sanitized['button_color'] = isset($input['button_color']) ? sanitize_hex_color($input['button_color']) : '#ce7a31';
    $sanitized['button_text_color'] = isset($input['button_text_color']) ? sanitize_hex_color($input['button_text_color']) : '#ffffff';
    $sanitized['button_border_color'] = isset($input['button_border_color']) ? sanitize_hex_color($input['button_border_color']) : '#ce7a31';
    
    // Font family validation
    $allowed_fonts = array('Arial', 'Helvetica', 'Times New Roman', 'Georgia', 'Courier New', 'Verdana', 'Tahoma', 'Trebuchet MS', 'Impact', 'Comic Sans MS', 'Raleway');
    $sanitized['font_family'] = isset($input['font_family']) && in_array($input['font_family'], $allowed_fonts) ? $input['font_family'] : 'Arial';
    
    // Border style validation
    $allowed_styles = array('solid', 'dashed', 'dotted', 'double', 'none');
    $sanitized['border_style'] = isset($input['border_style']) && in_array($input['border_style'], $allowed_styles) ? $input['border_style'] : 'solid';
    
    // Button font weight validation
    $allowed_weights = array('normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900');
    $sanitized['button_font_weight'] = isset($input['button_font_weight']) && in_array($input['button_font_weight'], $allowed_weights) ? $input['button_font_weight'] : '600';
    
    // Date fields
    $sanitized['start_date'] = isset($input['start_date']) ? sanitize_text_field($input['start_date']) : '';
    $sanitized['end_date'] = isset($input['end_date']) ? sanitize_text_field($input['end_date']) : '';
    $sanitized['image_start_date'] = isset($input['image_start_date']) ? sanitize_text_field($input['image_start_date']) : '';
    $sanitized['image_end_date'] = isset($input['image_end_date']) ? sanitize_text_field($input['image_end_date']) : '';
    
    return $sanitized;
}

/**
 * Section callback functions with helpful descriptions
 */
function banner_basic_section_callback() {
    echo '<p>Configure the basic banner settings including enabling the banner and setting your message.</p>';
}

function banner_display_section_callback() {
    echo '<p>Control where and when your banner appears on your website.</p>';
}

function banner_styling_section_callback() {
    echo '<p>Customize the appearance of your banner with colors, fonts, and borders.</p>';
}

function banner_scheduling_section_callback() {
    echo '<p>Schedule when your banner should start and stop displaying.</p>';
}

function banner_image_section_callback() {
    echo '<p>Add an image banner with its own scheduling separate from the text banner.</p>';
}

function banner_button_section_callback() {
    echo '<p>Add a call-to-action button to your banner with customizable styling options.</p>';
}

// Callback functions for settings fields
function banner_plugin_enabled_callback() {
    $settings = get_option('banner_plugin_settings');
    $enabled = isset($settings['enabled']) ? $settings['enabled'] : false;
    
    echo '<div class="ca-banner-toggle-container">';
    echo '<label class="ca-banner-toggle">';
    echo '<input type="checkbox" name="banner_plugin_settings[enabled]" value="1"' . checked(1, $enabled, false) . '>';
    echo '<span class="ca-banner-toggle-slider"></span>';
    echo '</label>';
    echo '<span class="ca-banner-toggle-label">' . ($enabled ? 'Enabled' : 'Disabled') . '</span>';
    echo '</div>';
    echo '<p class="description">Toggle to activate or deactivate the banner on your website.</p>';
}

function banner_plugin_message_callback() {
    $settings = get_option('banner_plugin_settings');
    $message = isset($settings['message']) ? $settings['message'] : '';
    echo '<textarea name="banner_plugin_settings[message]" rows="4" cols="50" placeholder="Enter your banner message here...">' . esc_html($message) . '</textarea>';
    echo '<p class="description">Enter the text you want to display in your scrolling banner. This message will repeat across the banner.<br><strong>HTML/CSS Support:</strong> You can use HTML tags and inline CSS styles for formatting. Examples: <code>&lt;strong&gt;Bold text&lt;/strong&gt;</code>, <code>&lt;span style="color: red;"&gt;Red text&lt;/span&gt;</code>, <code>&lt;em&gt;Italic text&lt;/em&gt;</code></p>';
}

function banner_plugin_repeat_callback() {
    $settings = get_option('banner_plugin_settings');
    $repeat = isset($settings['repeat']) ? $settings['repeat'] : '10';
    echo '<input type="number" name="banner_plugin_settings[repeat]" value="' . esc_attr($repeat) . '" min="1" max="100">';
    echo '<p class="description">How many times to repeat your message across the banner (1-100). More repeats create a longer scrolling effect.</p>';
}

function banner_plugin_background_color_callback() {
    $settings = get_option('banner_plugin_settings');
    $background_color = isset($settings['background_color']) ? $settings['background_color'] : '#729946';
    echo '<input type="color" name="banner_plugin_settings[background_color]" value="' . esc_attr($background_color) . '">';
}

function banner_plugin_text_color_callback() {
    $settings = get_option('banner_plugin_settings');
    $text_color = isset($settings['text_color']) ? $settings['text_color'] : '#000000';
    echo '<input type="color" name="banner_plugin_settings[text_color]" value="' . esc_attr($text_color) . '">';
}

function banner_plugin_font_size_callback() {
    $settings = get_option('banner_plugin_settings');
    $font_size = isset($settings['font_size']) ? $settings['font_size'] : '16';
    echo '<input type="number" name="banner_plugin_settings[font_size]" value="' . esc_attr($font_size) . '" min="10" max="40">';
}

function banner_plugin_font_family_callback() {
    $settings = get_option('banner_plugin_settings');
    $font_family = isset($settings['font_family']) ? $settings['font_family'] : 'Arial';
    $font_options = array(
        'Arial' => 'Arial',
        'Helvetica' => 'Helvetica',
        'Times New Roman' => 'Times New Roman',
        'Georgia' => 'Georgia',
        'Courier New' => 'Courier New',
        'Verdana' => 'Verdana',
        'Tahoma' => 'Tahoma',
        'Trebuchet MS' => 'Trebuchet MS',
        'Impact' => 'Impact',
        'Comic Sans MS' => 'Comic Sans MS',
        'Raleway' => 'Raleway'
    );

    echo '<select name="banner_plugin_settings[font_family]">';
    foreach ($font_options as $value => $label) {
        $selected = ($font_family === $value) ? 'selected' : '';
        echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . $label . '</option>';
    }
    echo '</select>';
}

function banner_plugin_border_width_callback() {
    $settings = get_option('banner_plugin_settings');
    $border_width = isset($settings['border_width']) ? $settings['border_width'] : '0';
    echo '<input type="number" name="banner_plugin_settings[border_width]" value="' . esc_attr($border_width) . '" min="0" max="10">';
}

function banner_plugin_border_style_callback() {
    $settings = get_option('banner_plugin_settings');
    $border_style = isset($settings['border_style']) ? $settings['border_style'] : 'solid';
    $style_options = array(
        'solid' => 'Solid',
        'dashed' => 'Dashed',
        'dotted' => 'Dotted',
        'double' => 'Double',
        'none' => 'None'
    );

    echo '<select name="banner_plugin_settings[border_style]">';
    foreach ($style_options as $value => $label) {
        $selected = ($border_style === $value) ? 'selected' : '';
        echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . $label . '</option>';
    }
    echo '</select>';
}

function banner_plugin_border_color_callback() {
    $settings = get_option('banner_plugin_settings');
    $border_color = isset($settings['border_color']) ? $settings['border_color'] : '#000000';
    echo '<input type="color" name="banner_plugin_settings[border_color]" value="' . esc_attr($border_color) . '">';
}

function banner_plugin_disable_mobile_callback() {
    $settings = get_option('banner_plugin_settings');
    $disable_mobile = isset($settings['disable_mobile']) ? $settings['disable_mobile'] : false;
    echo '<input type="checkbox" name="banner_plugin_settings[disable_mobile]" value="1"' . checked(1, $disable_mobile, false) . '>';
}

function banner_plugin_start_date_callback() {
    $settings = get_option('banner_plugin_settings');
    $start_date = isset($settings['start_date']) ? $settings['start_date'] : '';
    echo '<input type="datetime-local" name="banner_plugin_settings[start_date]" value="' . esc_attr($start_date) . '">';
}

function banner_plugin_end_date_callback() {
    $settings = get_option('banner_plugin_settings');
    $end_date = isset($settings['end_date']) ? $settings['end_date'] : '';
    echo '<input type="datetime-local" name="banner_plugin_settings[end_date]" value="' . esc_attr($end_date) . '">';
}

function banner_plugin_urls_callback() {
    $settings = get_option('banner_plugin_settings');
    $urls = isset($settings['urls']) ? $settings['urls'] : '';
    echo '<textarea name="banner_plugin_settings[urls]" rows="4" cols="50" placeholder="/about-us/&#10;/contact/&#10;/products/">' . esc_textarea($urls) . '</textarea>';
    echo '<p class="description"><strong>Include Pages:</strong> Enter one URL per line to display the banner on specific pages. Use relative URLs like <code>/about-us/</code> or <code>/contact/</code>. Leave empty to show on all pages when not using sitewide mode.</p>';
}

function banner_plugin_image_callback() {
    $settings = get_option('banner_plugin_settings');
    $image = isset($settings['image']) ? $settings['image'] : '';
    echo '<input type="text" name="banner_plugin_settings[image]" value="' . esc_attr($image) . '" class="regular-text">';
    echo '<input type="button" class="button button-secondary" value="Upload Image" id="upload_image_button">';
    echo '<p class="description">Enter an image URL or use the upload button.</p>';
}

function banner_plugin_image_start_date_callback() {
    $settings = get_option('banner_plugin_settings');
    $image_start_date = isset($settings['image_start_date']) ? $settings['image_start_date'] : '';
    echo '<input type="datetime-local" name="banner_plugin_settings[image_start_date]" value="' . esc_attr($image_start_date) . '">';
}

function banner_plugin_image_end_date_callback() {
    $settings = get_option('banner_plugin_settings');
    $image_end_date = isset($settings['image_end_date']) ? $settings['image_end_date'] : '';
    echo '<input type="datetime-local" name="banner_plugin_settings[image_end_date]" value="' . esc_attr($image_end_date) . '">';
}

// Add callback functions for the new settings
function banner_plugin_sitewide_callback() {
    $settings = get_option('banner_plugin_settings');
    $sitewide = isset($settings['sitewide']) ? $settings['sitewide'] : false;
    
    echo '<fieldset>';
    echo '<label><input type="radio" name="banner_plugin_settings[sitewide]" value="1"' . checked(1, $sitewide, false) . '> Display sitewide (all pages)</label><br>';
    echo '<label><input type="radio" name="banner_plugin_settings[sitewide]" value="0"' . checked(0, $sitewide, false) . '> Display on specific pages only</label>';
    echo '</fieldset>';
    echo '<p class="description"><strong>Choose how you want to control banner visibility:</strong><br>';
    echo '• <strong>Sitewide:</strong> Banner appears on all pages except those you exclude<br>';
    echo '• <strong>Specific pages:</strong> Banner only appears on pages you specify</p>';
}

function banner_plugin_exclude_urls_callback() {
    $settings = get_option('banner_plugin_settings');
    $exclude_urls = isset($settings['exclude_urls']) ? $settings['exclude_urls'] : '';
    echo '<textarea name="banner_plugin_settings[exclude_urls]" rows="4" cols="50" placeholder="/checkout/&#10;/cart/&#10;/admin/">' . esc_textarea($exclude_urls) . '</textarea>';
    echo '<p class="description"><strong>Exclude Pages:</strong> Enter one URL per line to exclude the banner from specific pages. Use relative URLs like <code>/checkout/</code> or <code>/cart/</code>. Works with both sitewide and specific page modes.</p>';
}

// Button callback functions
function banner_plugin_button_enabled_callback() {
    $settings = get_option('banner_plugin_settings');
    $button_enabled = isset($settings['button_enabled']) ? $settings['button_enabled'] : false;
    
    echo '<div class="ca-banner-toggle-container">';
    echo '<label class="ca-banner-toggle">';
    echo '<input type="checkbox" name="banner_plugin_settings[button_enabled]" value="1"' . checked(1, $button_enabled, false) . '>';
    echo '<span class="ca-banner-toggle-slider"></span>';
    echo '</label>';
    echo '<span class="ca-banner-toggle-label">' . ($button_enabled ? 'Enabled' : 'Disabled') . '</span>';
    echo '</div>';
    echo '<p class="description">Enable a call-to-action button in your banner.</p>';
}

function banner_plugin_button_text_callback() {
    $settings = get_option('banner_plugin_settings');
    $button_text = isset($settings['button_text']) ? $settings['button_text'] : '';
    echo '<input type="text" name="banner_plugin_settings[button_text]" value="' . esc_attr($button_text) . '" class="regular-text" placeholder="Learn More">';
    echo '<p class="description">Enter the text to display on the button (e.g., "Shop Now", "Learn More", "Get Started").</p>';
}

function banner_plugin_button_link_callback() {
    $settings = get_option('banner_plugin_settings');
    $button_link = isset($settings['button_link']) ? $settings['button_link'] : '';
    echo '<input type="url" name="banner_plugin_settings[button_link]" value="' . esc_attr($button_link) . '" class="regular-text" placeholder="https://example.com">';
    echo '<p class="description">Enter the URL where the button should link to (e.g., https://example.com/shop).</p>';
}

function banner_plugin_button_color_callback() {
    $settings = get_option('banner_plugin_settings');
    $button_color = isset($settings['button_color']) ? $settings['button_color'] : '#ce7a31';
    echo '<input type="color" name="banner_plugin_settings[button_color]" value="' . esc_attr($button_color) . '">';
    echo '<p class="description">Choose the background color for the button.</p>';
}

function banner_plugin_button_text_color_callback() {
    $settings = get_option('banner_plugin_settings');
    $button_text_color = isset($settings['button_text_color']) ? $settings['button_text_color'] : '#ffffff';
    echo '<input type="color" name="banner_plugin_settings[button_text_color]" value="' . esc_attr($button_text_color) . '">';
    echo '<p class="description">Choose the text color for the button.</p>';
}

function banner_plugin_button_border_width_callback() {
    $settings = get_option('banner_plugin_settings');
    $button_border_width = isset($settings['button_border_width']) ? $settings['button_border_width'] : '0';
    echo '<input type="number" name="banner_plugin_settings[button_border_width]" value="' . esc_attr($button_border_width) . '" min="0" max="10">';
    echo '<p class="description">Set the border width for the button (0-10px).</p>';
}

function banner_plugin_button_border_color_callback() {
    $settings = get_option('banner_plugin_settings');
    $button_border_color = isset($settings['button_border_color']) ? $settings['button_border_color'] : '#ce7a31';
    echo '<input type="color" name="banner_plugin_settings[button_border_color]" value="' . esc_attr($button_border_color) . '">';
    echo '<p class="description">Choose the border color for the button.</p>';
}

function banner_plugin_button_border_radius_callback() {
    $settings = get_option('banner_plugin_settings');
    $button_border_radius = isset($settings['button_border_radius']) ? $settings['button_border_radius'] : '4';
    echo '<input type="number" name="banner_plugin_settings[button_border_radius]" value="' . esc_attr($button_border_radius) . '" min="0" max="50">';
    echo '<p class="description">Set the border radius for rounded corners (0-50px).</p>';
}

function banner_plugin_button_padding_callback() {
    $settings = get_option('banner_plugin_settings');
    $button_padding = isset($settings['button_padding']) ? $settings['button_padding'] : '8';
    echo '<input type="number" name="banner_plugin_settings[button_padding]" value="' . esc_attr($button_padding) . '" min="0" max="50">';
    echo '<p class="description">Set the internal padding for the button (0-50px).</p>';
}

function banner_plugin_button_font_size_callback() {
    $settings = get_option('banner_plugin_settings');
    $button_font_size = isset($settings['button_font_size']) ? $settings['button_font_size'] : '14';
    echo '<input type="number" name="banner_plugin_settings[button_font_size]" value="' . esc_attr($button_font_size) . '" min="8" max="24">';
    echo '<p class="description">Set the font size for the button text (8-24px).</p>';
}

function banner_plugin_button_font_weight_callback() {
    $settings = get_option('banner_plugin_settings');
    $button_font_weight = isset($settings['button_font_weight']) ? $settings['button_font_weight'] : '600';
    $weight_options = array(
        'normal' => 'Normal',
        'bold' => 'Bold',
        '100' => '100 (Thin)',
        '200' => '200 (Extra Light)',
        '300' => '300 (Light)',
        '400' => '400 (Regular)',
        '500' => '500 (Medium)',
        '600' => '600 (Semi Bold)',
        '700' => '700 (Bold)',
        '800' => '800 (Extra Bold)',
        '900' => '900 (Black)'
    );

    echo '<select name="banner_plugin_settings[button_font_weight]">';
    foreach ($weight_options as $value => $label) {
        $selected = ($button_font_weight === $value) ? 'selected' : '';
        echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . $label . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Choose the font weight for the button text.</p>';
}

// Enqueue admin scripts
function banner_plugin_admin_scripts() {
    wp_enqueue_media();
    add_action('admin_footer', 'banner_plugin_admin_footer_scripts');
    add_action('admin_head', 'banner_plugin_admin_styles');
}
add_action('admin_enqueue_scripts', 'banner_plugin_admin_scripts');

// Admin styles for toggle switch
function banner_plugin_admin_styles() {
    ?>
    <style>
    .ca-banner-toggle-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .ca-banner-toggle {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 22px;
    }
    
    .ca-banner-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .ca-banner-toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }
    
    .ca-banner-toggle-slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    .ca-banner-toggle input:checked + .ca-banner-toggle-slider {
        background-color: #2196F3;
    }
    
    .ca-banner-toggle input:checked + .ca-banner-toggle-slider:before {
        transform: translateX(18px);
    }
    
    .ca-banner-toggle-label {
        font-weight: 600;
        color: #333;
    }
    
    .ca-banner-toggle input:checked ~ .ca-banner-toggle-label {
        color: #2196F3;
    }
    </style>
    <?php
}

// Admin footer scripts
function banner_plugin_admin_footer_scripts() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var uploadButton = document.getElementById('upload_image_button');
        var imageInput = document.querySelector('input[name="banner_plugin_settings[image]"]');

        if (uploadButton) {
            uploadButton.addEventListener('click', function(e) {
                e.preventDefault();
                var image = wp.media({ 
                    title: 'Upload Image',
                    multiple: false
                }).open()
                .on('select', function(e){
                    var uploaded_image = image.state().get('selection').first();
                    var image_url = uploaded_image.toJSON().url;
                    if (imageInput) {
                        imageInput.value = image_url;
                    }
                });
            });
        }

        // Handle toggle switch label updates
        var enableToggle = document.querySelector('input[name="banner_plugin_settings[enabled]"]');
        var toggleLabel = document.querySelector('.ca-banner-toggle-label');
        
        if (enableToggle && toggleLabel) {
            enableToggle.addEventListener('change', function() {
                toggleLabel.textContent = this.checked ? 'Enabled' : 'Disabled';
            });
        }
        
        // Handle button toggle switch label updates
        var buttonToggle = document.querySelector('input[name="banner_plugin_settings[button_enabled]"]');
        var buttonToggleLabels = document.querySelectorAll('.ca-banner-toggle-label');
        
        if (buttonToggle && buttonToggleLabels.length > 1) {
            var buttonToggleLabel = buttonToggleLabels[1]; // Second toggle label
            buttonToggle.addEventListener('change', function() {
                buttonToggleLabel.textContent = this.checked ? 'Enabled' : 'Disabled';
            });
        }
    });
    </script>
    <?php
}

/**
 * Normalize URL for consistent comparison
 * @param string $url Raw URL to normalize
 * @return string Normalized URL
 */
function ca_banner_normalize_url($url) {
    $url = trim($url);
    
    // Handle empty or root URLs
    if ($url === '' || $url === '/') {
        return '/';
    }
    
    // Remove leading slash if present, then add trailing slash
    $url = ltrim($url, '/');
    $url = '/' . rtrim($url, '/') . '/';
    
    return $url;
}

/**
 * Process URL list from textarea input
 * @param string $url_list Raw URL list from textarea
 * @return array Array of normalized URLs
 */
function ca_banner_process_url_list($url_list) {
    if (empty($url_list)) {
        return array();
    }
    
    $urls = preg_split('/\r\n|\r|\n/', $url_list);
    $urls = array_map('ca_banner_normalize_url', $urls);
    $urls = array_filter($urls, function($url) {
        return !empty($url);
    });
    
    return array_unique($urls);
}

/**
 * Check if current URL matches any URL in the list (supports wildcards)
 * @param string $current_url Current normalized URL
 * @param array $url_list Array of URLs to match against
 * @return bool True if match found
 */
function ca_banner_url_matches($current_url, $url_list) {
    if (empty($url_list) || empty($current_url)) {
        return false;
    }
    
    foreach ($url_list as $url) {
        // Exact match
        if ($current_url === $url) {
            return true;
        }
        
        // Wildcard match (if URL ends with *)
        if (substr($url, -1) === '*') {
            $pattern = rtrim($url, '*');
            if (strpos($current_url, $pattern) === 0) {
                return true;
            }
        }
        
        // Partial match for subdirectories
        if (strpos($current_url, $url) === 0) {
            return true;
        }
    }
    
    return false;
}

/**
 * Validate banner settings and return sanitized values
 * @param array $settings Raw settings array
 * @return array Validated settings array
 */
function ca_banner_validate_settings($settings) {
    $validated = array();
    
    // Ensure required fields exist
    $validated['enabled'] = isset($settings['enabled']) ? (bool) $settings['enabled'] : false;
    $validated['message'] = isset($settings['message']) ? trim($settings['message']) : '';
    $validated['repeat'] = isset($settings['repeat']) ? max(1, min(100, intval($settings['repeat']))) : 10;
    
    // Only proceed if banner is enabled and has a message
    if (!$validated['enabled'] || empty($validated['message'])) {
        return $validated;
    }
    
    // Validate other settings
    $validated['sitewide'] = isset($settings['sitewide']) ? (bool) $settings['sitewide'] : false;
    $validated['disable_mobile'] = isset($settings['disable_mobile']) ? (bool) $settings['disable_mobile'] : false;
    $validated['urls'] = isset($settings['urls']) ? $settings['urls'] : '';
    $validated['exclude_urls'] = isset($settings['exclude_urls']) ? $settings['exclude_urls'] : '';
    
    // Validate button settings
    $validated['button_enabled'] = isset($settings['button_enabled']) ? (bool) $settings['button_enabled'] : false;
    $validated['button_text'] = isset($settings['button_text']) ? trim($settings['button_text']) : '';
    $validated['button_link'] = isset($settings['button_link']) ? esc_url_raw($settings['button_link']) : '';
    
    // Validate colors
    $validated['background_color'] = isset($settings['background_color']) && preg_match('/^#[a-fA-F0-9]{6}$/', $settings['background_color']) 
        ? $settings['background_color'] : '#729946';
    $validated['text_color'] = isset($settings['text_color']) && preg_match('/^#[a-fA-F0-9]{6}$/', $settings['text_color']) 
        ? $settings['text_color'] : '#000000';
    $validated['border_color'] = isset($settings['border_color']) && preg_match('/^#[a-fA-F0-9]{6}$/', $settings['border_color']) 
        ? $settings['border_color'] : '#000000';
    
    // Validate button colors
    $validated['button_color'] = isset($settings['button_color']) && preg_match('/^#[a-fA-F0-9]{6}$/', $settings['button_color']) 
        ? $settings['button_color'] : '#ce7a31';
    $validated['button_text_color'] = isset($settings['button_text_color']) && preg_match('/^#[a-fA-F0-9]{6}$/', $settings['button_text_color']) 
        ? $settings['button_text_color'] : '#ffffff';
    $validated['button_border_color'] = isset($settings['button_border_color']) && preg_match('/^#[a-fA-F0-9]{6}$/', $settings['button_border_color']) 
        ? $settings['button_border_color'] : '#ce7a31';
    
    // Validate numeric values
    $validated['font_size'] = isset($settings['font_size']) ? max(10, min(40, intval($settings['font_size']))) : 16;
    $validated['border_width'] = isset($settings['border_width']) ? max(0, min(10, intval($settings['border_width']))) : 0;
    
    // Validate button numeric values
    $validated['button_border_width'] = isset($settings['button_border_width']) ? max(0, min(10, intval($settings['button_border_width']))) : 0;
    $validated['button_border_radius'] = isset($settings['button_border_radius']) ? max(0, min(50, intval($settings['button_border_radius']))) : 4;
    $validated['button_padding'] = isset($settings['button_padding']) ? max(0, min(50, intval($settings['button_padding']))) : 8;
    $validated['button_font_size'] = isset($settings['button_font_size']) ? max(8, min(24, intval($settings['button_font_size']))) : 14;
    
    // Validate font family
    $allowed_fonts = array('Arial', 'Helvetica', 'Times New Roman', 'Georgia', 'Courier New', 'Verdana', 'Tahoma', 'Trebuchet MS', 'Impact', 'Comic Sans MS', 'Raleway');
    $validated['font_family'] = isset($settings['font_family']) && in_array($settings['font_family'], $allowed_fonts) 
        ? $settings['font_family'] : 'Arial';
    
    // Validate border style
    $allowed_styles = array('solid', 'dashed', 'dotted', 'double', 'none');
    $validated['border_style'] = isset($settings['border_style']) && in_array($settings['border_style'], $allowed_styles) 
        ? $settings['border_style'] : 'solid';
    
    // Validate button font weight
    $allowed_weights = array('normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900');
    $validated['button_font_weight'] = isset($settings['button_font_weight']) && in_array($settings['button_font_weight'], $allowed_weights) 
        ? $settings['button_font_weight'] : '600';
    
    // Validate dates
    $validated['start_date'] = isset($settings['start_date']) ? $settings['start_date'] : '';
    $validated['end_date'] = isset($settings['end_date']) ? $settings['end_date'] : '';
    $validated['image'] = isset($settings['image']) ? esc_url_raw($settings['image']) : '';
    $validated['image_start_date'] = isset($settings['image_start_date']) ? $settings['image_start_date'] : '';
    $validated['image_end_date'] = isset($settings['image_end_date']) ? $settings['image_end_date'] : '';
    
    return $validated;
}

/**
 * Detect potential theme conflicts and apply fixes
 * @return void
 */
function ca_banner_handle_theme_conflicts() {
    $theme = wp_get_theme();
    $theme_name = strtolower($theme->get('Name'));
    
    // Common problematic themes
    $problematic_themes = array(
        'avada', 'enfold', 'the7', 'beaver builder', 'elementor', 'astra', 'generatepress', 
        'oceanwp', 'storefront', 'flatsome', 'woodmart', 'porto', 'bridge', 'salient'
    );
    
    $is_problematic = false;
    foreach ($problematic_themes as $problematic) {
        if (strpos($theme_name, $problematic) !== false) {
            $is_problematic = true;
            break;
        }
    }
    
    if ($is_problematic) {
        // Add additional CSS fixes for problematic themes
        add_action('wp_head', function() {
            echo '<style type="text/css">
                /* CA Banner Theme Compatibility Fixes */
                .ca-banner-container {
                    position: relative !important;
                    z-index: 999999 !important;
                    transform: translateZ(0) !important;
                    -webkit-transform: translateZ(0) !important;
                }
                
                /* Override theme-specific positioning */
                body.ca-banner-active .site-header,
                body.ca-banner-active .main-header,
                body.ca-banner-active .header,
                body.ca-banner-active .navbar {
                    position: relative !important;
                    z-index: 1 !important;
                }
                
                /* Ensure banner appears above theme elements */
                .ca-banner-container * {
                    position: relative !important;
                    z-index: inherit !important;
                }
            </style>';
        }, 999);
    }
}

// Enqueue banner script
function banner_plugin_enqueue_scripts() {
    $settings = get_option('banner_plugin_settings');
    
    // Validate and sanitize settings
    $validated_settings = ca_banner_validate_settings($settings);
    
    if (!$validated_settings['enabled'] || empty($validated_settings['message'])) {
        return;
    }
    
    // Handle theme conflicts
    ca_banner_handle_theme_conflicts();
    
    // Use validated settings
    $message = $validated_settings['message'];
    $repeat = $validated_settings['repeat'];
    $background_color = $validated_settings['background_color'];
    $text_color = $validated_settings['text_color'];
    $font_size = $validated_settings['font_size'];
    $font_family = $validated_settings['font_family'];
    $border_width = $validated_settings['border_width'];
    $border_style = $validated_settings['border_style'];
    $border_color = $validated_settings['border_color'];
    $disable_mobile = $validated_settings['disable_mobile'];
    $start_date = $validated_settings['start_date'];
    $end_date = $validated_settings['end_date'];
    $urls = $validated_settings['urls'];
    $image = $validated_settings['image'];
    $image_start_date = $validated_settings['image_start_date'];
    $image_end_date = $validated_settings['image_end_date'];
    $sitewide = $validated_settings['sitewide'];
    $exclude_urls = $validated_settings['exclude_urls'];
    
    // Button settings
    $button_enabled = $validated_settings['button_enabled'];
    $button_text = $validated_settings['button_text'];
    $button_link = $validated_settings['button_link'];
    $button_color = $validated_settings['button_color'];
    $button_text_color = $validated_settings['button_text_color'];
    $button_border_width = $validated_settings['button_border_width'];
    $button_border_color = $validated_settings['button_border_color'];
    $button_border_radius = $validated_settings['button_border_radius'];
    $button_padding = $validated_settings['button_padding'];
    $button_font_size = $validated_settings['button_font_size'];
    $button_font_weight = $validated_settings['button_font_weight'];

    // Get current URL and normalize it for reliable matching
    $current_url_raw = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $current_url_raw = strtok($current_url_raw, '?'); // Remove query parameters
    $current_url_raw = strtok($current_url_raw, '#'); // Remove fragments
    
    // Normalize current URL for comparison
    $current_url = ca_banner_normalize_url($current_url_raw);
    
    // Process exclude URLs - normalize them for comparison
    $exclude_urls_array = ca_banner_process_url_list($exclude_urls);
    
    // Process include URLs - normalize them for comparison
    $include_urls_array = ca_banner_process_url_list($urls);
    
    // Check if the banner should be displayed
    $should_display = false;
    
    if ($sitewide) {
        // If sitewide is enabled, display everywhere except excluded pages
        $should_display = !ca_banner_url_matches($current_url, $exclude_urls_array);
    } else {
        // If not sitewide, check if current URL is in the include list
        // If no URLs specified, show everywhere (backward compatibility)
        $should_display = empty($include_urls_array) || ca_banner_url_matches($current_url, $include_urls_array);
    }
    
    // Apply exclude filter to both modes (exclude always takes precedence)
    if ($should_display && !empty($exclude_urls_array)) {
        $should_display = !ca_banner_url_matches($current_url, $exclude_urls_array);
    }
    
    // Enhanced debug output for administrators
    if (current_user_can('manage_options') && defined('WP_DEBUG') && WP_DEBUG) {
        echo '<!-- CA Banner Debug Info: ';
        echo 'Version: 1.2.6, ';
        echo 'Current URL: ' . esc_html($current_url) . ', ';
        echo 'Raw URL: ' . esc_html($current_url_raw) . ', ';
        echo 'Sitewide Mode: ' . ($sitewide ? 'Yes' : 'No') . ', ';
        echo 'Include URLs: [' . esc_html(implode(', ', $include_urls_array)) . '], ';
        echo 'Exclude URLs: [' . esc_html(implode(', ', $exclude_urls_array)) . '], ';
        echo 'Should Display: ' . ($should_display ? 'Yes' : 'No') . ', ';
        echo 'Enabled: ' . ($enabled ? 'Yes' : 'No') . ', ';
        echo 'Message Length: ' . strlen($message) . ' chars';
        echo ' -->';
    }
    
    if (!$should_display) {
        return;
    }

    echo '<script>';
    echo 'var caBannerConfig = {';
    echo 'message: \'' . addslashes($message) . ' &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; \',';
    echo 'repeat: ' . intval($repeat) . ',';
    echo 'backgroundColor: "' . esc_js($background_color) . '",';
    echo 'textColor: "' . esc_js($text_color) . '",';
    echo 'fontSize: ' . intval($font_size) . ',';
    echo 'fontFamily: "' . esc_js($font_family) . '",';
    echo 'borderWidth: ' . intval($border_width) . ',';
    echo 'borderStyle: "' . esc_js($border_style) . '",';
    echo 'borderColor: "' . esc_js($border_color) . '",';
    echo 'disableMobile: ' . ($disable_mobile ? 'true' : 'false') . ',';
    echo 'startDate: "' . esc_js($start_date) . '",';
    echo 'endDate: "' . esc_js($end_date) . '",';
    echo 'image: "' . esc_js($image) . '",';
    echo 'imageStartDate: "' . esc_js($image_start_date) . '",';
    echo 'imageEndDate: "' . esc_js($image_end_date) . '"';
    echo '};';
    ?>
    
    (function() {
        'use strict';
        
        var caBanner = {
            initialized: false,
            
            init: function() {
                if (this.initialized) return;
                this.initialized = true;
                
                try {
                    this.createBanner();
                    this.createBannerImage();
                    this.addBodyClasses();
                } catch (error) {
                    console.warn('CA Banner initialization error:', error);
                    // Fallback: try again after a short delay
                    setTimeout(this.init.bind(this), 100);
                }
            },
            
            createBanner: function() {
                var config = caBannerConfig;
                
                // Mobile check
                if (config.disableMobile && this.isMobile()) {
                    return;
                }

                // Date validation
                if (!this.isWithinDateRange(config.startDate, config.endDate)) {
                    return;
                }

                // Check if banner already exists
                if (document.querySelector('.ca-banner-container')) {
                    return;
                }

                var banner = document.createElement("div");
                banner.className = "ca-banner-container";
                banner.setAttribute('data-ca-banner', 'true');
                
                var bannerContent = document.createElement("div");
                bannerContent.className = "ca-banner-content";
                
                // Create repeated message safely with HTML support
                var message = config.message || '';
                var repeat = Math.max(1, Math.min(100, config.repeat || 10));
                var repeatedMessage = '';
                for (var i = 0; i < repeat; i++) {
                    repeatedMessage += message;
                }
                
                // Set HTML content to allow HTML/CSS styling
                bannerContent.innerHTML = repeatedMessage;

                // Apply inline styles for maximum compatibility
                banner.style.cssText = [
                    'position: relative !important',
                    'top: 0 !important',
                    'left: 0 !important',
                    'width: 100% !important',
                    'background-color: ' + (config.backgroundColor || '#729946') + ' !important',
                    'color: ' + (config.textColor || '#000000') + ' !important',
                    'padding: 10px !important',
                    'text-align: center !important',
                    'z-index: 999999 !important',
                    'overflow: hidden !important',
                    'font-weight: 600 !important',
                    'font-size: ' + (config.fontSize || 16) + 'px !important',
                    'font-family: "' + (config.fontFamily || 'Arial') + '", sans-serif !important',
                    'border-top: ' + (config.borderWidth || 0) + 'px ' + (config.borderStyle || 'solid') + ' ' + (config.borderColor || '#000000') + ' !important',
                    'border-bottom: ' + (config.borderWidth || 0) + 'px ' + (config.borderStyle || 'solid') + ' ' + (config.borderColor || '#000000') + ' !important',
                    'margin: 0 !important',
                    'box-shadow: none !important'
                ].join('; ');

                // Apply styles to banner content
                bannerContent.style.display = 'inline-block';
                bannerContent.style.whiteSpace = 'nowrap';
                bannerContent.style.margin = '0';
                bannerContent.style.padding = '0';
                
                // Create and add CSS animation dynamically (only if not already added)
                if (!document.querySelector('#ca-banner-animation-style')) {
                    var style = document.createElement('style');
                    style.id = 'ca-banner-animation-style';
                    style.textContent = `
                        @keyframes ca-banner-marquee {
                            0% { transform: translateX(0%); }
                            100% { transform: translateX(-100%); }
                        }
                        .ca-banner-content {
                            animation: ca-banner-marquee 120s linear infinite !important;
                        }
                    `;
                    document.head.appendChild(style);
                }
                
                // Add animation class
                bannerContent.classList.add('ca-banner-content');

                banner.appendChild(bannerContent);


                // Insert banner at the very beginning of body
                if (document.body) {
                    document.body.insertBefore(banner, document.body.firstChild);
                } else {
                    // Fallback: append to document
                    document.documentElement.appendChild(banner);
                }
            },
            
            createBannerImage: function() {
                var config = caBannerConfig;
                
                if (!config.image) return;
                
                // Date validation
                if (!this.isWithinDateRange(config.imageStartDate, config.imageEndDate)) {
                    return;
                }

                // Check if image banner already exists
                if (document.querySelector('.ca-banner-image')) {
                    return;
                }

                var img = document.createElement("img");
                img.className = "ca-banner-image";
                img.src = config.image;
                img.style.cssText = "width: 100% !important; height: auto !important; display: block !important; margin-top: 3rem !important;";
                img.setAttribute('data-ca-banner-image', 'true');
                
                if (document.body) {
                    document.body.insertBefore(img, document.body.firstChild);
                }
            },
            
            addBodyClasses: function() {
                if (document.documentElement) {
                    document.documentElement.classList.add('ca-banner-active');
                }
                if (document.body) {
                    document.body.classList.add('ca-banner-active');
                }
            },
            
            isMobile: function() {
                return window.matchMedia && window.matchMedia("(max-width: 768px)").matches;
            },
            
            isWithinDateRange: function(startDate, endDate) {
                if (!startDate && !endDate) return true;
                
                var currentDate = new Date();
                
                if (startDate) {
                    var start = new Date(startDate);
                    if (isNaN(start.getTime()) || currentDate < start) {
                        return false;
                    }
                }
                
                if (endDate) {
                    var end = new Date(endDate);
                    if (isNaN(end.getTime()) || currentDate > end) {
                        return false;
                    }
                }
                
                return true;
            }
        };

        // Multiple initialization methods for maximum compatibility
        function initBanner() {
            caBanner.init();
        }

        // Try multiple initialization methods
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initBanner);
        } else {
            initBanner();
        }

        // Fallback for themes that might interfere
        setTimeout(initBanner, 100);
        setTimeout(initBanner, 500);
        
        // Also try on window load as final fallback
        window.addEventListener('load', initBanner);
        
    })();
    
    <?php
    echo '</script>';
}

/**
 * Enqueue banner styles
 */
function banner_plugin_enqueue_styles() {
    $settings = get_option('banner_plugin_settings');
    $enabled = isset($settings['enabled']) ? $settings['enabled'] : false;

    if (!$enabled) {
        return;
    }

    $css = '
        /* CA Banner - High specificity to override theme styles */
        html.ca-banner-active { 
            margin-top: 0 !important; 
        }
        
        body.ca-banner-active {
            margin-top: 0 !important;
        }
        
        /* Ensure banner appears above all theme elements */
        .ca-banner-container {
            position: relative !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            z-index: 999999 !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
        }
        
        .ca-banner-content {
            display: inline-block !important;
            white-space: nowrap !important;
            animation: ca-banner-marquee 120s linear infinite !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        @keyframes ca-banner-marquee {
            0% {
                transform: translateX(0%) !important;
            }
            100% {
                transform: translateX(-100%) !important;
            }
        }
        
        
        /* Mobile responsive adjustments */
        @media (max-width: 768px) {
            .ca-banner-container {
                font-size: 14px !important;
                padding: 8px !important;
            }
        }
    ';
    
    // Add styles directly to head
    add_action('wp_head', function() use ($css) {
        echo '<style type="text/css">' . $css . '</style>';
    });
}
add_action('wp_enqueue_scripts', 'banner_plugin_enqueue_styles', 5);
add_action('wp_head', 'banner_plugin_enqueue_scripts', 1);