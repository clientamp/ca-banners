<?php
/**
 * Settings class for managing plugin settings
 *
 * @package CA_Banners
 * @since 1.2.7
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CA Banners Settings class
 */
class CA_Banners_Settings {
    
    /**
     * Settings option name
     */
    const OPTION_NAME = 'banner_plugin_settings';
    
    /**
     * Default settings
     */
    private $default_settings = array(
        'enabled' => false,
        'message' => '',
        'repeat' => 10,
        'background_color' => '#729946',
        'text_color' => '#000000',
        'font_size' => 16,
        'font_family' => 'Arial',
        'border_width' => 0,
        'border_style' => 'solid',
        'border_color' => '#000000',
        'sitewide' => false,
        'disable_mobile' => false,
        'urls' => '',
        'exclude_urls' => '',
        'start_date' => '',
        'end_date' => '',
        'image' => '',
        'image_start_date' => '',
        'image_end_date' => '',
        'button_enabled' => false,
        'button_text' => '',
        'button_link' => '',
        'button_color' => '#ce7a31',
        'button_text_color' => '#ffffff',
        'button_border_width' => 0,
        'button_border_color' => '#ce7a31',
        'button_border_radius' => 4,
        'button_padding' => 8,
        'button_font_size' => 14,
        'button_font_weight' => '600'
    );
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Register plugin settings with WordPress
     */
    public function register_settings() {
        register_setting('banner_plugin_settings', self::OPTION_NAME, array(
            'sanitize_callback' => array($this, 'sanitize_settings')
        ));

        // Basic Settings Section
        add_settings_section('banner_basic_section', 'Basic Settings', array($this, 'basic_section_callback'), 'banner-plugin');
        add_settings_field('banner_enabled', 'Enable Banner', array($this, 'enabled_callback'), 'banner-plugin', 'banner_basic_section');
        add_settings_field('banner_message', 'Banner Message', array($this, 'message_callback'), 'banner-plugin', 'banner_basic_section');
        add_settings_field('banner_repeat', 'Message Repeats', array($this, 'repeat_callback'), 'banner-plugin', 'banner_basic_section');
        add_settings_field('banner_speed', 'Scroll Speed', array($this, 'speed_callback'), 'banner-plugin', 'banner_basic_section');

        // Display Settings Section
        add_settings_section('banner_display_section', 'Display Settings', array($this, 'display_section_callback'), 'banner-plugin');
        add_settings_field('banner_sitewide', 'Display Sitewide', array($this, 'sitewide_callback'), 'banner-plugin', 'banner_display_section');
        add_settings_field('banner_urls', 'Display on Pages', array($this, 'urls_callback'), 'banner-plugin', 'banner_display_section');
        add_settings_field('banner_exclude_urls', 'Exclude on Pages', array($this, 'exclude_urls_callback'), 'banner-plugin', 'banner_display_section');
        add_settings_field('banner_disable_mobile', 'Disable on Mobile', array($this, 'disable_mobile_callback'), 'banner-plugin', 'banner_display_section');

        // Style Settings Section (renamed from Styling)
        add_settings_section('banner_styling_section', 'Style Settings', array($this, 'styling_section_callback'), 'banner-plugin');
        add_settings_field('banner_background_color', 'Background Color', array($this, 'background_color_callback'), 'banner-plugin', 'banner_styling_section');
        add_settings_field('banner_text_color', 'Text Color', array($this, 'text_color_callback'), 'banner-plugin', 'banner_styling_section');
        add_settings_field('banner_font_size', 'Font Size', array($this, 'font_size_callback'), 'banner-plugin', 'banner_styling_section');
        add_settings_field('banner_font_family', 'Font Family', array($this, 'font_family_callback'), 'banner-plugin', 'banner_styling_section');
        add_settings_field('banner_font_weight', 'Font Weight', array($this, 'font_weight_callback'), 'banner-plugin', 'banner_styling_section');
        add_settings_field('banner_border_width', 'Border Width', array($this, 'border_width_callback'), 'banner-plugin', 'banner_styling_section');
        add_settings_field('banner_border_style', 'Border Style', array($this, 'border_style_callback'), 'banner-plugin', 'banner_styling_section');
        add_settings_field('banner_border_color', 'Border Color', array($this, 'border_color_callback'), 'banner-plugin', 'banner_styling_section');

        // Button Settings Section
        add_settings_section('banner_button_section', 'Button Settings', array($this, 'button_section_callback'), 'banner-plugin');
        add_settings_field('banner_button_enabled', 'Enable Button', array($this, 'button_enabled_callback'), 'banner-plugin', 'banner_button_section');
        add_settings_field('banner_button_text', 'Button Text', array($this, 'button_text_callback'), 'banner-plugin', 'banner_button_section');
        add_settings_field('banner_button_link', 'Button Link', array($this, 'button_link_callback'), 'banner-plugin', 'banner_button_section');
        add_settings_field('banner_button_color', 'Button Color', array($this, 'button_color_callback'), 'banner-plugin', 'banner_button_section');
        add_settings_field('banner_button_text_color', 'Button Text Color', array($this, 'button_text_color_callback'), 'banner-plugin', 'banner_button_section');
        add_settings_field('banner_button_border_width', 'Button Border Width', array($this, 'button_border_width_callback'), 'banner-plugin', 'banner_button_section');
        add_settings_field('banner_button_border_color', 'Button Border Color', array($this, 'button_border_color_callback'), 'banner-plugin', 'banner_button_section');
        add_settings_field('banner_button_border_radius', 'Button Border Radius', array($this, 'button_border_radius_callback'), 'banner-plugin', 'banner_button_section');
        add_settings_field('banner_button_padding', 'Button Padding', array($this, 'button_padding_callback'), 'banner-plugin', 'banner_button_section');
        add_settings_field('banner_button_font_size', 'Button Font Size', array($this, 'button_font_size_callback'), 'banner-plugin', 'banner_button_section');
        add_settings_field('banner_button_font_weight', 'Button Font Weight', array($this, 'button_font_weight_callback'), 'banner-plugin', 'banner_button_section');

        // Scheduling Settings Section
        add_settings_section('banner_scheduling_section', 'Scheduling Settings', array($this, 'scheduling_section_callback'), 'banner-plugin');
        add_settings_field('banner_start_date', 'Banner Start Date', array($this, 'start_date_callback'), 'banner-plugin', 'banner_scheduling_section');
        add_settings_field('banner_end_date', 'Banner End Date', array($this, 'end_date_callback'), 'banner-plugin', 'banner_scheduling_section');

        // Static Image Banner Settings Section
        add_settings_section('banner_image_section', 'Static Image Banner Settings', array($this, 'image_section_callback'), 'banner-plugin');
        add_settings_field('banner_image', 'Banner Image', array($this, 'image_callback'), 'banner-plugin', 'banner_image_section');
        add_settings_field('banner_image_start_date', 'Image Start Date', array($this, 'image_start_date_callback'), 'banner-plugin', 'banner_image_section');
        add_settings_field('banner_image_end_date', 'Image End Date', array($this, 'image_end_date_callback'), 'banner-plugin', 'banner_image_section');
    }
    
