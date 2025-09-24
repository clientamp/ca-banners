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

        // Display Settings Section
        add_settings_section('banner_display_section', 'Display Settings', array($this, 'display_section_callback'), 'banner-plugin');
        add_settings_field('banner_sitewide', 'Display Sitewide', array($this, 'sitewide_callback'), 'banner-plugin', 'banner_display_section');
        add_settings_field('banner_urls', 'Display on Pages', array($this, 'urls_callback'), 'banner-plugin', 'banner_display_section');
        add_settings_field('banner_exclude_urls', 'Exclude on Pages', array($this, 'exclude_urls_callback'), 'banner-plugin', 'banner_display_section');
        add_settings_field('banner_disable_mobile', 'Disable on Mobile', array($this, 'disable_mobile_callback'), 'banner-plugin', 'banner_display_section');

        // Styling Settings Section
        add_settings_section('banner_styling_section', 'Styling Settings', array($this, 'styling_section_callback'), 'banner-plugin');
        add_settings_field('banner_background_color', 'Background Color', array($this, 'background_color_callback'), 'banner-plugin', 'banner_styling_section');
        add_settings_field('banner_text_color', 'Text Color', array($this, 'text_color_callback'), 'banner-plugin', 'banner_styling_section');
        add_settings_field('banner_font_size', 'Font Size', array($this, 'font_size_callback'), 'banner-plugin', 'banner_styling_section');
        add_settings_field('banner_font_family', 'Font Family', array($this, 'font_family_callback'), 'banner-plugin', 'banner_styling_section');
        add_settings_field('banner_border_width', 'Border Width', array($this, 'border_width_callback'), 'banner-plugin', 'banner_styling_section');
        add_settings_field('banner_border_style', 'Border Style', array($this, 'border_style_callback'), 'banner-plugin', 'banner_styling_section');
        add_settings_field('banner_border_color', 'Border Color', array($this, 'border_color_callback'), 'banner-plugin', 'banner_styling_section');

        // Scheduling Settings Section
        add_settings_section('banner_scheduling_section', 'Scheduling Settings', array($this, 'scheduling_section_callback'), 'banner-plugin');
        add_settings_field('banner_start_date', 'Banner Start Date', array($this, 'start_date_callback'), 'banner-plugin', 'banner_scheduling_section');
        add_settings_field('banner_end_date', 'Banner End Date', array($this, 'end_date_callback'), 'banner-plugin', 'banner_scheduling_section');

        // Image Banner Settings Section
        add_settings_section('banner_image_section', 'Image Banner Settings', array($this, 'image_section_callback'), 'banner-plugin');
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
        echo '<p>Add an image banner with its own scheduling separate from the text banner.</p>';
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
        echo '<label for="banner_message">Banner Message</label>';
        
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
        echo '<label for="banner_repeat">Message Repeats</label>';
        echo '<div style="display: flex; align-items: center; gap: 10px;">';
        echo '<input type="range" name="' . self::OPTION_NAME . '[repeat]" id="banner_repeat" class="ca-banner-range" value="' . esc_attr($repeat) . '" min="1" max="100" oninput="document.getElementById(\'repeat-value\').textContent = this.value">';
        echo '<span class="ca-banner-range-value" id="repeat-value">' . esc_attr($repeat) . '</span>';
        echo '</div>';
        echo '<p class="description">How many times to repeat your message across the banner (1-100). More repeats create a longer scrolling effect.</p>';
        echo '</div>';
    }
    
    public function background_color_callback() {
        $settings = $this->get_settings();
        $background_color = $settings['background_color'];
        echo '<div class="ca-banner-form-group">';
        echo '<label for="banner_background_color">Background Color</label>';
        echo '<input type="color" name="' . self::OPTION_NAME . '[background_color]" id="banner_background_color" class="ca-banner-color-picker" value="' . esc_attr($background_color) . '">';
        echo '<p class="description">Choose the background color for your banner.</p>';
        echo '</div>';
    }
    
    public function text_color_callback() {
        $settings = $this->get_settings();
        $text_color = $settings['text_color'];
        echo '<div class="ca-banner-form-group">';
        echo '<label for="banner_text_color">Text Color</label>';
        echo '<input type="color" name="' . self::OPTION_NAME . '[text_color]" id="banner_text_color" class="ca-banner-color-picker" value="' . esc_attr($text_color) . '">';
        echo '<p class="description">Choose the text color for your banner.</p>';
        echo '</div>';
    }
    
    public function font_size_callback() {
        $settings = $this->get_settings();
        $font_size = $settings['font_size'];
        echo '<div class="ca-banner-form-group">';
        echo '<label for="banner_font_size">Font Size</label>';
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
        echo '<label for="banner_font_family">Font Family</label>';
        echo '<select name="' . self::OPTION_NAME . '[font_family]" id="banner_font_family" class="ca-banner-select">';
        foreach ($font_options as $value => $label) {
            $selected = ($font_family === $value) ? 'selected' : '';
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . $label . '</option>';
        }
        echo '</select>';
        echo '<p class="description">Choose the font family for your banner text.</p>';
        echo '</div>';
    }
    
    public function border_width_callback() {
        $settings = $this->get_settings();
        $border_width = $settings['border_width'];
        echo '<div class="ca-banner-form-group">';
        echo '<label for="banner_border_width">Border Width</label>';
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
        echo '<label for="banner_border_style">Border Style</label>';
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
        echo '<label for="banner_border_color">Border Color</label>';
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
        echo '<div class="ca-banner-form-group">';
        echo '<label for="banner_urls">Display on Pages</label>';
        echo '<textarea name="' . self::OPTION_NAME . '[urls]" id="banner_urls" class="ca-banner-textarea" rows="4" placeholder="/about-us/&#10;/contact/&#10;/products/">' . esc_textarea($urls) . '</textarea>';
        echo '<p class="description"><strong>Include Pages:</strong> Enter one URL per line to display the banner on specific pages. Use relative URLs like <code>/about-us/</code> or <code>/contact/</code>. Leave empty to show on all pages when not using sitewide mode.</p>';
        echo '</div>';
    }
    
    public function exclude_urls_callback() {
        $settings = $this->get_settings();
        $exclude_urls = $settings['exclude_urls'];
        echo '<div class="ca-banner-form-group">';
        echo '<label for="banner_exclude_urls">Exclude on Pages</label>';
        echo '<textarea name="' . self::OPTION_NAME . '[exclude_urls]" id="banner_exclude_urls" class="ca-banner-textarea" rows="4" placeholder="/checkout/&#10;/cart/&#10;/admin/">' . esc_textarea($exclude_urls) . '</textarea>';
        echo '<p class="description"><strong>Exclude Pages:</strong> Enter one URL per line to exclude the banner from specific pages. Use relative URLs like <code>/checkout/</code> or <code>/cart/</code>. Works with both sitewide and specific page modes.</p>';
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
        echo '<label for="banner_image">Banner Image</label>';
        echo '<input type="text" name="' . self::OPTION_NAME . '[image]" id="banner_image" class="ca-banner-input" value="' . esc_attr($image) . '">';
        echo '<input type="button" class="button button-secondary" value="Upload Image" id="upload_image_button">';
        echo '<p class="description">Enter an image URL or use the upload button to select an image from your media library.</p>';
        echo '</div>';
    }
    
    public function image_start_date_callback() {
        $settings = $this->get_settings();
        $image_start_date = $settings['image_start_date'];
        echo '<div class="ca-banner-form-group">';
        echo '<label for="banner_image_start_date">Image Start Date</label>';
        echo '<input type="datetime-local" name="' . self::OPTION_NAME . '[image_start_date]" id="banner_image_start_date" class="ca-banner-input" value="' . esc_attr($image_start_date) . '">';
        echo '<p class="description">Set when the image banner should start displaying. Leave empty to start immediately.</p>';
        echo '</div>';
    }
    
    public function image_end_date_callback() {
        $settings = $this->get_settings();
        $image_end_date = $settings['image_end_date'];
        echo '<div class="ca-banner-form-group">';
        echo '<label for="banner_image_end_date">Image End Date</label>';
        echo '<input type="datetime-local" name="' . self::OPTION_NAME . '[image_end_date]" id="banner_image_end_date" class="ca-banner-input" value="' . esc_attr($image_end_date) . '">';
        echo '<p class="description">Set when the image banner should stop displaying. Leave empty to display indefinitely.</p>';
        echo '</div>';
    }
}
