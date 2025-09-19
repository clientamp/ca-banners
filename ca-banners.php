/*
Plugin Name: CA Banners
Plugin URI: https://github.com/clientamp/ca-banners
Description: Professional WordPress banner plugin with customizable scrolling messages, advanced scheduling, page targeting, and image support. Perfect for promotions, announcements, and site-wide notices.
Version: 1.2
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
    add_menu_page('CA Banners', 'CA Banners', 'manage_options', 'banner-plugin', 'banner_plugin_settings_page');
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
    $screen->add_help_tab(array(
        'id' => 'ca-banners-help',
        'title' => 'Quick Start Guide',
        'content' => '
            <h3>Quick Start Guide</h3>
            <ol>
                <li><strong>Enable Banner:</strong> Check the "Enable Banner" checkbox</li>
                <li><strong>Add Message:</strong> Enter your banner message in the text area</li>
                <li><strong>Choose Display:</strong> Select "Display Sitewide" or specify pages</li>
                <li><strong>Customize Style:</strong> Adjust colors, fonts, and borders</li>
                <li><strong>Save Settings:</strong> Click "Save Changes"</li>
            </ol>
            <h3>Common Use Cases</h3>
            <ul>
                <li><strong>Promotional Banner:</strong> Enable sitewide, add your promotion message, set start/end dates</li>
                <li><strong>Page-Specific Notice:</strong> Disable sitewide, add specific URLs like /about-us/, /contact/</li>
                <li><strong>Mobile-Friendly:</strong> Check "Disable on Mobile" if banner doesn\'t work well on phones</li>
            </ul>
        '
    ));
    
    $screen->add_help_tab(array(
        'id' => 'ca-banners-troubleshooting',
        'title' => 'Troubleshooting',
        'content' => '
            <h3>Troubleshooting</h3>
            <h4>Banner Not Showing?</h4>
            <ul>
                <li>Make sure "Enable Banner" is checked</li>
                <li>Check if you have a message entered</li>
                <li>Verify your display settings (sitewide or specific pages)</li>
                <li>Check if start/end dates are set correctly</li>
            </ul>
            <h4>Banner Showing on Wrong Pages?</h4>
            <ul>
                <li>If using sitewide, add unwanted pages to "Exclude on Pages"</li>
                <li>If not using sitewide, check your "Display on Pages" URLs</li>
                <li>URLs should start with / and end with / (e.g., /about-us/)</li>
            </ul>
        '
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
            ?>
        </form>
    </div>
    <?php
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
    
    // Text fields
    $sanitized['message'] = isset($input['message']) ? sanitize_textarea_field($input['message']) : '';
    $sanitized['urls'] = isset($input['urls']) ? sanitize_textarea_field($input['urls']) : '';
    $sanitized['exclude_urls'] = isset($input['exclude_urls']) ? sanitize_textarea_field($input['exclude_urls']) : '';
    $sanitized['image'] = isset($input['image']) ? esc_url_raw($input['image']) : '';
    
    // Numeric fields with validation
    $sanitized['repeat'] = isset($input['repeat']) ? max(1, min(100, intval($input['repeat']))) : 10;
    $sanitized['font_size'] = isset($input['font_size']) ? max(10, min(40, intval($input['font_size']))) : 16;
    $sanitized['border_width'] = isset($input['border_width']) ? max(0, min(10, intval($input['border_width']))) : 0;
    
    // Color fields
    $sanitized['background_color'] = isset($input['background_color']) ? sanitize_hex_color($input['background_color']) : '#729946';
    $sanitized['text_color'] = isset($input['text_color']) ? sanitize_hex_color($input['text_color']) : '#000000';
    $sanitized['border_color'] = isset($input['border_color']) ? sanitize_hex_color($input['border_color']) : '#000000';
    
    // Font family validation
    $allowed_fonts = array('Arial', 'Helvetica', 'Times New Roman', 'Georgia', 'Courier New', 'Verdana', 'Tahoma', 'Trebuchet MS', 'Impact', 'Comic Sans MS', 'Raleway');
    $sanitized['font_family'] = isset($input['font_family']) && in_array($input['font_family'], $allowed_fonts) ? $input['font_family'] : 'Arial';
    
    // Border style validation
    $allowed_styles = array('solid', 'dashed', 'dotted', 'double', 'none');
    $sanitized['border_style'] = isset($input['border_style']) && in_array($input['border_style'], $allowed_styles) ? $input['border_style'] : 'solid';
    
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

// Callback functions for settings fields
function banner_plugin_enabled_callback() {
    $settings = get_option('banner_plugin_settings');
    $enabled = isset($settings['enabled']) ? $settings['enabled'] : false;
    echo '<input type="checkbox" name="banner_plugin_settings[enabled]" value="1"' . checked(1, $enabled, false) . '>';
    echo '<p class="description">Check this box to activate the banner on your website.</p>';
}

function banner_plugin_message_callback() {
    $settings = get_option('banner_plugin_settings');
    $message = isset($settings['message']) ? $settings['message'] : '';
    echo '<textarea name="banner_plugin_settings[message]" rows="4" cols="50" placeholder="Enter your banner message here...">' . esc_textarea($message) . '</textarea>';
    echo '<p class="description">Enter the text you want to display in your scrolling banner. This message will repeat across the banner.</p>';
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
    echo '<p class="description"><strong>Enter one URL per line</strong> to display the banner on specific pages. Use relative URLs like <code>/about-us/</code> or <code>/contact/</code>. Leave empty to show on all pages (when sitewide is disabled).</p>';
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
    echo '<input type="checkbox" name="banner_plugin_settings[sitewide]" value="1"' . checked(1, $sitewide, false) . '>';
    echo '<p class="description"><strong>Enable to display banner across all pages.</strong> When enabled, this overrides the "Display on Pages" setting. Use "Exclude on Pages" to hide the banner on specific pages.</p>';
}

function banner_plugin_exclude_urls_callback() {
    $settings = get_option('banner_plugin_settings');
    $exclude_urls = isset($settings['exclude_urls']) ? $settings['exclude_urls'] : '';
    echo '<textarea name="banner_plugin_settings[exclude_urls]" rows="4" cols="50" placeholder="/checkout/&#10;/cart/&#10;/admin/">' . esc_textarea($exclude_urls) . '</textarea>';
    echo '<p class="description"><strong>Enter one URL per line</strong> to exclude the banner from specific pages when sitewide is enabled. Use relative URLs like <code>/checkout/</code> or <code>/cart/</code>.</p>';
}

// Enqueue admin scripts
function banner_plugin_admin_scripts() {
    wp_enqueue_media();
    add_action('admin_footer', 'banner_plugin_admin_footer_scripts');
}
add_action('admin_enqueue_scripts', 'banner_plugin_admin_scripts');

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
    });
    </script>
    <?php
}

// Enqueue banner script
function banner_plugin_enqueue_scripts() {
    $settings = get_option('banner_plugin_settings');
    $enabled = isset($settings['enabled']) ? $settings['enabled'] : false;

    if (!$enabled) {
        return;
    }

    $message = isset($settings['message']) ? $settings['message'] : '';
    $repeat = isset($settings['repeat']) ? $settings['repeat'] : '10';
    $background_color = isset($settings['background_color']) ? $settings['background_color'] : '#729946';
    $text_color = isset($settings['text_color']) ? $settings['text_color'] : '#000000';
    $font_size = isset($settings['font_size']) ? $settings['font_size'] : '16';
    $font_family = isset($settings['font_family']) ? $settings['font_family'] : 'Arial';
    $border_width = isset($settings['border_width']) ? $settings['border_width'] : '0';
    $border_style = isset($settings['border_style']) ? $settings['border_style'] : 'solid';
    $border_color = isset($settings['border_color']) ? $settings['border_color'] : '#000000';
    $disable_mobile = isset($settings['disable_mobile']) ? $settings['disable_mobile'] : false;
    $start_date = isset($settings['start_date']) ? $settings['start_date'] : '';
    $end_date = isset($settings['end_date']) ? $settings['end_date'] : '';
    $urls = isset($settings['urls']) ? $settings['urls'] : '';
    $image = isset($settings['image']) ? $settings['image'] : '';
    $image_start_date = isset($settings['image_start_date']) ? $settings['image_start_date'] : '';
    $image_end_date = isset($settings['image_end_date']) ? $settings['image_end_date'] : '';
    $sitewide = isset($settings['sitewide']) ? $settings['sitewide'] : false;
    $exclude_urls = isset($settings['exclude_urls']) ? $settings['exclude_urls'] : '';

    /*
    // Old
    $current_url = $_SERVER['REQUEST_URI'];
    $banner_urls = preg_split('/\r\n|\r|\n/', $urls);
    $banner_urls = array_map('trim', $banner_urls);
    $banner_urls = array_filter($banner_urls);
    
    if (!empty($banner_urls) && !in_array($current_url, $banner_urls)) {
        return;
    }
    */
   // Update: Get current URL without query parameters and ensure it has a trailing slash

    
    // Update: Get current URL without query parameters and ensure it has a trailing slash
    $current_url = rtrim(strtok($_SERVER['REQUEST_URI'], '?'), '/') . '/';

    if ($_SERVER['REQUEST_URI'] == '/') {
        $is_homepage = true;
    } else {
        $is_homepage = false;
    }
    
    // Process exclude URLs
    $exclude_urls_array = preg_split('/\r\n|\r|\n/', $exclude_urls);
    $exclude_urls_array = array_map(function($url) {
        return rtrim(trim($url), '/') . '/';
    }, $exclude_urls_array);
    $exclude_urls_array = array_filter($exclude_urls_array);
    
    // Process include URLs
    $include_urls_array = preg_split('/\r\n|\r|\n/', $urls);
    $include_urls_array = array_map(function($url) {
        return rtrim(trim($url), '/') . '/';
    }, $include_urls_array);
    $include_urls_array = array_filter($include_urls_array);
    
    // Check if the banner should be displayed
    $should_display = false;
    
    if ($sitewide) {
        // If sitewide is enabled, display everywhere except excluded pages
        $should_display = !in_array($current_url, $exclude_urls_array);
        // Always show on homepage if sitewide is enabled and homepage is not explicitly excluded
        if ($is_homepage && !in_array('/', $exclude_urls_array)) {
            $should_display = true;
        }
    } else {
        // If not sitewide, check if current URL is in the include list
        $should_display = empty($include_urls_array) || in_array($current_url, $include_urls_array);
        // For homepage, check if '/' is in the include list
        if ($is_homepage && !empty($include_urls_array)) {
            $should_display = in_array('/', $include_urls_array);
        }
    }
    
    if (!$should_display) {
        return;
    }

    echo '<script>';
    echo 'var bannerMessage = \'' . esc_js($message) . ' &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; \';';
    echo 'var bannerRepeat = "' . esc_js($repeat) . '";';
    echo 'var bannerBackgroundColor = "' . esc_js($background_color) . '";';
    echo 'var bannerTextColor = "' . esc_js($text_color) . '";';
    echo 'var bannerFontSize = "' . esc_js($font_size) . '";';
    echo 'var bannerFontFamily = "' . esc_js($font_family) . '";';
    echo 'var bannerBorderWidth = "' . esc_js($border_width) . '";';
    echo 'var bannerBorderStyle = "' . esc_js($border_style) . '";';
    echo 'var bannerBorderColor = "' . esc_js($border_color) . '";';
    echo 'var bannerDisableMobile = ' . ($disable_mobile ? 'true' : 'false') . ';';
    echo 'var bannerStartDate = "' . esc_js($start_date) . '";';
    echo 'var bannerEndDate = "' . esc_js($end_date) . '";';
    echo 'var bannerImage = "' . esc_js($image) . '";';
    echo 'var bannerImageStartDate = "' . esc_js($image_start_date) . '";';
    echo 'var bannerImageEndDate = "' . esc_js($image_end_date) . '";';
    echo 'var currentUrl = "' . esc_js($current_url) . '";';
    ?>
    
    function createBanner() {
        var isMobile = window.matchMedia("(max-width: 768px)").matches;
        if (bannerDisableMobile && isMobile) {
            return;
        }

        var currentDate = new Date();
        var startDate = bannerStartDate ? new Date(bannerStartDate) : null;
        var endDate = bannerEndDate ? new Date(bannerEndDate) : null;

        if (startDate && currentDate < startDate) {
            return;
        }

        if (endDate && currentDate > endDate) {
            return;
        }

        var banner = document.createElement("div");
        banner.className = "banner";
        var bannerContent = document.createElement("div");
        bannerContent.className = "banner-content";
        bannerContent.innerHTML = bannerMessage.repeat(bannerRepeat);
        banner.appendChild(bannerContent);

        var style = document.createElement("style");
        style.innerHTML = `
            .banner {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                background-color: ${bannerBackgroundColor};
                color: ${bannerTextColor};
                padding: 10px;
                text-align: center;
                z-index: 9999;
                overflow: hidden;
                font-weight: 600;
                font-size: ${bannerFontSize}px;
                font-family: '${bannerFontFamily}', sans-serif;
                border-top: ${bannerBorderWidth}px ${bannerBorderStyle} ${bannerBorderColor};
                border-bottom: ${bannerBorderWidth}px ${bannerBorderStyle} ${bannerBorderColor};
            }

            .banner-content {
                display: inline-block;
                white-space: nowrap;
                animation: marquee 120s linear infinite;
            }

            @keyframes marquee {
                0% {
                    transform: translateX(0%);
                }
                100% {
                    transform: translateX(-100%);
                }
            }
        `;

        document.body.appendChild(banner);
        document.head.appendChild(style);
    }

    function createBannerImage() {
        var currentDate = new Date();
        var imageStartDate = bannerImageStartDate ? new Date(bannerImageStartDate) : null;
        var imageEndDate = bannerImageEndDate ? new Date(bannerImageEndDate) : null;

        if (imageStartDate && currentDate < imageStartDate) {
            return;
        }

        if (imageEndDate && currentDate > imageEndDate) {
            return;
        }

        if (bannerImage) {
            var img = document.createElement("img");
            img.src = bannerImage;
            img.style.cssText = "width: 100%; height: auto; display: block; margin-top: 3rem;";
            document.body.insertBefore(img, document.body.firstChild);
        }
    }
    /*
    window.onload = function() {
        createBanner();
        createBannerImage();
    };
    */
    document.addEventListener('DOMContentLoaded', function() {
        createBanner();
        createBannerImage();
    });
    
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
        html { 
            margin-top: 6rem !important; 
        } 
        .banner-button { 
            display: inline-block; 
            padding: 5px 12px; 
            background-color: rgb(206, 122, 49);
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-weight: 600; 
            text-decoration: none; 
            transition: all 0.3s ease; 
        } 
        .banner-button:hover, 
        .banner-button:focus { 
            background-color: rgba(206, 122, 49, 0.8); 
            color: white; 
            text-decoration: none;
        }
    ';
    
    // Add styles directly to head
    add_action('wp_head', function() use ($css) {
        echo '<style type="text/css">' . $css . '</style>';
    });
}
add_action('wp_enqueue_scripts', 'banner_plugin_enqueue_styles');
add_action('wp_footer', 'banner_plugin_enqueue_scripts');