    /**
     * Get plugin settings
     *
     * @return array Settings array
     */
    public function get_settings() {
        $settings = get_option(self::OPTION_NAME, array());
        return wp_parse_args($settings, $this->default_settings);
    }
    
    /**
     * Update plugin settings
     *
     * @param array $settings Settings to update
     * @return bool True on success
     */
    public function update_settings($settings) {
        return update_option(self::OPTION_NAME, $settings);
    }
    
    /**
     * Get a specific setting
     *
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed Setting value
     */
    public function get_setting($key, $default = null) {
        $settings = $this->get_settings();
        
        if (isset($settings[$key])) {
            return $settings[$key];
        }
        
        if ($default !== null) {
            return $default;
        }
        
        return isset($this->default_settings[$key]) ? $this->default_settings[$key] : null;
    }
    
    /**
     * Sanitize settings using validator
     *
     * @param array $input Raw input
     * @return array Sanitized input
     */
    public function sanitize_settings($input) {
        $ca_banners = CA_Banners::get_instance();
        return $ca_banners->validator->sanitize_settings($input);
    }
    
    /**
     * Section callback functions
     */
    public function basic_section_callback() {
        echo '<p>Configure the basic banner settings including enabling the banner and setting your message.</p>';
    }
    
    public function display_section_callback() {
        echo '<p>Control where and when your banner appears on your website.</p>';
    }
    
