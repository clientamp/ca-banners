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
     * Log errors using centralized error handler
     * 
     * Centralized error logging method that uses the main plugin's error handler
     * if available, or falls back to basic WordPress error logging.
     * 
     * @since 1.2.7
     * @param string $message The error message to log
     * @param Exception|null $exception Optional exception object
     * @param string $type Error type (use CA_Banners_Error_Handler constants)
     * @param string $severity Error severity (use CA_Banners_Error_Handler constants)
     */
    private function log_error($message, $exception = null, $type = CA_Banners_Error_Handler::TYPE_SYSTEM, $severity = CA_Banners_Error_Handler::SEVERITY_MEDIUM) {
        $ca_banners = CA_Banners::get_instance();
        if ($ca_banners && $ca_banners->error_handler) {
            $ca_banners->error_handler->log_error($message, $type, $severity, $exception);
        } else {
            // Fallback to basic logging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('CA Banners Admin: ' . $message . ($exception ? ' - ' . $exception->getMessage() : ''));
            }
        }
    }
    
    /**
     * Handle security errors with proper logging
     * 
     * Logs security-related errors with high severity and optionally logs
     * security events if WordPress security logging is available.
     * 
     * @since 1.2.7
     * @param string $message The security error message
     * @param array $context Additional context data for the security event
     */
    private function handle_security_error($message, $context = array()) {
        $this->log_error($message, null, CA_Banners_Error_Handler::TYPE_SECURITY, CA_Banners_Error_Handler::SEVERITY_HIGH);
        
        // Log security event
        if (function_exists('wp_log_security_event')) {
            wp_log_security_event('ca_banners_security', $message, $context);
        }
    }
    
    /**
     * Constructor - Initialize admin functionality
     * 
     * Sets up WordPress admin hooks including admin menu, script enqueuing,
     * and AJAX handlers for cache management and error log clearing.
     * 
     * @since 1.2.7
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_ca_banners_clear_error_log', array($this, 'ajax_clear_error_log'));
        
        // Add AJAX handlers for cache management
        add_action('wp_ajax_ca_banners_clear_settings_cache', array($this, 'ajax_clear_settings_cache'));
        add_action('wp_ajax_ca_banners_clear_banner_cache', array($this, 'ajax_clear_banner_cache'));
        add_action('wp_ajax_ca_banners_clear_all_cache', array($this, 'ajax_clear_all_cache'));
    }
    
    /**
     * Add admin menu and submenu pages
     * 
     * Creates the main CA Banners menu page and error log submenu page
     * in the WordPress admin area with appropriate capabilities.
     * 
     * @since 1.2.7
     */
    public function add_admin_menu() {
        add_menu_page(
            'Scrolling Banners', 
            'Scrolling Banners', 
            CA_Banners_Constants::CAPABILITY_MANAGE, 
            CA_Banners_Constants::ADMIN_PAGE_SLUG, 
            array($this, 'display_settings_page'), 
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h18c1.1 0 2 .9 2 2v14c0 1.1-.9 2-2 2H3c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2zm0 2v12h18V5H3zm2 2h14v2H5V7zm0 4h14v2H5v-2zm0 4h10v2H5v-2z"/></svg>')
        );
        
        // Add error log submenu for administrators
        add_submenu_page(
            CA_Banners_Constants::ADMIN_PAGE_SLUG,
            __('Error Log', CA_Banners_Constants::TEXT_DOMAIN),
            __('Error Log', CA_Banners_Constants::TEXT_DOMAIN),
            CA_Banners_Constants::CAPABILITY_MANAGE,
            CA_Banners_Constants::ADMIN_ERROR_LOG_SLUG,
            array($this, 'display_error_log_page')
        );
    }
    
    /**
     * Display the main settings page
     * 
     * Renders the complete settings page with all configuration options
     * organized in cards, including live preview and cache management.
     * 
     * @since 1.2.7
     */
    public function display_settings_page() {
        // Check user capabilities
        if (!current_user_can(CA_Banners_Constants::CAPABILITY_MANAGE)) {
            $this->handle_security_error('Unauthorized access attempt to admin page', array('user_id' => get_current_user_id(), 'action' => 'display_settings_page'));
            wp_die(__('You do not have sufficient permissions to access this page.', CA_Banners_Constants::TEXT_DOMAIN));
        }
        
        $this->add_help_tabs();
        
        ?>
        <div class="wrap ca-banner-admin-wrap">
            <h1>Scrolling Banners Settings</h1>
            
            <form method="post" action="options.php" id="ca-banner-settings-form">
                <?php settings_fields(CA_Banners_Constants::OPTION_NAME); ?>
                <?php wp_nonce_field(CA_Banners_Constants::NONCE_SETTINGS_ACTION, 'ca_banners_nonce'); ?>
                
                <!-- Live Preview -->
                <div class="ca-banner-preview">
                    <span class="ca-banner-preview-label">Live Preview</span>
                    <div class="ca-banner-preview-content" id="ca-banner-preview-content">
                        Your banner message will appear here...
                    </div>
                </div>
                
                <!-- Basic Settings Card -->
                <div class="ca-banner-card">
                    <div class="ca-banner-card-header">
                        <svg class="ca-banner-card-icon" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <h2 class="ca-banner-card-title">Basic Settings</h2>
                    </div>
                    <div class="ca-banner-card-content">
                        <?php do_settings_fields(CA_Banners_Constants::ADMIN_PAGE_SLUG, 'banner_basic_section'); ?>
                    </div>
                </div>
                
                <!-- Style Settings Card -->
                <div class="ca-banner-card">
                    <div class="ca-banner-card-header">
                        <svg class="ca-banner-card-icon" viewBox="0 0 24 24">
                            <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8z"/>
                        </svg>
                        <h2 class="ca-banner-card-title">Style Settings</h2>
                    </div>
                    <div class="ca-banner-card-content">
                        <?php do_settings_fields(CA_Banners_Constants::ADMIN_PAGE_SLUG, 'banner_styling_section'); ?>
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
                        <?php 
                        // Render each field individually to prevent label concatenation
                        $this->render_display_settings_fields();
                        ?>
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
                        <?php do_settings_fields(CA_Banners_Constants::ADMIN_PAGE_SLUG, 'banner_scheduling_section'); ?>
                    </div>
                </div>
                
                <!-- Button Settings Card -->
                <div class="ca-banner-card">
                    <div class="ca-banner-card-header">
                        <svg class="ca-banner-card-icon" viewBox="0 0 24 24">
                            <path d="M19 7h-8v6h8V7zm-2 4h-4V9h4v2zm4-12H3C1.9 3 1 3.9 1 5v14c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H3V5h18v14z"/>
                        </svg>
                        <h2 class="ca-banner-card-title">Button Settings</h2>
                    </div>
                    <div class="ca-banner-card-content">
                        <?php do_settings_fields(CA_Banners_Constants::ADMIN_PAGE_SLUG, 'banner_button_section'); ?>
                    </div>
                </div>
                
                <!-- Static Image Banner Settings Card -->
                <div class="ca-banner-card">
                    <div class="ca-banner-card-header">
                        <svg class="ca-banner-card-icon" viewBox="0 0 24 24">
                            <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                        </svg>
                        <h2 class="ca-banner-card-title">Static Image Banner Settings</h2>
                    </div>
                    <div class="ca-banner-card-content">
                        <?php do_settings_fields(CA_Banners_Constants::ADMIN_PAGE_SLUG, 'banner_image_section'); ?>
                    </div>
                </div>
                
                <div style="margin-top: 20px;">
                    <?php submit_button('Save Changes', 'primary', 'submit', false, array('id' => 'ca-banner-save-btn')); ?>
                </div>
                
                <!-- Cache Management Section -->
                <div class="ca-banner-card" style="margin-top: 20px;">
                    <div class="ca-banner-card-header">
                        <svg class="ca-banner-card-icon" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <h2 class="ca-banner-card-title">Cache Management</h2>
                    </div>
                    <div class="ca-banner-card-content">
                        <p>Manage plugin caching to improve performance and clear cached data when needed.</p>
                        
                        <?php
                        $ca_banners = CA_Banners::get_instance();
                        $cache_stats = $ca_banners->get_cache_stats();
                        ?>
                        
                        <div class="ca-banner-cache-stats">
                            <h4>Cache Statistics</h4>
                            <p><strong>Total Cached Items:</strong> <?php echo esc_html($cache_stats['total_cached_items']); ?></p>
                        </div>
                        
                        <div class="ca-banner-cache-actions">
                            <button type="button" class="button" id="clear-settings-cache">Clear Settings Cache</button>
                            <button type="button" class="button" id="clear-banner-cache">Clear Banner Cache</button>
                            <button type="button" class="button button-secondary" id="clear-all-cache">Clear All Caches</button>
                        </div>
                        
                        <div id="cache-message" style="margin-top: 10px; display: none;"></div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Add help tabs to the settings page
     * 
     * Adds contextual help tabs with quick start guide and troubleshooting
     * information to assist users in configuring the plugin.
     * 
     * @since 1.2.7
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
     * Render display settings fields individually
     * 
     * Renders display-related settings fields separately to prevent
     * label concatenation issues with WordPress settings API.
     * 
     * @since 1.2.7
     */
    private function render_display_settings_fields() {
        $settings = new CA_Banners_Settings();
        
        // Banner Visibility
        echo '<div class="ca-banner-form-group">';
        echo '<label for="banner_sitewide_yes">Banner Visibility</label>';
        $settings->sitewide_callback();
        echo '</div>';
        
        // Include Pages
        echo '<div class="ca-banner-form-group ca-banner-conditional-field" data-depends-on="banner_sitewide" data-show-when="false">';
        echo '<label for="banner_urls">Include Pages</label>';
        $settings->urls_callback();
        echo '</div>';
        
        // Exclude Pages
        echo '<div class="ca-banner-form-group ca-banner-conditional-field" data-depends-on="banner_sitewide" data-show-when="false">';
        echo '<label for="banner_exclude_urls">Exclude Pages</label>';
        $settings->exclude_urls_callback();
        echo '</div>';
        
        // Mobile Display
        echo '<div class="ca-banner-form-group">';
        echo '<label for="banner_disable_mobile">Mobile Display</label>';
        $settings->disable_mobile_callback();
        echo '</div>';
    }
    
    /**
     * Enqueue admin scripts and styles
     * 
     * Loads admin-specific CSS and JavaScript files only on the CA Banners
     * admin page and for users with appropriate capabilities.
     * 
     * @since 1.2.7
     * @param string $hook The current admin page hook
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our admin page and for authorized users
        if ($hook !== 'toplevel_page_' . CA_Banners_Constants::ADMIN_PAGE_SLUG || !current_user_can(CA_Banners_Constants::CAPABILITY_MANAGE)) {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_style('ca-banners-admin', CA_BANNERS_PLUGIN_URL . 'admin/css/admin.css', array(), CA_BANNERS_VERSION);
        wp_enqueue_script('ca-banners-admin', CA_BANNERS_PLUGIN_URL . 'admin/js/admin.js', array('jquery'), CA_BANNERS_VERSION, true);
        
        // Localize script with cache management nonce
        wp_localize_script('ca-banners-admin', 'ca_banners_admin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(CA_Banners_Constants::NONCE_CACHE_ACTION)
        ));
    }
    
    /**
     * AJAX handler for clearing settings cache
     * 
     * Handles AJAX requests to clear the settings cache. Includes security
     * checks for nonce verification and user capabilities.
     * 
     * @since 1.2.7
     */
    public function ajax_clear_settings_cache() {
        // Check nonce and capabilities
        if (!wp_verify_nonce($_POST['nonce'], CA_Banners_Constants::NONCE_CACHE_ACTION) || !current_user_can(CA_Banners_Constants::CAPABILITY_MANAGE)) {
            wp_die('Security check failed');
        }
        
        $ca_banners = CA_Banners::get_instance();
        $ca_banners->invalidate_settings_cache();
        
        wp_send_json_success(array('message' => 'Settings cache cleared successfully'));
    }
    
    /**
     * AJAX handler for clearing banner cache
     * 
     * Handles AJAX requests to clear the banner HTML cache. Includes security
     * checks for nonce verification and user capabilities.
     * 
     * @since 1.2.7
     */
    public function ajax_clear_banner_cache() {
        // Check nonce and capabilities
        if (!wp_verify_nonce($_POST['nonce'], CA_Banners_Constants::NONCE_CACHE_ACTION) || !current_user_can(CA_Banners_Constants::CAPABILITY_MANAGE)) {
            wp_die('Security check failed');
        }
        
        $ca_banners = CA_Banners::get_instance();
        $ca_banners->invalidate_banner_cache();
        
        wp_send_json_success(array('message' => 'Banner cache cleared successfully'));
    }
    
    /**
     * AJAX handler for clearing all caches
     * 
     * Handles AJAX requests to clear all plugin caches. Includes security
     * checks for nonce verification and user capabilities.
     * 
     * @since 1.2.7
     */
    public function ajax_clear_all_cache() {
        // Check nonce and capabilities
        if (!wp_verify_nonce($_POST['nonce'], CA_Banners_Constants::NONCE_CACHE_ACTION) || !current_user_can(CA_Banners_Constants::CAPABILITY_MANAGE)) {
            wp_die('Security check failed');
        }
        
        $ca_banners = CA_Banners::get_instance();
        $ca_banners->clear_all_caches();
        
        wp_send_json_success(array('message' => 'All caches cleared successfully'));
    }
    
    /**
     * Display the error log page
     * 
     * Renders the error log page with error statistics, error list table,
     * and functionality to clear the error log.
     * 
     * @since 1.2.7
     */
    public function display_error_log_page() {
        // Check user capabilities
        if (!current_user_can(CA_Banners_Constants::CAPABILITY_MANAGE)) {
            $this->handle_security_error('Unauthorized access attempt to error log page', array('user_id' => get_current_user_id(), 'action' => 'display_error_log_page'));
            wp_die(__('You do not have sufficient permissions to access this page.', CA_Banners_Constants::TEXT_DOMAIN));
        }
        
        $ca_banners = CA_Banners::get_instance();
        $error_handler = $ca_banners->error_handler;
        
        if (!$error_handler) {
            echo '<div class="wrap"><h1>' . __('Error Log', CA_Banners_Constants::TEXT_DOMAIN) . '</h1><p>' . __('Error handler not available.', CA_Banners_Constants::TEXT_DOMAIN) . '</p></div>';
            return;
        }
        
        $error_log = $error_handler->get_error_log(CA_Banners_Constants::MAX_ERROR_LOG_ENTRIES);
        $error_stats = $error_handler->get_error_stats();
        
        ?>
        <div class="wrap">
            <h1><?php _e('Scrolling Banners Error Log', CA_Banners_Constants::TEXT_DOMAIN); ?></h1>
            
            <div class="ca-banner-error-stats" style="background: #fff; padding: 15px; margin: 20px 0; border: 1px solid #ccd0d4; border-radius: 4px;">
                <h3><?php _e('Error Statistics', CA_Banners_Constants::TEXT_DOMAIN); ?></h3>
                <p><strong><?php _e('Total Errors:', CA_Banners_Constants::TEXT_DOMAIN); ?></strong> <?php echo esc_html($error_stats['total_errors']); ?></p>
                <p><strong><?php _e('Recent Errors (1 hour):', CA_Banners_Constants::TEXT_DOMAIN); ?></strong> <?php echo esc_html($error_stats['recent_errors']); ?></p>
                
                <?php if (!empty($error_stats['by_severity'])): ?>
                <p><strong><?php _e('By Severity:', CA_Banners_Constants::TEXT_DOMAIN); ?></strong></p>
                <ul>
                    <?php foreach ($error_stats['by_severity'] as $severity => $count): ?>
                        <li><?php echo esc_html(ucfirst($severity)); ?>: <?php echo esc_html($count); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                
                <button type="button" class="button" id="clear-error-log"><?php _e('Clear Error Log', CA_Banners_Constants::TEXT_DOMAIN); ?></button>
            </div>
            
            <?php if (empty($error_log)): ?>
                <div class="notice notice-success">
                    <p><?php _e('No errors logged.', CA_Banners_Constants::TEXT_DOMAIN); ?></p>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Timestamp', CA_Banners_Constants::TEXT_DOMAIN); ?></th>
                            <th><?php _e('Severity', CA_Banners_Constants::TEXT_DOMAIN); ?></th>
                            <th><?php _e('Type', CA_Banners_Constants::TEXT_DOMAIN); ?></th>
                            <th><?php _e('Message', CA_Banners_Constants::TEXT_DOMAIN); ?></th>
                            <th><?php _e('Context', CA_Banners_Constants::TEXT_DOMAIN); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($error_log as $error): ?>
                            <tr>
                                <td><?php echo esc_html($error['timestamp']); ?></td>
                                <td>
                                    <span class="ca-banner-severity ca-banner-severity-<?php echo esc_attr($error['severity']); ?>">
                                        <?php echo esc_html(ucfirst($error['severity'])); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(ucfirst($error['type'])); ?></td>
                                <td><?php echo esc_html($error['message']); ?></td>
                                <td>
                                    <?php if (isset($error['context']['is_admin'])): ?>
                                        <?php echo $error['context']['is_admin'] ? __('Admin', CA_Banners_Constants::TEXT_DOMAIN) : __('Frontend', CA_Banners_Constants::TEXT_DOMAIN); ?>
                                    <?php endif; ?>
                                    <?php if (isset($error['context']['user_id'])): ?>
                                        | <?php printf(__('User: %d', CA_Banners_Constants::TEXT_DOMAIN), $error['context']['user_id']); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if (isset($error['exception_message'])): ?>
                                <tr class="ca-banner-error-details">
                                    <td colspan="5" style="padding-left: 30px; font-family: monospace; font-size: 12px; color: #666;">
                                        <strong><?php _e('Exception:', CA_Banners_Constants::TEXT_DOMAIN); ?></strong> <?php echo esc_html($error['exception_message']); ?><br>
                                        <strong><?php _e('File:', CA_Banners_Constants::TEXT_DOMAIN); ?></strong> <?php echo esc_html($error['exception_file']); ?>:<?php echo esc_html($error['exception_line']); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <style>
        .ca-banner-severity {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .ca-banner-severity-critical { background: #dc3232; color: white; }
        .ca-banner-severity-high { background: #ff6b35; color: white; }
        .ca-banner-severity-medium { background: #ffb900; color: black; }
        .ca-banner-severity-low { background: #00a32a; color: white; }
        .ca-banner-error-details td { border-top: none !important; }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#clear-error-log').on('click', function() {
                if (confirm('<?php _e('Are you sure you want to clear the error log?', CA_Banners_Constants::TEXT_DOMAIN); ?>')) {
                    $.post(ajaxurl, {
                        action: '<?php echo CA_Banners_Constants::AJAX_CLEAR_ERROR_LOG; ?>',
                        nonce: '<?php echo wp_create_nonce(CA_Banners_Constants::NONCE_ERROR_LOG_ACTION); ?>'
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('<?php _e('Error clearing log:', CA_Banners_Constants::TEXT_DOMAIN); ?> ' + response.data);
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handler for clearing error log
     * 
     * Handles AJAX requests to clear the error log. Includes security
     * checks for nonce verification and user capabilities.
     * 
     * @since 1.2.7
     */
    public function ajax_clear_error_log() {
        // Check nonce and capabilities
        if (!wp_verify_nonce($_POST['nonce'], CA_Banners_Constants::NONCE_ERROR_LOG_ACTION) || !current_user_can(CA_Banners_Constants::CAPABILITY_MANAGE)) {
            $this->handle_security_error('Security check failed for error log clear', array('user_id' => get_current_user_id(), 'action' => 'clear_error_log'));
            wp_die('Security check failed');
        }
        
        $ca_banners = CA_Banners::get_instance();
        if ($ca_banners->error_handler) {
            $ca_banners->error_handler->clear_error_log();
        }
        
        wp_send_json_success(array('message' => 'Error log cleared successfully'));
    }
}
