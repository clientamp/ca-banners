<?php
/**
 * Frontend class for handling banner display
 *
 * @package CA_Banners
 * @since 1.2.7
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CA Banners Frontend class
 */
class CA_Banners_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Debug: Check if frontend class is being constructed
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('CA Banners: Frontend class constructor called');
        }
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 5);
        add_action('wp_head', array($this, 'render_banner'), 1);
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        $settings = get_option('banner_plugin_settings');
        $enabled = isset($settings['enabled']) ? $settings['enabled'] : false;

        if (!$enabled) {
            return;
        }

        wp_enqueue_style('ca-banners-frontend', CA_BANNERS_PLUGIN_URL . 'public/css/frontend.css', array(), CA_BANNERS_VERSION);
    }
    
    /**
     * Render banner
     */
    public function render_banner() {
        // Debug: Check if render_banner is being called
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('CA Banners: render_banner() called');
        }
        
        $settings = get_option('banner_plugin_settings');
        
        // Debug: Check settings
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('CA Banners: Settings = ' . print_r($settings, true));
        }
        
        // Create validator instance if not available
        if (!class_exists('CA_Banners_Validator')) {
            require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-validator.php';
        }
        $validator = new CA_Banners_Validator();
        
        // Validate and sanitize settings
        $validated_settings = $validator->validate_settings($settings);
        
        if (!$validated_settings['enabled'] || empty($validated_settings['message'])) {
            return;
        }
        
        // Handle theme conflicts
        $this->handle_theme_conflicts();
        
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
        
        // Check scheduling
        if (!class_exists('CA_Banners_Scheduler')) {
            require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-scheduler.php';
        }
        $scheduler = new CA_Banners_Scheduler();
        if (!$scheduler->is_banner_scheduled($validated_settings)) {
            return;
        }
        
        // Check URL matching
        if (!class_exists('CA_Banners_URL_Matcher')) {
            require_once CA_BANNERS_PLUGIN_DIR . 'includes/class-url-matcher.php';
        }
        $url_matcher = new CA_Banners_URL_Matcher();
        
        $current_url_raw = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $current_url_raw = strtok($current_url_raw, '?'); // Remove query parameters
        $current_url_raw = strtok($current_url_raw, '#'); // Remove fragments
        
        if (!$url_matcher->should_display_banner($current_url_raw, $sitewide, $urls, $exclude_urls)) {
            return;
        }
        
        // Enhanced debug output for administrators
        if (current_user_can('manage_options') && defined('WP_DEBUG') && WP_DEBUG) {
            echo '<!-- CA Banner Debug Info: ';
            echo 'Version: ' . CA_BANNERS_VERSION . ', ';
            echo 'Current URL: ' . esc_html($url_matcher->normalize_url($current_url_raw)) . ', ';
            echo 'Raw URL: ' . esc_html($current_url_raw) . ', ';
            echo 'Sitewide Mode: ' . ($sitewide ? 'Yes' : 'No') . ', ';
            echo 'Should Display: Yes, ';
            echo 'Enabled: ' . ($validated_settings['enabled'] ? 'Yes' : 'No') . ', ';
            echo 'Message Length: ' . strlen($message) . ' chars';
            echo ' -->';
        }
        
        $this->render_banner_script($message, $repeat, $background_color, $text_color, $font_size, $font_family, $border_width, $border_style, $border_color, $disable_mobile, $start_date, $end_date, $image, $image_start_date, $image_end_date);
    }
    
    /**
     * Render banner JavaScript
     */
    private function render_banner_script($message, $repeat, $background_color, $text_color, $font_size, $font_family, $border_width, $border_style, $border_color, $disable_mobile, $start_date, $end_date, $image, $image_start_date, $image_end_date) {
        echo '<script>';
        echo 'var caBannerConfig = {';
        echo 'message: ' . json_encode($message . ' &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; ') . ',';
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
     * Detect potential theme conflicts and apply fixes
     */
    private function handle_theme_conflicts() {
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
}