    public function styling_section_callback() {
        echo '<p>Customize the appearance of your banner with colors, fonts, and borders.</p>';
    }
    
    public function scheduling_section_callback() {
        echo '<p>Schedule when your banner should start and stop displaying.</p>';
    }
    
    public function image_section_callback() {
        echo '<p><strong>Static Image Banner:</strong> Display a full-width promotional image at the top of your pages, separate from the scrolling text banner. Perfect for announcements, promotions, or seasonal campaigns.</p>';
        echo '<p><strong>Key Features:</strong></p>';
        echo '<ul style="margin: 10px 0; padding-left: 20px;">';
        echo '<li>Full-width image display at the top of pages</li>';
        echo '<li>Independent scheduling from the text banner</li>';
        echo '<li>Appears above the scrolling text banner</li>';
        echo '<li>Perfect for promotional campaigns and announcements</li>';
        echo '</ul>';
    }
    
    /**
     * Field callback functions - these will be moved to admin class
     */
    public function enabled_callback() {
        $settings = $this->get_settings();
        $enabled = $settings['enabled'];
        
        echo '<div class="ca-banner-toggle-container">';
        echo '<label class="ca-banner-toggle">';
        echo '<input type="checkbox" name="' . self::OPTION_NAME . '[enabled]" value="1"' . checked(1, $enabled, false) . '>';
        echo '<span class="ca-banner-toggle-slider"></span>';
        echo '</label>';
        echo '<span class="ca-banner-toggle-label">' . ($enabled ? 'Enabled' : 'Disabled') . '</span>';
        echo '</div>';
        echo '<p class="description">Toggle to activate or deactivate the banner on your website.</p>';
    }
    
    public function message_callback() {
        $settings = $this->get_settings();
        $message = $settings['message'];
        echo '<div class="ca-banner-form-group">';
        
        // Use WordPress editor instead of textarea
        wp_editor($message, 'banner_message', array(
            'textarea_name' => self::OPTION_NAME . '[message]',
            'textarea_rows' => 4,
            'media_buttons' => false, // Disable media buttons for banner
            'teeny' => true, // Use minimal editor
            'tinymce' => array(
                'toolbar1' => 'bold,italic,underline,|,forecolor,|,link,unlink,|,undo,redo',
                'toolbar2' => '',
                'toolbar3' => '',
                'toolbar4' => '',
                'menubar' => false,
                'statusbar' => false,
                'resize' => false,
                'height' => 120,
                'setup' => 'function(ed) {
                    ed.on("change", function() {
                        ed.save();
                    });
                }'
            ),
            'quicktags' => array(
                'buttons' => 'strong,em,link,del'
            )
        ));
        
