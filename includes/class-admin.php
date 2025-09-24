<?php
/**
 * Admin class for handling WordPress admin interface
 *
 * @package CA_Banners
 * @since 1.2.7
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CA Banners Admin class
 */
class CA_Banners_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'CA Banners', 
            'CA Banners', 
            'manage_options', 
            'banner-plugin', 
            array($this, 'display_settings_page'), 
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h18c1.1 0 2 .9 2 2v14c0 1.1-.9 2-2 2H3c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2zm0 2v12h18V5H3zm2 2h14v2H5V7zm0 4h14v2H5v-2zm0 4h10v2H5v-2z"/></svg>')
        );
    }
    
    /**
     * Display settings page
     */
    public function display_settings_page() {
        $this->add_help_tabs();
        
        ?>
        <div class="wrap ca-banner-admin-wrap">
            <h1>CA Banners Settings</h1>
            
            <form method="post" action="options.php" id="ca-banner-settings-form">
                <?php settings_fields('banner_plugin_settings'); ?>
                
                <!-- Basic Settings Card -->
                <div class="ca-banner-card">
                    <div class="ca-banner-card-header">
                        <svg class="ca-banner-card-icon" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <h2 class="ca-banner-card-title">Basic Settings</h2>
                    </div>
                    <div class="ca-banner-card-content">
                        <?php do_settings_fields('banner-plugin', 'banner_basic_section'); ?>
                    </div>
                </div>
                
                <!-- Display Settings Card -->
                <div class="ca-banner-card">
                    <div class="ca-banner-card-header">
                        <svg class="ca-banner-card-icon" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        <h2 class="ca-banner-card-title">Display Settings</h2>
                    </div>
                    <div class="ca-banner-card-content">
                        <?php do_settings_fields('banner-plugin', 'banner_display_section'); ?>
                    </div>
                </div>
                
                <!-- Styling Settings Card -->
                <div class="ca-banner-card">
                    <div class="ca-banner-card-header">
                        <svg class="ca-banner-card-icon" viewBox="0 0 24 24">
                            <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8z"/>
                        </svg>
                        <h2 class="ca-banner-card-title">Styling Settings</h2>
                    </div>
                    <div class="ca-banner-card-content">
                        <?php do_settings_fields('banner-plugin', 'banner_styling_section'); ?>
                        
                        <!-- Live Preview -->
                        <div class="ca-banner-preview">
                            <span class="ca-banner-preview-label">Live Preview</span>
                            <div class="ca-banner-preview-content" id="ca-banner-preview-content">
                                Your banner message will appear here...
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Scheduling Settings Card -->
                <div class="ca-banner-card">
                    <div class="ca-banner-card-header">
                        <svg class="ca-banner-card-icon" viewBox="0 0 24 24">
                            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                        </svg>
                        <h2 class="ca-banner-card-title">Scheduling Settings</h2>
                    </div>
                    <div class="ca-banner-card-content">
                        <?php do_settings_fields('banner-plugin', 'banner_scheduling_section'); ?>
                    </div>
                </div>
                
                <!-- Image Banner Settings Card -->
                <div class="ca-banner-card">
                    <div class="ca-banner-card-header">
                        <svg class="ca-banner-card-icon" viewBox="0 0 24 24">
                            <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                        </svg>
                        <h2 class="ca-banner-card-title">Image Banner Settings</h2>
                    </div>
                    <div class="ca-banner-card-content">
                        <?php do_settings_fields('banner-plugin', 'banner_image_section'); ?>
                    </div>
                </div>
                
                <div style="margin-top: 20px;">
                    <?php submit_button('Save Changes', 'primary', 'submit', false, array('id' => 'ca-banner-save-btn')); ?>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Add help tabs
     */
    private function add_help_tabs() {
        $screen = get_current_screen();
        
        $help_content = '<h3>Quick Start Guide</h3>
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
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our admin page
        if ($hook !== 'toplevel_page_banner-plugin') {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_style('ca-banners-admin', CA_BANNERS_PLUGIN_URL . 'admin/css/admin.css', array(), CA_BANNERS_VERSION);
        wp_enqueue_script('ca-banners-admin', CA_BANNERS_PLUGIN_URL . 'admin/js/admin.js', array('jquery'), CA_BANNERS_VERSION, true);
    }
}
