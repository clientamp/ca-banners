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
     * Constructor - Initialize frontend functionality
     * 
     * Sets up WordPress frontend hooks for script enqueuing and banner rendering.
     * Uses appropriate hook priorities defined in constants.
     * 
     * @since 1.2.7
     */
    public function __construct() {
        // Debug: Check if frontend class is being constructed
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('view_ca_banners')) {
            error_log('CA Banners: Frontend class constructor called');
        }
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), CA_Banners_Constants::HOOK_PRIORITY_ENQUEUE_SCRIPTS);
        add_action('wp_head', array($this, 'render_banner'), CA_Banners_Constants::HOOK_PRIORITY_RENDER_BANNER);
    }
    
    /**
     * Enqueue frontend scripts and styles
     * 
     * Loads the frontend CSS file with cache busting to ensure users
     * always get the latest styles.
     * 
     * @since 1.2.7
     */
    public function enqueue_scripts() {
        // Always enqueue CSS to ensure it's available with cache busting
        wp_enqueue_style('ca-banners-frontend', CA_BANNERS_PLUGIN_URL . CA_Banners_Constants::DIR_PUBLIC . '/' . CA_Banners_Constants::DIR_CSS . '/' . CA_Banners_Constants::CSS_FILE_EXTENSION, array(), CA_BANNERS_VERSION . '-' . time() . '-fix-duplicate');
    }
    
    /**
     * Render banner on frontend
     * 
     * Main method for rendering banners on the frontend. Handles settings
     * validation, scheduling checks, URL matching, caching, and banner generation.
     * 
     * @since 1.2.7
     */
    public function render_banner() {
        try {
            // Debug: Check if render_banner is being called
            if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('view_ca_banners')) {
                error_log('CA Banners: render_banner() called');
            }
            
        // Debug comment only for administrators in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can(CA_Banners_Constants::CAPABILITY_VIEW)) {
            echo '<!-- Scrolling Banners: render_banner() called -->';
        }
            
            $settings = get_option(CA_Banners_Constants::OPTION_NAME);
            
            // Use settings class to get proper defaults
            $settings_class = new CA_Banners_Settings();
            $settings = $settings_class->get_settings();
            
            // Debug: Check settings
            if (defined('WP_DEBUG') && WP_DEBUG && current_user_can(CA_Banners_Constants::CAPABILITY_VIEW)) {
                error_log(CA_Banners_Constants::DEBUG_LOG_PREFIX . ': Settings = ' . print_r($settings, true));
            }
            
            // Create validator instance
            $validator = new CA_Banners_Validator();
            
            // Validate and sanitize settings
            $validated_settings = $validator->validate_settings($settings);
            
            if (!$validated_settings['enabled'] || empty($validated_settings['message'])) {
                if (defined('WP_DEBUG') && WP_DEBUG && current_user_can(CA_Banners_Constants::CAPABILITY_VIEW)) {
                    error_log(CA_Banners_Constants::DEBUG_LOG_PREFIX . ': Banner not enabled or no message after validation. Validated settings: ' . print_r($validated_settings, true));
                }
                if (defined('WP_DEBUG') && WP_DEBUG && current_user_can(CA_Banners_Constants::CAPABILITY_VIEW)) {
                    echo '<!-- Scrolling Banners: Banner not enabled or no message -->';
                }
                return;
            }
            
            // Handle theme conflicts
            $this->handle_theme_conflicts();
            
            // Use validated settings
            $message = $validated_settings['message'];
            $repeat = $validated_settings['repeat'];
            $speed = $validated_settings['speed'];
            $background_color = $validated_settings['background_color'];
            $text_color = $validated_settings['text_color'];
        $font_size = $validated_settings['font_size'];
        $font_family = $validated_settings['font_family'];
        $font_weight = $validated_settings['font_weight'];
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
        $sticky = $validated_settings['sticky'];
        
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
        $button_lock_enabled = $validated_settings['button_lock_enabled'];
        $button_lock_position = $validated_settings['button_lock_position'];
        $button_gap = $validated_settings['button_gap'];
        $vertical_padding = $validated_settings['vertical_padding'];
        $button_new_window = $validated_settings['button_new_window'];
        $link_color = $validated_settings['link_color'] ?? '#0000ff';

        // Check scheduling
        $scheduler = new CA_Banners_Scheduler();
        if (!$scheduler->is_banner_scheduled($validated_settings)) {
            return;
        }
        
        // Check URL matching
        $url_matcher = new CA_Banners_URL_Matcher();
        
        $current_url_raw = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $current_url_raw = strtok($current_url_raw, '?'); // Remove query parameters
        $current_url_raw = strtok($current_url_raw, '#'); // Remove fragments
        
            if (!$url_matcher->should_display_banner($current_url_raw, $sitewide, $urls, $exclude_urls)) {
                if (defined('WP_DEBUG') && WP_DEBUG && current_user_can(CA_Banners_Constants::CAPABILITY_VIEW)) {
                    echo '<!-- Scrolling Banners: URL matcher says not to display banner -->';
                }
                return;
            }
        
        // Enhanced debug output for administrators
        if (current_user_can(CA_Banners_Constants::CAPABILITY_VIEW) && defined('WP_DEBUG') && WP_DEBUG) {
            echo '<!-- Scrolling Banners Debug Info: ';
            echo 'Version: ' . CA_BANNERS_VERSION . ', ';
            echo 'Current URL: ' . esc_html($url_matcher->normalize_url($current_url_raw)) . ', ';
            echo 'Raw URL: ' . esc_html($current_url_raw) . ', ';
            echo 'Sitewide Mode: ' . ($sitewide ? 'Yes' : 'No') . ', ';
            echo 'Should Display: Yes, ';
            echo 'Enabled: ' . ($validated_settings['enabled'] ? 'Yes' : 'No') . ', ';
            echo 'Message Length: ' . strlen($message) . ' chars';
            echo ' -->';
        }
        
            if (defined('WP_DEBUG') && WP_DEBUG && current_user_can(CA_Banners_Constants::CAPABILITY_VIEW)) {
                echo '<!-- Scrolling Banners: About to render banner script -->';
            }
            
            // Generate settings hash for caching
            $settings_hash = $this->generate_settings_hash($validated_settings);
            
            // Try to get cached banner HTML
            $ca_banners = CA_Banners::get_instance();
            $cached_html = $ca_banners->get_cached_banner_html($settings_hash);
            
            if ($cached_html !== false) {
                // Cache hit - output cached HTML
                echo $cached_html;
                
                if (defined('WP_DEBUG') && WP_DEBUG && current_user_can(CA_Banners_Constants::CAPABILITY_VIEW)) {
                    echo '<!-- Scrolling Banners: Banner HTML served from cache -->';
                }
            } else {
                // Cache miss - generate and cache HTML
                ob_start();
                $this->render_banner_script($message, $repeat, $speed, $background_color, $text_color, $font_size, $font_family, $font_weight, $border_width, $border_style, $border_color, $disable_mobile, $start_date, $end_date, $image, $image_start_date, $image_end_date, $button_enabled, $button_text, $button_link, $button_color, $button_text_color, $button_border_width, $button_border_color, $button_border_radius, $button_padding, $button_font_size, $button_font_weight, $sticky, $button_lock_enabled, $button_lock_position, $button_gap, $vertical_padding, $button_new_window, $link_color);
                $banner_html = ob_get_clean();
                
                // Cache the generated HTML
                $ca_banners->set_cached_banner_html($settings_hash, $banner_html);
                
                // Output the HTML
                echo $banner_html;
                
                if (defined('WP_DEBUG') && WP_DEBUG && current_user_can(CA_Banners_Constants::CAPABILITY_VIEW)) {
                    echo '<!-- Scrolling Banners: Banner HTML generated and cached -->';
                }
            }
        
        } catch (Exception $e) {
            $this->log_error('Failed to render banner', $e);
            // Don't break the site, just log the error
        }
    }
    
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
                error_log('CA Banners Frontend: ' . $message . ($exception ? ' - ' . $exception->getMessage() : ''));
            }
        }
    }
    
    /**
     * Generate settings hash for caching
     * 
     * Creates a unique hash based on relevant settings to use as a cache key.
     * This ensures cache invalidation when settings change.
     * 
     * @since 1.2.7
     * @param array $settings The settings array to hash
     * @return string MD5 hash of the settings
     */
    private function generate_settings_hash($settings) {
        // Create a hash based on relevant settings for caching
        $cache_data = array(
            'enabled' => $settings['enabled'] ?? false,
            'message' => $settings['message'] ?? '',
            'repeat' => $settings['repeat'] ?? 10,
            'speed' => $settings['speed'] ?? 60,
            'background_color' => $settings['background_color'] ?? '#729946',
            'text_color' => $settings['text_color'] ?? '#000000',
            'font_size' => $settings['font_size'] ?? 16,
            'font_family' => $settings['font_family'] ?? 'Arial',
            'border_width' => $settings['border_width'] ?? 0,
            'border_style' => $settings['border_style'] ?? 'solid',
            'border_color' => $settings['border_color'] ?? '#000000',
            'sitewide' => $settings['sitewide'] ?? true,
            'disable_mobile' => $settings['disable_mobile'] ?? false,
            'urls' => $settings['urls'] ?? '',
            'exclude_urls' => $settings['exclude_urls'] ?? '',
            'start_date' => $settings['start_date'] ?? '',
            'end_date' => $settings['end_date'] ?? '',
            'button_enabled' => $settings['button_enabled'] ?? false,
            'button_text' => $settings['button_text'] ?? '',
            'button_link' => $settings['button_link'] ?? '',
            'button_color' => $settings['button_color'] ?? '#ce7a31',
            'button_text_color' => $settings['button_text_color'] ?? '#ffffff',
            'button_border_width' => $settings['button_border_width'] ?? 0,
            'button_border_radius' => $settings['button_border_radius'] ?? 4,
            'button_padding' => $settings['button_padding'] ?? 8,
            'button_font_size' => $settings['button_font_size'] ?? 14,
            'button_font_weight' => $settings['button_font_weight'] ?? '600',
            'button_lock_enabled' => $settings['button_lock_enabled'] ?? false,
            'button_lock_position' => $settings['button_lock_position'] ?? 'left',
            'button_gap' => $settings['button_gap'] ?? 15,
            'vertical_padding' => $settings['vertical_padding'] ?? 10,
            'button_new_window' => $settings['button_new_window'] ?? false,
            'link_color' => $settings['link_color'] ?? '#0000ff',
            'image' => $settings['image'] ?? '',
            'image_start_date' => $settings['image_start_date'] ?? '',
            'image_end_date' => $settings['image_end_date'] ?? '',
            'sticky' => $settings['sticky'] ?? false
        );
        
        return md5(serialize($cache_data));
    }
    
    /**
     * Direct banner rendering (legacy method)
     * 
     * Simplified banner rendering method that bypasses some validation
     * and caching for backward compatibility.
     * 
     * @since 1.2.7
     */
    public function render_banner_direct() {
        // Debug: Check if direct render is being called
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('view_ca_banners')) {
            error_log('CA Banners: render_banner_direct() called');
        }
        
        $settings = get_option('banner_plugin_settings');
        
        // Use settings class to get proper defaults
        $settings_class = new CA_Banners_Settings();
        $settings = $settings_class->get_settings();
        
        // Debug: Check settings
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can(CA_Banners_Constants::CAPABILITY_VIEW)) {
            error_log(CA_Banners_Constants::DEBUG_LOG_PREFIX . ' Direct render settings = ' . print_r($settings, true));
        }
        
        if (!$settings || !isset($settings['enabled']) || !$settings['enabled'] || empty($settings['message'])) {
            if (defined('WP_DEBUG') && WP_DEBUG && current_user_can(CA_Banners_Constants::CAPABILITY_VIEW)) {
                error_log(CA_Banners_Constants::DEBUG_LOG_PREFIX . ' Banner not enabled or no message. Settings: ' . print_r($settings, true));
            }
            return;
        }
        
        // Simple banner rendering (like original)
        $message = $settings['message'];
        $repeat = isset($settings['repeat']) ? intval($settings['repeat']) : 10;
        $speed = isset($settings['speed']) ? intval($settings['speed']) : 60;
        $background_color = isset($settings['background_color']) ? $settings['background_color'] : '#729946';
        $text_color = isset($settings['text_color']) ? $settings['text_color'] : '#000000';
        $font_size = isset($settings['font_size']) ? intval($settings['font_size']) : 16;
        $font_family = isset($settings['font_family']) ? $settings['font_family'] : 'Arial';
        $font_weight = isset($settings['font_weight']) ? $settings['font_weight'] : '600';
        $border_width = isset($settings['border_width']) ? intval($settings['border_width']) : 0;
        $border_style = isset($settings['border_style']) ? $settings['border_style'] : 'solid';
        $border_color = isset($settings['border_color']) ? $settings['border_color'] : '#000000';
        $disable_mobile = isset($settings['disable_mobile']) ? $settings['disable_mobile'] : false;
        
        // Button settings
        $button_enabled = isset($settings['button_enabled']) ? $settings['button_enabled'] : false;
        $button_text = isset($settings['button_text']) ? $settings['button_text'] : '';
        $button_link = isset($settings['button_link']) ? $settings['button_link'] : '';
        $button_color = isset($settings['button_color']) ? $settings['button_color'] : '#ce7a31';
        $button_text_color = isset($settings['button_text_color']) ? $settings['button_text_color'] : '#ffffff';
        $button_border_width = isset($settings['button_border_width']) ? intval($settings['button_border_width']) : 0;
        $button_border_color = isset($settings['button_border_color']) ? $settings['button_border_color'] : '#ce7a31';
        $button_border_radius = isset($settings['button_border_radius']) ? intval($settings['button_border_radius']) : 4;
        $button_padding = isset($settings['button_padding']) ? intval($settings['button_padding']) : 8;
        $button_font_size = isset($settings['button_font_size']) ? intval($settings['button_font_size']) : 14;
        $button_font_weight = isset($settings['button_font_weight']) ? $settings['button_font_weight'] : '600';
        
        // Create single message (JavaScript will handle repetition) - Remove line breaks
        $single_message = str_replace(["\r\n", "\r", "\n"], ' ', $message) . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ';
        
        echo '<script>';
        echo 'var caBannerConfig = {';
        echo 'message: ' . wp_json_encode($single_message) . ',';
        echo 'repeat: ' . $repeat . ',';
        echo 'speed: ' . intval($speed ?: 60) . ',';
        echo 'backgroundColor: "' . esc_js($background_color) . '",';
        echo 'textColor: "' . esc_js($text_color) . '",';
        echo 'fontSize: ' . intval($font_size ?: 16) . ',';
        echo 'fontFamily: "' . esc_js($font_family) . '",';
        echo 'fontWeight: "' . esc_js($font_weight ?: '600') . '",';
        echo 'borderWidth: ' . $border_width . ',';
        echo 'borderStyle: "' . esc_js($border_style) . '",';
        echo 'borderColor: "' . esc_js($border_color) . '",';
        echo 'disableMobile: ' . ($disable_mobile ? 'true' : 'false') . ',';
        echo 'buttonEnabled: ' . ($button_enabled ? 'true' : 'false') . ',';
        echo 'buttonText: "' . esc_js($button_text) . '",';
        echo 'buttonLink: "' . esc_js($button_link) . '",';
        echo 'buttonColor: "' . esc_js($button_color) . '",';
        echo 'buttonTextColor: "' . esc_js($button_text_color) . '",';
        echo 'buttonBorderWidth: ' . $button_border_width . ',';
        echo 'buttonBorderColor: "' . esc_js($button_border_color) . '",';
        echo 'buttonBorderRadius: ' . $button_border_radius . ',';
        echo 'buttonPadding: ' . $button_padding . ',';
        echo 'buttonFontSize: ' . $button_font_size . ',';
        echo 'buttonFontWeight: "' . esc_js($button_font_weight) . '",';
        echo 'buttonFixedRight: ' . ($button_fixed_right ? 'true' : 'false') . ',';
        echo 'buttonFixedLeft: ' . ($button_fixed_left ? 'true' : 'false') . ',';
        echo 'buttonGap: ' . intval($button_gap) . '';
        echo '};';
        ?>
        
        (function() {
            'use strict';
            
            // HTML sanitization function to prevent XSS
            function sanitizeHtml(html) {
                const allowedTags = ['strong', 'em', 'b', 'i', 'span', 'br', 'a'];
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const walker = document.createTreeWalker(doc.body, NodeFilter.SHOW_ELEMENT);
                const toProcess = [];
                while (walker.nextNode()) {
                    toProcess.push(walker.currentNode);
                }
                toProcess.forEach(node => {
                    const tag = node.tagName.toLowerCase();
                    if (allowedTags.includes(tag)) {
                        // Clean attributes
                        for (let attr of Array.from(node.attributes)) {
                            const attrName = attr.name.toLowerCase();
                            if (attrName.startsWith('on') || attr.value.match(/^(javascript|data|vbscript):/i)) {
                                node.removeAttribute(attr.name);
                            }
                        }
                        // For 'a' tags, ensure href is present and safe
                        if (tag === 'a' && !node.hasAttribute('href')) {
                            node.replaceWith(...node.childNodes);
                        }
                    } else {
                        // Unwrap disallowed tags by replacing with their children
                        node.replaceWith(...node.childNodes);
                    }
                });
                // Preserve &nbsp; and other entities
                return doc.body.innerHTML.replace(/&amp;nbsp;/g, '&nbsp;');
            }
            
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
                
                // Create message container
                var messageContainer = document.createElement("div");
                messageContainer.className = "ca-banner-message";
                
                // Create message safely with HTML support - repeat message for scrolling effect
                var message = caBannerConfig.message || '';
                var repeat = Math.max(1, Math.min(100, caBannerConfig.repeat || 10));
                
                // Clean up message content - remove unwanted div elements
                var cleanMessage = message.replace(/&lt;div[^&gt;]*style="[^"]*left:\s*50%[^"]*"[^&gt;]*&gt;&lt;\/div&gt;/gi, '');
                cleanMessage = cleanMessage.replace(/&lt;div[^&gt;]*style="[^"]*top:\s*50px[^"]*"[^&gt;]*&gt;&lt;\/div&gt;/gi, '');
                cleanMessage = cleanMessage.replace(/&lt;div[^&gt;]*style="[^"]*left:\s*50%[^"]*top:\s*50px[^"]*"[^&gt;]*&gt;&lt;\/div&gt;/gi, '');
                cleanMessage = cleanMessage.replace(/&lt;div[^&gt;]*style="[^"]*top:\s*50px[^"]*left:\s*50%[^"]*"[^&gt;]*&gt;&lt;\/div&gt;/gi, '');
                cleanMessage = cleanMessage.replace(/\s+/g, ' ').trim(); // Clean up extra spaces
                
                // Repeat the message and button
                var buttonAppended = false;
                var isFixed = caBannerConfig.buttonFixedRight || caBannerConfig.buttonFixedLeft;
                if (isFixed && caBannerConfig.buttonEnabled && caBannerConfig.buttonText && caBannerConfig.buttonLink) {
                    // Create single button for fixed position
                    var button = document.createElement("a");
                    button.href = caBannerConfig.buttonLink;
                    button.className = "ca-banner-button";
                    button.textContent = caBannerConfig.buttonText;
                    var buttonStyles = [
                        'display: inline-block !important',
                        'background-color: ' + (caBannerConfig.buttonColor || '#ce7a31') + ' !important',
                        'color: ' + (caBannerConfig.buttonTextColor || '#ffffff') + ' !important',
                        'border: ' + (caBannerConfig.buttonBorderWidth || 0) + 'px solid ' + (caBannerConfig.buttonBorderColor || '#ce7a31') + ' !important',
                        'border-radius: ' + (caBannerConfig.buttonBorderRadius || 4) + 'px !important',
                        'padding: ' + (caBannerConfig.buttonPadding || 8) + 'px !important',
                        'font-size: ' + (caBannerConfig.buttonFontSize || 14) + 'px !important',
                        'font-weight: ' + (caBannerConfig.buttonFontWeight || '600') + ' !important',
                        'text-decoration: none !important',
                        'white-space: nowrap !important',
                        'vertical-align: middle !important'
                    ];
                    if (caBannerConfig.buttonFixedRight) {
                        buttonStyles.push('margin-right: ' + (caBannerConfig.buttonGap || 15) + 'px !important');
                        buttonStyles.push('margin-left: 10px !important');
                    } else if (caBannerConfig.buttonFixedLeft) {
                        buttonStyles.push('margin-left: ' + (caBannerConfig.buttonGap || 15) + 'px !important');
                        buttonStyles.push('margin-right: 10px !important');
                    }
                    button.style.cssText = buttonStyles.join('; ');
                    buttonAppended = true;
                } 
                for (var i = 0; i < repeat; i++) {
                    var msgSpan = document.createElement("span");
                    msgSpan.innerHTML = caBanner.sanitizeHtml(cleanMessage);
                    messageContainer.appendChild(msgSpan);

                    if (!isFixed && caBannerConfig.buttonEnabled && caBannerConfig.buttonText && caBannerConfig.buttonLink) {
                        var button = document.createElement("a");
                        button.href = caBannerConfig.buttonLink;
                        button.className = "ca-banner-button";
                        button.textContent = caBannerConfig.buttonText;
                        button.style.cssText = [
                            'display: inline-block !important',
                            'background-color: ' + (caBannerConfig.buttonColor || '#ce7a31') + ' !important',
                            'color: ' + (caBannerConfig.buttonTextColor || '#ffffff') + ' !important',
                            'border: ' + (caBannerConfig.buttonBorderWidth || 0) + 'px solid ' + (caBannerConfig.buttonBorderColor || '#ce7a31') + ' !important',
                            'border-radius: ' + (caBannerConfig.buttonBorderRadius || 4) + 'px !important',
                            'padding: ' + (caBannerConfig.buttonPadding || 8) + 'px !important',
                            'font-size: ' + (caBannerConfig.buttonFontSize || 14) + 'px !important',
                            'font-weight: ' + (caBannerConfig.buttonFontWeight || '600') + ' !important',
                            'text-decoration: none !important',
                            'margin-left: 10px !important',
                            'margin-right: 30px !important',
                            'white-space: nowrap !important',
                            'vertical-align: middle !important'
                        ].join('; ');
                        messageContainer.appendChild(button);
                    }
                }
                
                // Apply CSS animation for scrolling effect - Match admin preview exactly
                var speed = caBannerConfig.speed || 60;
                messageContainer.style.animationDuration = speed + 's';
                messageContainer.style.display = 'inline-block';
                messageContainer.style.whiteSpace = 'nowrap';
                messageContainer.style.paddingRight = '20px';
                messageContainer.style.willChange = 'transform';
                messageContainer.style.minWidth = '200px';
                
                bannerContent.appendChild(messageContainer);
                if (buttonAppended) {
                    if (caBannerConfig.buttonFixedLeft) {
                        bannerContent.insertBefore(button, messageContainer);
                    } else {
                        bannerContent.appendChild(button);
                    }
                    bannerContent.style.display = 'flex';
                    bannerContent.style.alignItems = 'center';
                    messageContainer.style.flex = '1 1 auto';
                    messageContainer.style.overflow = 'hidden';
                }
                
                // Apply inline styles - Match Live Preview exactly
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
                    'font-weight: ' + caBannerConfig.fontWeight + ' !important',
                    'font-size: ' + caBannerConfig.fontSize + 'px !important',
                    'font-family: "' + caBannerConfig.fontFamily + '", sans-serif !important',
                    'border-radius: 4px !important',
                        'white-space: nowrap !important',
                        'display: block !important',
                        'overflow: hidden !important',
                    'min-height: 40px !important',
                    'border-top: ' + caBannerConfig.borderWidth + 'px ' + caBannerConfig.borderStyle + ' ' + caBannerConfig.borderColor + ' !important',
                    'border-bottom: ' + caBannerConfig.borderWidth + 'px ' + caBannerConfig.borderStyle + ' ' + caBannerConfig.borderColor + ' !important',
                    'margin: 0 !important',
                    'box-shadow: none !important'
                ].join('; ');
                
                // Apply styles to banner content - Match admin preview
                bannerContent.style.display = 'flex';
                bannerContent.style.alignItems = 'center';
                bannerContent.style.width = '100%';
                bannerContent.style.overflow = 'hidden';
                bannerContent.style.whiteSpace = 'nowrap';
                bannerContent.style.margin = '0';
                bannerContent.style.padding = '0';
                
                // Force the style with a slight delay to override any theme interference
                setTimeout(function() {
                    bannerContent.style.setProperty('display', 'flex', 'important');
                }, 50);
                
                // Add CSS animation (create once, update duration via style attribute) - Match preview exactly
                if (!document.querySelector('#ca-banner-animation-style')) {
                    var style = document.createElement('style');
                    style.id = 'ca-banner-animation-style';
                    style.textContent = '@keyframes ca-banner-preview-scroll { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }';
                    document.head.appendChild(style);
                }
                
                // Apply animation duration directly to the message container - Match preview exactly
                messageContainer.style.animation = 'ca-banner-preview-scroll ' + caBannerConfig.speed + 's linear infinite';
                
                banner.appendChild(bannerContent);
                
                // Insert banner at the beginning of body
                if (document.body) {
                    document.body.insertBefore(banner, document.body.firstChild);
                } else {
                    // Try again immediately
                    setTimeout(createBanner, 1);
                }
            }
            
            // Immediate initialization - no delays
            if (document.body) {
                createBanner();
            } else {
                // Only wait for DOM if body isn't ready yet
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', createBanner);
                } else {
                    // Document is ready but body might not be, try immediately
                    createBanner();
                }
            }
        })();
        
        <?php
        echo '</script>';
    }
    
    /**
     * Render banner JavaScript
     * 
     * Generates the complete JavaScript code for banner display including
     * configuration, animation, and security features.
     * 
     * @since 1.2.7
     * @param string $message Banner message
     * @param int $repeat Number of message repetitions
     * @param int $speed Scroll speed
     * @param string $background_color Background color
     * @param string $text_color Text color
     * @param int $font_size Font size
     * @param string $font_family Font family
     * @param string $font_weight Font weight
     * @param int $border_width Border width
     * @param string $border_style Border style
     * @param string $border_color Border color
     * @param bool $disable_mobile Whether to disable on mobile
     * @param string $start_date Banner start date
     * @param string $end_date Banner end date
     * @param string $image Image URL
     * @param string $image_start_date Image start date
     * @param string $image_end_date Image end date
     * @param bool $button_enabled Whether button is enabled
     * @param string $button_text Button text
     * @param string $button_link Button link
     * @param string $button_color Button color
     * @param string $button_text_color Button text color
     * @param int $button_border_width Button border width
     * @param string $button_border_color Button border color
     * @param int $button_border_radius Button border radius
     * @param int $button_padding Button padding
     * @param int $button_font_size Button font size
     * @param string $button_font_weight Button font weight
     * @param bool $sticky Whether the banner is sticky
     * @param bool $button_lock_enabled Whether button locking is enabled
     * @param string $button_lock_position Lock position: 'left' or 'right'
     * @param int $button_gap Gap from edge for fixed buttons
     * @param int $vertical_padding Vertical padding for banner height
     * @param bool $button_new_window Open link in new window
     * @param string $link_color Link color for banner
     */
    private function render_banner_script($message, $repeat, $speed, $background_color, $text_color, $font_size, $font_family, $font_weight, $border_width, $border_style, $border_color, $disable_mobile, $start_date, $end_date, $image, $image_start_date, $image_end_date, $button_enabled, $button_text, $button_link, $button_color, $button_text_color, $button_border_width, $button_border_color, $button_border_radius, $button_padding, $button_font_size, $button_font_weight, $sticky, $button_lock_enabled, $button_lock_position, $button_gap, $vertical_padding, $button_new_window, $link_color) {
        echo '<script>';
        echo 'var caBannerConfig = {';
        echo 'message: ' . wp_json_encode(str_replace(["\r\n", "\r", "\n"], ' ', $message) . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ') . ',';
        echo 'repeat: ' . intval($repeat) . ',';
        echo 'speed: ' . intval($speed ?: 60) . ',';
        echo 'backgroundColor: "' . esc_js($background_color) . '",';
        echo 'textColor: "' . esc_js($text_color) . '",';
        echo 'fontSize: ' . intval($font_size ?: 16) . ',';
        echo 'fontFamily: "' . esc_js($font_family) . '",';
        echo 'fontWeight: "' . esc_js($font_weight ?: '600') . '",';
        echo 'borderWidth: ' . intval($border_width) . ',';
        echo 'borderStyle: "' . esc_js($border_style) . '",';
        echo 'borderColor: "' . esc_js($border_color) . '",';
        echo 'disableMobile: ' . ($disable_mobile ? 'true' : 'false') . ',';
        echo 'startDate: "' . esc_js($start_date) . '",';
        echo 'endDate: "' . esc_js($end_date) . '",';
        echo 'image: "' . esc_js($image) . '",';
        echo 'imageStartDate: "' . esc_js($image_start_date) . '",';
        echo 'imageEndDate: "' . esc_js($image_end_date) . '",';
        echo 'buttonEnabled: ' . ($button_enabled ? 'true' : 'false') . ',';
        echo 'buttonText: "' . esc_js($button_text) . '",';
        echo 'buttonLink: "' . esc_js($button_link) . '",';
        echo 'buttonColor: "' . esc_js($button_color) . '",';
        echo 'buttonTextColor: "' . esc_js($button_text_color) . '",';
        echo 'buttonBorderWidth: ' . intval($button_border_width) . ',';
        echo 'buttonBorderColor: "' . esc_js($button_border_color) . '",';
        echo 'buttonBorderRadius: ' . intval($button_border_radius) . ',';
        echo 'buttonPadding: ' . intval($button_padding) . ',';
        echo 'buttonFontSize: ' . intval($button_font_size) . ',';
        echo 'buttonFontWeight: "' . esc_js($button_font_weight) . '",';
        echo 'buttonLockEnabled: ' . ($button_lock_enabled ? 'true' : 'false') . ',';
        echo 'buttonLockPosition: "' . esc_js($button_lock_position) . '",';
        echo 'buttonGap: ' . intval($button_gap) . ',';
        echo 'verticalPadding: ' . intval($vertical_padding) . ',';
        echo 'buttonNewWindow: ' . ($button_new_window ? 'true' : 'false') . ',';
        echo 'linkColor: "' . esc_js($link_color) . '",';
        echo 'sticky: ' . ($sticky ? 'true' : 'false') . '';
        echo '};';
        ?>
        
        (function() {
            'use strict';
            
            var caBanner = {
                initialized: false,
                
                // HTML sanitization function to prevent XSS
                sanitizeHtml: function(html) {
                    const allowedTags = ['strong', 'em', 'b', 'i', 'span', 'br', 'a'];
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const walker = document.createTreeWalker(doc.body, NodeFilter.SHOW_ELEMENT);
                    const toProcess = [];
                    while (walker.nextNode()) {
                        toProcess.push(walker.currentNode);
                    }
                    toProcess.forEach(node => {
                        const tag = node.tagName.toLowerCase();
                        if (allowedTags.includes(tag)) {
                            // Clean attributes
                            for (let attr of Array.from(node.attributes)) {
                                const attrName = attr.name.toLowerCase();
                                if (attrName.startsWith('on') || attr.value.match(/^(javascript|data|vbscript):/i)) {
                                    node.removeAttribute(attr.name);
                                }
                            }
                            // For 'a' tags, ensure href is present and safe
                            if (tag === 'a' && !node.hasAttribute('href')) {
                                node.replaceWith(...node.childNodes);
                            }
                        } else {
                            // Unwrap disallowed tags by replacing with their children
                            node.replaceWith(...node.childNodes);
                        }
                    });
                    // Preserve &nbsp; and other entities
                    return doc.body.innerHTML.replace(/&amp;nbsp;/g, '&nbsp;');
                },
                
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

                    // Check if body is ready
                    if (!document.body) {
                        // Try again immediately
                        setTimeout(this.createBanner.bind(this), 1);
                        return;
                    }

                    var banner = document.createElement("div");
                    banner.className = "ca-banner-container";
                    banner.setAttribute('data-ca-banner', 'true');
                    
                    var bannerContent = document.createElement("div");
                    bannerContent.className = "ca-banner-content";
                    
                    // Create message container
                    var messageContainer = document.createElement("div");
                    messageContainer.className = "ca-banner-message";
                    
                    // Create message safely with HTML support - repeat message for scrolling effect
                    var message = config.message || '';
                    var repeat = Math.max(1, Math.min(100, config.repeat || 10));
                    
                    // Clean up message content - remove unwanted div elements
                    var cleanMessage = message.replace(/&lt;div[^&gt;]*style="[^"]*left:\s*50%[^"]*"[^&gt;]*&gt;&lt;\/div&gt;/gi, '');
                    cleanMessage = cleanMessage.replace(/&lt;div[^&gt;]*style="[^"]*top:\s*50px[^"]*"[^&gt;]*&gt;&lt;\/div&gt;/gi, '');
                    cleanMessage = cleanMessage.replace(/&lt;div[^&gt;]*style="[^"]*left:\s*50%[^"]*top:\s*50px[^"]*"[^&gt;]*&gt;&lt;\/div&gt;/gi, '');
                    cleanMessage = cleanMessage.replace(/&lt;div[^&gt;]*style="[^"]*top:\s*50px[^"]*left:\s*50%[^"]*"[^&gt;]*&gt;&lt;\/div&gt;/gi, '');
                    cleanMessage = cleanMessage.replace(/\s+/g, ' ').trim(); // Clean up extra spaces
                    
                    // Repeat the message and button
                    var buttonAppended = false;
                    var isFixed = config.buttonLockEnabled;
                    if (isFixed && config.buttonEnabled && config.buttonText && config.buttonLink) {
                        // Create single button for fixed position
                        var button = document.createElement("a");
                        button.href = config.buttonLink;
                        if (config.buttonNewWindow) {
                            button.target = '_blank';
                        }
                        button.className = "ca-banner-button";
                        button.textContent = config.buttonText;
                        var buttonStyles = [
                            'display: inline-block !important',
                            'background-color: ' + (config.buttonColor || '#ce7a31') + ' !important',
                            'color: ' + (config.buttonTextColor || '#ffffff') + ' !important',
                            'border: ' + (config.buttonBorderWidth || 0) + 'px solid ' + (config.buttonBorderColor || '#ce7a31') + ' !important',
                            'border-radius: ' + (config.buttonBorderRadius || 4) + 'px !important',
                            'padding: ' + (config.buttonPadding || 8) + 'px !important',
                            'font-size: ' + (config.buttonFontSize || 14) + 'px !important',
                            'font-weight: ' + (config.buttonFontWeight || '600') + ' !important',
                            'text-decoration: none !important',
                            'white-space: nowrap !important',
                            'vertical-align: middle !important',
                            'position: relative !important',
                            'z-index: 2 !important'
                        ];
                        if (config.buttonLockPosition === 'right') {
                            buttonStyles.push('margin-right: ' + (config.buttonGap || 15) + 'px !important');
                            buttonStyles.push('margin-left: 10px !important');
                        } else if (config.buttonLockPosition === 'left') {
                            buttonStyles.push('margin-left: ' + (config.buttonGap || 15) + 'px !important');
                            buttonStyles.push('margin-right: 10px !important');
                        }
                        button.style.cssText = buttonStyles.join('; ');
                        buttonAppended = true;
                    } 
                    for (var i = 0; i < repeat; i++) {
                        var msgSpan = document.createElement("span");
                        msgSpan.innerHTML = caBanner.sanitizeHtml(cleanMessage);
                        messageContainer.appendChild(msgSpan);

                        if (!isFixed && config.buttonEnabled && config.buttonText && config.buttonLink) {
                            var button = document.createElement("a");
                            button.href = config.buttonLink;
                            if (config.buttonNewWindow) {
                                button.target = '_blank';
                            }
                            button.className = "ca-banner-button";
                            button.textContent = config.buttonText;
                            button.style.cssText = [
                                'display: inline-block !important',
                                'background-color: ' + (config.buttonColor || '#ce7a31') + ' !important',
                                'color: ' + (config.buttonTextColor || '#ffffff') + ' !important',
                                'border: ' + (config.buttonBorderWidth || 0) + 'px solid ' + (config.buttonBorderColor || '#ce7a31') + ' !important',
                                'border-radius: ' + (config.buttonBorderRadius || 4) + 'px !important',
                                'padding: ' + (config.buttonPadding || 8) + 'px !important',
                                'font-size: ' + (config.buttonFontSize || 14) + 'px !important',
                                'font-weight: ' + (config.buttonFontWeight || '600') + ' !important',
                                'text-decoration: none !important',
                                'margin-left: 10px !important',
                                'margin-right: 20px !important',
                                'white-space: nowrap !important',
                                'vertical-align: middle !important'
                            ].join('; ');
                            messageContainer.appendChild(button);
                        }
                    }
                    
                    // Apply CSS animation for scrolling effect - Match admin preview exactly
                    var speed = config.speed || 60;
                    messageContainer.style.animationDuration = speed + 's';
                    messageContainer.style.display = 'inline-block';
                    messageContainer.style.whiteSpace = 'nowrap';
                    messageContainer.style.paddingRight = '20px';
                    messageContainer.style.willChange = 'transform';
                    messageContainer.style.minWidth = '200px';
                    
                    bannerContent.appendChild(messageContainer);
                    if (buttonAppended) {
                        if (config.buttonLockPosition === 'left') {
                            bannerContent.insertBefore(button, messageContainer);
                        } else {
                            bannerContent.appendChild(button);
                        }
                        bannerContent.style.display = 'flex';
                        bannerContent.style.alignItems = 'center';
                        messageContainer.style.flex = '1 1 auto';
                        messageContainer.style.overflow = 'hidden';
                    }
                    
                    // Apply inline styles for maximum compatibility - Match Live Preview exactly
                    banner.style.cssText = [
                        'position: relative !important',
                        'top: 0 !important',
                        'left: 0 !important',
                        'width: 100% !important',
                        'background-color: ' + (config.backgroundColor || '#729946') + ' !important',
                        'color: ' + (config.textColor || '#000000') + ' !important',
                        'padding: ' + (config.verticalPadding || 10) + 'px 10px !important',
                        'text-align: center !important',
                        'z-index: 999999 !important',
                        'overflow: hidden !important',
                        'font-weight: ' + (config.fontWeight || '600') + ' !important',
                        'font-size: ' + (config.fontSize || 16) + 'px !important',
                        'font-family: "' + (config.fontFamily || 'Arial') + '", sans-serif !important',
                        'border-radius: 4px !important',
                        'white-space: nowrap !important',
                        'display: flex !important',
                        'align-items: center !important',
                        'min-height: 40px !important',
                        'border-top: ' + (config.borderWidth || 0) + 'px ' + (config.borderStyle || 'solid') + ' ' + (config.borderColor || '#000000') + ' !important',
                        'border-bottom: ' + (config.borderWidth || 0) + 'px ' + (config.borderStyle || 'solid') + ' ' + (config.borderColor || '#000000') + ' !important',
                        'margin: 0 !important',
                        'box-shadow: none !important'
                    ].join('; ');

                    // Apply styles to banner content - Match admin preview
                    bannerContent.style.display = 'flex';
                    bannerContent.style.alignItems = 'center';
                    bannerContent.style.width = '100%';
                    bannerContent.style.overflow = 'hidden';
                    bannerContent.style.whiteSpace = 'nowrap';
                    bannerContent.style.margin = '0';
                    bannerContent.style.padding = '0';
                
                // Force the style with a slight delay to override any theme interference
                setTimeout(function() {
                    bannerContent.style.setProperty('display', 'flex', 'important');
                }, 50);
                    
                    // Add CSS animation (create once, update duration via style attribute) - Match preview exactly
                    if (!document.querySelector('#ca-banner-animation-style')) {
                        var style = document.createElement('style');
                        style.id = 'ca-banner-animation-style';
                        style.textContent = '@keyframes ca-banner-preview-scroll { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }';
                        document.head.appendChild(style);
                    }
                    
                // Apply animation duration directly to the message container - Match preview exactly
                messageContainer.style.animation = 'ca-banner-preview-scroll ' + (config.speed || 60) + 's linear infinite';
                    
                    // Add animation class
                    bannerContent.classList.add('ca-banner-content');

                    banner.appendChild(bannerContent);

                    // Apply inline styles without position
                    banner.style.cssText = [
                        'left: 0 !important',
                        'width: 100% !important',
                        'background-color: ' + (config.backgroundColor || '#729946') + ' !important',
                        'color: ' + (config.textColor || '#000000') + ' !important',
                        'padding: ' + (config.verticalPadding || 10) + 'px 10px !important',
                        'text-align: center !important',
                        'z-index: 999999 !important',
                        'overflow: hidden !important',
                        'font-weight: ' + (config.fontWeight || '600') + ' !important',
                        'font-size: ' + (config.fontSize || 16) + 'px !important',
                        'font-family: "' + (config.fontFamily || 'Arial') + '", sans-serif !important',
                        'border-radius: 4px !important',
                        'white-space: nowrap !important',
                        'align-items: center !important',
                        'min-height: 40px !important',
                        'border-top: ' + (config.borderWidth || 0) + 'px ' + (config.borderStyle || 'solid') + ' ' + (config.borderColor || '#000000') + ' !important',
                        'border-bottom: ' + (config.borderWidth || 0) + 'px ' + (config.borderStyle || 'solid') + ' ' + (config.borderColor || '#000000') + ' !important',
                        'margin: 0 !important',
                        'box-shadow: none !important',
                        'display: flex !important'
                    ].join('; ');

                    // Add sticky class if enabled
                    if (config.sticky) {
                        banner.classList.add('ca-banner-sticky');
                        
                        // Adjust top for admin bar dynamically
                        var adminBar = document.getElementById('wpadminbar');
                        if (adminBar) {
                            banner.style.top = adminBar.offsetHeight + 'px !important';
                        } else {
                            banner.style.top = '0 !important';
                        }
                    } else {
                        banner.style.position = 'relative !important';
                        banner.style.top = '0 !important';
                    }

                    // Insert banner
                    document.body.insertBefore(banner, document.body.firstChild);

                    // Add link color style
                    banner.style.setProperty('--link-color', config.linkColor, 'important');
                    var linkStyle = document.createElement('style');
                    linkStyle.textContent = '.ca-banner-container a { color: var(--link-color) !important; }';
                    banner.appendChild(linkStyle);

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

            // Immediate initialization with debugging
            function initBanner() {
                // Prevent duplicate initialization
                if (document.querySelector('.ca-banner-container')) {
                    return;
                }
                caBanner.init();
            }

            // Try immediate initialization
            initBanner();
            
            // If immediate initialization failed (document not ready), try again on DOMContentLoaded
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    // Only create banner if one doesn't exist
                    if (!document.querySelector('.ca-banner-container')) {
                        initBanner();
                    }
                });
            } else {
            }
            
        })();
        
        <?php
        echo '</script>';
    }
    
    /**
     * Detect potential theme conflicts and apply fixes
     * 
     * Identifies common problematic themes and applies additional CSS fixes
     * to ensure banner display compatibility.
     * 
     * @since 1.2.7
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