        echo '<p class="description">Enter the text you want to display in your scrolling banner. This message will repeat across the banner.<br><strong>Rich Text Editor:</strong> Use the formatting buttons above to style your text. You can make text <strong>bold</strong>, <em>italic</em>, change colors, and add links.</p>';
        echo '</div>';
    }
    
    public function repeat_callback() {
        $settings = $this->get_settings();
        $repeat = $settings['repeat'];
        echo '<div class="ca-banner-form-group">';
        echo '<div style="display: flex; align-items: center; gap: 10px;">';
        echo '<input type="range" name="' . self::OPTION_NAME . '[repeat]" id="banner_repeat" class="ca-banner-range" value="' . esc_attr($repeat) . '" min="1" max="100" oninput="document.getElementById(\'repeat-value\').textContent = this.value">';
        echo '<span class="ca-banner-range-value" id="repeat-value">' . esc_attr($repeat) . '</span>';
        echo '</div>';
        echo '<p class="description">How many times to repeat your message across the banner (1-100). More repeats create a longer scrolling effect.</p>';
        echo '</div>';
    }
    
    public function speed_callback() {
        $settings = $this->get_settings();
        $speed = $settings['speed'];
        echo '<div class="ca-banner-form-group">';
        echo '<div style="display: flex; align-items: center; gap: 10px;">';
        echo '<input type="range" name="' . self::OPTION_NAME . '[speed]" id="banner_speed" class="ca-banner-range" value="' . esc_attr($speed) . '" min="30" max="300" oninput="document.getElementById(\'speed-value\').textContent = this.value + \'s\'">';
        echo '<span class="ca-banner-range-value" id="speed-value">' . esc_attr($speed) . 's</span>';
        echo '</div>';
        echo '<p class="description">Control how fast the banner message scrolls (30-300 seconds). Lower values = faster scrolling.</p>';
        echo '</div>';
    }
    
    public function background_color_callback() {
        $settings = $this->get_settings();
        $background_color = $settings['background_color'];
        echo '<div class="ca-banner-form-group">';
        echo '<input type="color" name="' . self::OPTION_NAME . '[background_color]" id="banner_background_color" class="ca-banner-color-picker" value="' . esc_attr($background_color) . '">';
        echo '<p class="description">Choose the background color for your banner.</p>';
        echo '</div>';
    }
    
    public function text_color_callback() {
        $settings = $this->get_settings();
        $text_color = $settings['text_color'];
        echo '<div class="ca-banner-form-group">';
        echo '<input type="color" name="' . self::OPTION_NAME . '[text_color]" id="banner_text_color" class="ca-banner-color-picker" value="' . esc_attr($text_color) . '">';
        echo '<p class="description">Choose the text color for your banner.</p>';
        echo '</div>';
    }
    
    public function font_size_callback() {
        $settings = $this->get_settings();
        $font_size = $settings['font_size'];
        echo '<div class="ca-banner-form-group">';
        echo '<div style="display: flex; align-items: center; gap: 10px;">';
        echo '<input type="range" name="' . self::OPTION_NAME . '[font_size]" id="banner_font_size" class="ca-banner-range" value="' . esc_attr($font_size) . '" min="10" max="40" oninput="document.getElementById(\'font-size-value\').textContent = this.value + \'px\'">';
        echo '<span class="ca-banner-range-value" id="font-size-value">' . esc_attr($font_size) . 'px</span>';
        echo '</div>';
        echo '<p class="description">Adjust the font size for your banner text (10-40px).</p>';
        echo '</div>';
    }
    
    public function font_family_callback() {
        $settings = $this->get_settings();
        $font_family = $settings['font_family'];
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

        echo '<div class="ca-banner-form-group">';
        echo '<select name="' . self::OPTION_NAME . '[font_family]" id="banner_font_family" class="ca-banner-select">';
        foreach ($font_options as $value => $label) {
            $selected = ($font_family === $value) ? 'selected' : '';
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . $label . '</option>';
        }
        echo '</select>';
        echo '<p class="description">Choose the font family for your banner text.</p>';
        echo '</div>';
    }
    
    public function font_weight_callback() {
        $settings = $this->get_settings();
        $font_weight = $settings['font_weight'];
        $weight_options = array(
            '300' => 'Light (300)',
            '400' => 'Normal (400)',
            '500' => 'Medium (500)',
            '600' => 'Semi Bold (600)',
            '700' => 'Bold (700)',
            '800' => 'Extra Bold (800)',
            '900' => 'Black (900)'
        );

        echo '<div class="ca-banner-form-group">';
        echo '<select name="' . self::OPTION_NAME . '[font_weight]" id="banner_font_weight" class="ca-banner-select">';
        foreach ($weight_options as $value => $label) {
            $selected = ($font_weight === $value) ? 'selected' : '';
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . $label . '</option>';
        }
        echo '</select>';
        echo '<p class="description">Choose the font weight for your banner text. This affects how bold the text appears.</p>';
        echo '</div>';
    }
    
    public function border_width_callback() {
        $settings = $this->get_settings();
        $border_width = $settings['border_width'];
        echo '<div class="ca-banner-form-group">';
        echo '<div style="display: flex; align-items: center; gap: 10px;">';
        echo '<input type="range" name="' . self::OPTION_NAME . '[border_width]" id="banner_border_width" class="ca-banner-range" value="' . esc_attr($border_width) . '" min="0" max="10" oninput="document.getElementById(\'border-width-value\').textContent = this.value + \'px\'">';
        echo '<span class="ca-banner-range-value" id="border-width-value">' . esc_attr($border_width) . 'px</span>';
        echo '</div>';
        echo '<p class="description">Set the border width for your banner (0-10px).</p>';
        echo '</div>';
    }
    
    public function border_style_callback() {
        $settings = $this->get_settings();
        $border_style = $settings['border_style'];
        $style_options = array(
            'solid' => 'Solid',
            'dashed' => 'Dashed',
            'dotted' => 'Dotted',
            'double' => 'Double',
            'none' => 'None'
        );

        echo '<div class="ca-banner-form-group">';
        echo '<select name="' . self::OPTION_NAME . '[border_style]" id="banner_border_style" class="ca-banner-select">';
        foreach ($style_options as $value => $label) {
            $selected = ($border_style === $value) ? 'selected' : '';
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . $label . '</option>';
        }
        echo '</select>';
        echo '<p class="description">Choose the border style for your banner.</p>';
        echo '</div>';
    }
    
    public function border_color_callback() {
        $settings = $this->get_settings();
        $border_color = $settings['border_color'];
        echo '<div class="ca-banner-form-group">';
        echo '<input type="color" name="' . self::OPTION_NAME . '[border_color]" id="banner_border_color" class="ca-banner-color-picker" value="' . esc_attr($border_color) . '">';
        echo '<p class="description">Choose the border color for your banner.</p>';
        echo '</div>';
    }
    
    public function sitewide_callback() {
        $settings = $this->get_settings();
        $sitewide = $settings['sitewide'];
        
        echo '<fieldset>';
        echo '<label><input type="radio" name="' . self::OPTION_NAME . '[sitewide]" value="1"' . checked(1, $sitewide, false) . '> Display sitewide (all pages)</label><br>';
        echo '<label><input type="radio" name="' . self::OPTION_NAME . '[sitewide]" value="0"' . checked(0, $sitewide, false) . '> Display on specific pages only</label>';
        echo '</fieldset>';
        echo '<p class="description"><strong>Choose how you want to control banner visibility:</strong><br>';
        echo '• <strong>Sitewide:</strong> Banner appears on all pages except those you exclude<br>';
        echo '• <strong>Specific pages:</strong> Banner only appears on pages you specify</p>';
    }
    
    public function urls_callback() {
        $settings = $this->get_settings();
        $urls = $settings['urls'];
        echo '<div class="ca-banner-form-group ca-banner-conditional-field" data-depends-on="banner_sitewide" data-show-when="false">';
        
        // Quick taxonomy options
        echo '<div class="ca-banner-quick-options" style="margin-bottom: 15px;">';
        echo '<h4 style="margin: 0 0 10px 0; font-size: 14px; color: #1d2327;">Quick Options:</h4>';
        echo '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';
        
        $quick_options = array(
            'all_pages' => 'All Pages',
            'all_posts' => 'All Posts', 
            'all_products' => 'All Products (WooCommerce)',
            'home_page' => 'Home Page Only',
            'blog_page' => 'Blog Page Only'
        );
        
        foreach ($quick_options as $value => $label) {
            $checked = (strpos($urls, $value) !== false) ? 'checked' : '';
            echo '<label style="display: flex; align-items: center; gap: 5px; font-size: 13px;">';
            echo '<input type="checkbox" class="ca-banner-quick-option" data-value="' . esc_attr($value) . '" ' . $checked . '>';
            echo $label;
            echo '</label>';
        }
        
        echo '</div>';
        echo '</div>';
        
        // Advanced textarea for custom URLs
        echo '<div class="ca-banner-advanced-urls">';
        echo '<h4 style="margin: 0 0 10px 0; font-size: 14px; color: #1d2327;">Custom URLs:</h4>';
        echo '<textarea name="' . self::OPTION_NAME . '[urls]" id="banner_urls" class="ca-banner-textarea" rows="4" placeholder="/about-us/&#10;/contact/&#10;/products/">' . esc_textarea($urls) . '</textarea>';
        echo '<p class="description"><strong>Include Pages:</strong> Enter one URL per line to display the banner on specific pages. Use relative URLs like <code>/about-us/</code> or <code>/contact/</code>. You can also use the quick options above.</p>';
        echo '</div>';
        
        echo '</div>';
    }
    
    public function exclude_urls_callback() {
        $settings = $this->get_settings();
        $exclude_urls = $settings['exclude_urls'];
        echo '<div class="ca-banner-form-group ca-banner-conditional-field" data-depends-on="banner_sitewide" data-show-when="false">';
        
        // Quick exclude options
        echo '<div class="ca-banner-quick-options" style="margin-bottom: 15px;">';
        echo '<h4 style="margin: 0 0 10px 0; font-size: 14px; color: #1d2327;">Quick Exclude Options:</h4>';
        echo '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';
        
        $exclude_options = array(
            'checkout' => 'Checkout Pages',
            'cart' => 'Cart Pages',
            'my_account' => 'My Account Pages',
            'admin' => 'Admin Pages',
            'login' => 'Login/Register Pages'
        );
        
        foreach ($exclude_options as $value => $label) {
            $checked = (strpos($exclude_urls, $value) !== false) ? 'checked' : '';
            echo '<label style="display: flex; align-items: center; gap: 5px; font-size: 13px;">';
            echo '<input type="checkbox" class="ca-banner-quick-exclude" data-value="' . esc_attr($value) . '" ' . $checked . '>';
            echo $label;
            echo '</label>';
        }
        
        echo '</div>';
        echo '</div>';
        
        // Advanced textarea for custom exclude URLs
        echo '<div class="ca-banner-advanced-urls">';
        echo '<h4 style="margin: 0 0 10px 0; font-size: 14px; color: #1d2327;">Custom Exclude URLs:</h4>';
        echo '<textarea name="' . self::OPTION_NAME . '[exclude_urls]" id="banner_exclude_urls" class="ca-banner-textarea" rows="4" placeholder="/checkout/&#10;/cart/&#10;/admin/">' . esc_textarea($exclude_urls) . '</textarea>';
        echo '<p class="description"><strong>Exclude Pages:</strong> Enter one URL per line to exclude the banner from specific pages. Use relative URLs like <code>/checkout/</code> or <code>/cart/</code>. You can also use the quick options above.</p>';
        echo '</div>';
        
        echo '</div>';
    }
    
    public function disable_mobile_callback() {
        $settings = $this->get_settings();
        $disable_mobile = $settings['disable_mobile'];
        echo '<div class="ca-banner-form-group">';
        echo '<label>';
        echo '<input type="checkbox" name="' . self::OPTION_NAME . '[disable_mobile]" value="1"' . checked(1, $disable_mobile, false) . '> ';
        echo 'Disable banner on mobile devices';
        echo '</label>';
        echo '<p class="description">Check this box to hide the banner on mobile devices (screen width less than 768px).</p>';
        echo '</div>';
    }
    
    public function start_date_callback() {
        $settings = $this->get_settings();
        $start_date = $settings['start_date'];
        echo '<div class="ca-banner-form-group">';
        echo '<label for="banner_start_date">Banner Start Date</label>';
        echo '<input type="datetime-local" name="' . self::OPTION_NAME . '[start_date]" id="banner_start_date" class="ca-banner-input" value="' . esc_attr($start_date) . '">';
        echo '<p class="description">Set when the banner should start displaying. Leave empty to start immediately.</p>';
        echo '</div>';
    }
    
    public function end_date_callback() {
        $settings = $this->get_settings();
        $end_date = $settings['end_date'];
        echo '<div class="ca-banner-form-group">';
        echo '<label for="banner_end_date">Banner End Date</label>';
        echo '<input type="datetime-local" name="' . self::OPTION_NAME . '[end_date]" id="banner_end_date" class="ca-banner-input" value="' . esc_attr($end_date) . '">';
        echo '<p class="description">Set when the banner should stop displaying. Leave empty to display indefinitely.</p>';
        echo '</div>';
    }
    
    public function image_callback() {
        $settings = $this->get_settings();
        $image = $settings['image'];
        echo '<div class="ca-banner-form-group">';
        echo '<input type="text" name="' . self::OPTION_NAME . '[image]" id="banner_image" class="ca-banner-input" value="' . esc_attr($image) . '" placeholder="https://example.com/promotional-image.jpg">';
        echo '<input type="button" class="button button-secondary" value="Upload Image" id="upload_image_button">';
        echo '<p class="description"><strong>Static Image URL:</strong> Enter the URL of your promotional image or use the upload button to select from your media library. This image will display as a full-width banner at the top of your pages.</p>';
        echo '</div>';
    }
    
    public function image_start_date_callback() {
        $settings = $this->get_settings();
        $image_start_date = $settings['image_start_date'];
        echo '<div class="ca-banner-form-group">';
        echo '<input type="datetime-local" name="' . self::OPTION_NAME . '[image_start_date]" id="banner_image_start_date" class="ca-banner-input" value="' . esc_attr($image_start_date) . '">';
        echo '<p class="description"><strong>Static Image Start Date:</strong> Set when the static image banner should start displaying. This is independent from the text banner scheduling. Leave empty to start immediately.</p>';
        echo '</div>';
    }
    
    public function image_end_date_callback() {
        $settings = $this->get_settings();
        $image_end_date = $settings['image_end_date'];
        echo '<div class="ca-banner-form-group">';
        echo '<input type="datetime-local" name="' . self::OPTION_NAME . '[image_end_date]" id="banner_image_end_date" class="ca-banner-input" value="' . esc_attr($image_end_date) . '">';
        echo '<p class="description"><strong>Static Image End Date:</strong> Set when the static image banner should stop displaying. This is independent from the text banner scheduling. Leave empty to display indefinitely.</p>';
        echo '</div>';
    }
    
    // Button Settings Callbacks
    public function button_section_callback() {
        echo '<p>Configure the call-to-action button that appears in your banner.</p>';
    }
    
    public function button_enabled_callback() {
        $settings = $this->get_settings();
        $button_enabled = $settings['button_enabled'];
        echo '<div class="ca-banner-form-group">';
        echo '<div class="ca-banner-toggle">';
        echo '<input type="checkbox" name="' . self::OPTION_NAME . '[button_enabled]" id="banner_button_enabled" value="1"' . checked(1, $button_enabled, false) . '>';
        echo '<label for="banner_button_enabled" class="ca-banner-toggle-slider"></label>';
        echo '<span class="ca-banner-toggle-label">' . ($button_enabled ? 'Enabled' : 'Disabled') . '</span>';
        echo '</div>';
        echo '<p class="description">Enable or disable the call-to-action button in your banner.</p>';
        echo '</div>';
    }
    
    public function button_text_callback() {
        $settings = $this->get_settings();
        $button_text = $settings['button_text'];
        echo '<div class="ca-banner-form-group">';
        echo '<input type="text" name="' . self::OPTION_NAME . '[button_text]" id="banner_button_text" class="ca-banner-input" value="' . esc_attr($button_text) . '" placeholder="e.g., Learn More, Shop Now, Get Started">';
        echo '<p class="description">Enter the text that will appear on the button.</p>';
        echo '</div>';
    }
    
    public function button_link_callback() {
        $settings = $this->get_settings();
        $button_link = $settings['button_link'];
        echo '<div class="ca-banner-form-group">';
        echo '<input type="url" name="' . self::OPTION_NAME . '[button_link]" id="banner_button_link" class="ca-banner-input" value="' . esc_attr($button_link) . '" placeholder="https://example.com">';
        echo '<p class="description">Enter the URL where the button should link to.</p>';
        echo '</div>';
    }
    
    public function button_color_callback() {
        $settings = $this->get_settings();
        $button_color = $settings['button_color'];
        echo '<div class="ca-banner-form-group">';
        echo '<input type="color" name="' . self::OPTION_NAME . '[button_color]" id="banner_button_color" class="ca-banner-color-picker" value="' . esc_attr($button_color) . '">';
        echo '<p class="description">Choose the background color for your button.</p>';
        echo '</div>';
    }
    
    public function button_text_color_callback() {
        $settings = $this->get_settings();
        $button_text_color = $settings['button_text_color'];
        echo '<div class="ca-banner-form-group">';
        echo '<input type="color" name="' . self::OPTION_NAME . '[button_text_color]" id="banner_button_text_color" class="ca-banner-color-picker" value="' . esc_attr($button_text_color) . '">';
        echo '<p class="description">Choose the text color for your button.</p>';
        echo '</div>';
    }
    
    public function button_border_width_callback() {
        $settings = $this->get_settings();
        $button_border_width = $settings['button_border_width'];
        echo '<div class="ca-banner-form-group">';
        echo '<div style="display: flex; align-items: center; gap: 10px;">';
        echo '<input type="range" name="' . self::OPTION_NAME . '[button_border_width]" id="banner_button_border_width" class="ca-banner-range" value="' . esc_attr($button_border_width) . '" min="0" max="10" oninput="document.getElementById(\'button-border-width-value\').textContent = this.value + \'px\'">';
        echo '<span class="ca-banner-range-value" id="button-border-width-value">' . esc_attr($button_border_width) . 'px</span>';
        echo '</div>';
        echo '<p class="description">Set the border width for your button (0-10px).</p>';
        echo '</div>';
    }
    
    public function button_border_color_callback() {
        $settings = $this->get_settings();
        $button_border_color = $settings['button_border_color'];
        echo '<div class="ca-banner-form-group">';
        echo '<input type="color" name="' . self::OPTION_NAME . '[button_border_color]" id="banner_button_border_color" class="ca-banner-color-picker" value="' . esc_attr($button_border_color) . '">';
        echo '<p class="description">Choose the border color for your button.</p>';
        echo '</div>';
    }
    
    public function button_border_radius_callback() {
        $settings = $this->get_settings();
        $button_border_radius = $settings['button_border_radius'];
        echo '<div class="ca-banner-form-group">';
        echo '<div style="display: flex; align-items: center; gap: 10px;">';
        echo '<input type="range" name="' . self::OPTION_NAME . '[button_border_radius]" id="banner_button_border_radius" class="ca-banner-range" value="' . esc_attr($button_border_radius) . '" min="0" max="50" oninput="document.getElementById(\'button-border-radius-value\').textContent = this.value + \'px\'">';
        echo '<span class="ca-banner-range-value" id="button-border-radius-value">' . esc_attr($button_border_radius) . 'px</span>';
        echo '</div>';
        echo '<p class="description">Set the border radius for your button (0-50px). Higher values create more rounded corners.</p>';
        echo '</div>';
    }
    
    public function button_padding_callback() {
        $settings = $this->get_settings();
        $button_padding = $settings['button_padding'];
        echo '<div class="ca-banner-form-group">';
        echo '<div style="display: flex; align-items: center; gap: 10px;">';
        echo '<input type="range" name="' . self::OPTION_NAME . '[button_padding]" id="banner_button_padding" class="ca-banner-range" value="' . esc_attr($button_padding) . '" min="0" max="50" oninput="document.getElementById(\'button-padding-value\').textContent = this.value + \'px\'">';
        echo '<span class="ca-banner-range-value" id="button-padding-value">' . esc_attr($button_padding) . 'px</span>';
        echo '</div>';
        echo '<p class="description">Set the internal padding for your button (0-50px).</p>';
        echo '</div>';
    }
    
    public function button_font_size_callback() {
        $settings = $this->get_settings();
        $button_font_size = $settings['button_font_size'];
        echo '<div class="ca-banner-form-group">';
        echo '<div style="display: flex; align-items: center; gap: 10px;">';
        echo '<input type="range" name="' . self::OPTION_NAME . '[button_font_size]" id="banner_button_font_size" class="ca-banner-range" value="' . esc_attr($button_font_size) . '" min="8" max="24" oninput="document.getElementById(\'button-font-size-value\').textContent = this.value + \'px\'">';
        echo '<span class="ca-banner-range-value" id="button-font-size-value">' . esc_attr($button_font_size) . 'px</span>';
        echo '</div>';
        echo '<p class="description">Adjust the font size for your button text (8-24px).</p>';
        echo '</div>';
    }
    
    public function button_font_weight_callback() {
        $settings = $this->get_settings();
        $button_font_weight = $settings['button_font_weight'];
        $weight_options = array(
            '300' => 'Light (300)',
            '400' => 'Normal (400)',
            '500' => 'Medium (500)',
            '600' => 'Semi Bold (600)',
            '700' => 'Bold (700)',
            '800' => 'Extra Bold (800)',
            '900' => 'Black (900)'
        );

        echo '<div class="ca-banner-form-group">';
        echo '<select name="' . self::OPTION_NAME . '[button_font_weight]" id="banner_button_font_weight" class="ca-banner-select">';
        foreach ($weight_options as $value => $label) {
            $selected = ($button_font_weight === $value) ? 'selected' : '';
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . $label . '</option>';
        }
        echo '</select>';
        echo '<p class="description">Choose the font weight for your button text.</p>';
        echo '</div>';
    }
}
