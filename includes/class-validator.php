<?php
/**
 * Validator class for input validation and sanitization
 *
 * @package CA_Banners
 * @since 1.2.7
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CA Banners Validator class
 */
class CA_Banners_Validator {
    
    /**
     * Log errors using centralized error handler
     * 
     * Centralized error logging method that uses the main plugin's error handler
     * if available, or falls back to basic WordPress error logging.
     * 
     * @since 1.2.7
     * @param string $message The error message to log
     * @param string $type Error type (use CA_Banners_Error_Handler constants)
     * @param string $severity Error severity (use CA_Banners_Error_Handler constants)
     * @param Exception|null $exception Optional exception object
     */
    private function log_error($message, $type = CA_Banners_Error_Handler::TYPE_VALIDATION, $severity = CA_Banners_Error_Handler::SEVERITY_MEDIUM, $exception = null) {
        $ca_banners = CA_Banners::get_instance();
        if ($ca_banners && $ca_banners->error_handler) {
            $ca_banners->error_handler->log_error($message, $type, $severity, $exception);
        } else {
            // Fallback to basic logging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('CA Banners Validator: ' . $message . ($exception ? ' - ' . $exception->getMessage() : ''));
            }
        }
    }
    
    /**
     * Sanitize plugin settings
     * 
     * Main sanitization method that processes raw input data through comprehensive
     * validation with proper error handling and fallback to safe defaults.
     * 
     * @since 1.2.7
     * @param array $input Raw input data from form
     * @return array Sanitized and validated settings array
     */
    public function sanitize_settings($input) {
        try {
            // Use comprehensive validation for enhanced security
            return $this->comprehensive_validate($input);
        } catch (Exception $e) {
            $this->log_error('Sanitization error', CA_Banners_Error_Handler::TYPE_VALIDATION, CA_Banners_Error_Handler::SEVERITY_MEDIUM, $e);
            // Return safe defaults on error
            return $this->get_safe_defaults();
        }
    }
    
    /**
     * Validate banner settings and return sanitized values
     * 
     * Comprehensive validation method that processes settings array through
     * all validation rules with proper error handling.
     * 
     * @since 1.2.7
     * @param array $settings Raw settings array to validate
     * @return array Validated and sanitized settings array
     */
    public function validate_settings($settings) {
        try {
            // Use comprehensive validation for enhanced security
            return $this->comprehensive_validate($settings);
        } catch (Exception $e) {
            $this->log_error('Validation error', CA_Banners_Error_Handler::TYPE_VALIDATION, CA_Banners_Error_Handler::SEVERITY_MEDIUM, $e);
            // Return safe defaults on error
            return $this->get_safe_defaults();
        }
    }
    
    /**
     * Validate URL list for security
     * 
     * Validates and sanitizes URL lists from textarea input with security
     * checks including length limits and malicious pattern detection.
     * 
     * @since 1.2.7
     * @param string $url_list Raw URL list from textarea
     * @return string Validated and sanitized URL list
     */
    private function validate_url_list($url_list) {
        if (empty($url_list)) {
            return '';
        }
        
        // Limit total URL list length to prevent DoS attacks
        if (strlen($url_list) > CA_Banners_Constants::MAX_URL_LIST_LENGTH) {
            $url_list = substr($url_list, 0, CA_Banners_Constants::MAX_URL_LIST_LENGTH);
            
            // Log potential DoS attempt
            $this->log_error('URL list truncated due to length limit (' . CA_Banners_Constants::MAX_URL_LIST_LENGTH . ' chars)', CA_Banners_Error_Handler::TYPE_SECURITY, CA_Banners_Error_Handler::SEVERITY_MEDIUM);
        }
        
        $lines = explode("\n", $url_list);
        $valid_urls = array();
        
        foreach ($lines as $url) {
            $url = trim($url);
            if (empty($url)) {
                continue;
            }
            
            // Limit individual URL length
            if (strlen($url) > CA_Banners_Constants::MAX_INDIVIDUAL_URL_LENGTH) {
                $url = substr($url, 0, CA_Banners_Constants::MAX_INDIVIDUAL_URL_LENGTH);
            }
            
            // Validate URL pattern - only allow relative URLs with safe characters
            if (preg_match('/^\/[a-zA-Z0-9\/\-_\.\*\?=&%#]*\/?$/', $url)) {
                // Additional security checks
                $url = $this->sanitize_url_path($url);
                if (!empty($url)) {
                    $valid_urls[] = $url;
                }
            }
        }
        
        return implode("\n", $valid_urls);
    }
    
    /**
     * Sanitize URL path to prevent path traversal and other attacks
     *
     * @param string $url Raw URL path
     * @return string Sanitized URL path
     */
    private function sanitize_url_path($url) {
        // Remove any path traversal attempts
        $url = str_replace(array('../', '..\\', '..%2f', '..%5c'), '', $url);
        
        // Remove null bytes
        $url = str_replace("\0", '', $url);
        
        // Remove any control characters
        $url = preg_replace('/[\x00-\x1F\x7F]/', '', $url);
        
        // Ensure URL starts with /
        if (!empty($url) && $url[0] !== '/') {
            $url = '/' . $url;
        }
        
        // Limit URL length to prevent DoS
        if (strlen($url) > CA_Banners_Constants::MAX_INDIVIDUAL_URL_LENGTH) {
            $url = substr($url, 0, CA_Banners_Constants::MAX_INDIVIDUAL_URL_LENGTH);
        }
        
        return $url;
    }
    
    /**
     * Validate button link URL for security
     *
     * @param string $url Raw button link URL
     * @return string Validated URL
     */
    private function validate_button_link($url) {
        if (empty($url)) {
            return '';
        }
        
        // Basic URL sanitization
        $url = esc_url_raw($url);
        
        // Additional security checks
        if (empty($url)) {
            return '';
        }
        
        // Check for dangerous protocols
        $dangerous_protocols = CA_Banners_Constants::DANGEROUS_PROTOCOLS;
        foreach ($dangerous_protocols as $protocol) {
            if (stripos($url, $protocol) === 0) {
                return '';
            }
        }
        
        // Ensure URL starts with http:// or https://
        if (!preg_match('/^https?:\/\//', $url)) {
            return '';
        }
        
        // Limit URL length
        if (strlen($url) > CA_Banners_Constants::MAX_BUTTON_LINK_LENGTH) {
            $url = substr($url, 0, CA_Banners_Constants::MAX_BUTTON_LINK_LENGTH);
        }
        
        return $url;
    }
    
    /**
     * Validate and sanitize HTML content with enhanced security
     *
     * @param string $content Raw HTML content
     * @return string Sanitized HTML content
     */
    private function validate_html_content($content) {
        if (empty($content)) {
            return '';
        }
        
        // Remove any script tags and event handlers
        $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content);
        $content = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $content);
        
        // Remove dangerous attributes
        $dangerous_attrs = CA_Banners_Constants::DANGEROUS_ATTRIBUTES;
        foreach ($dangerous_attrs as $attr) {
            $content = preg_replace('/\s*' . $attr . '\s*=\s*["\'][^"\']*["\']/i', '', $content);
        }
        
        // Use WordPress kses for safe HTML
        $allowed_tags = array(
            'strong' => array(),
            'em' => array(),
            'b' => array(),
            'i' => array(),
            'u' => array(),
            'span' => array('style' => array()),
            'div' => array('style' => array()),
            'p' => array(),
            'br' => array(),
            'a' => array('href' => array(), 'target' => array()),
            'img' => array('src' => array(), 'alt' => array(), 'width' => array(), 'height' => array())
        );
        
        $content = wp_kses($content, $allowed_tags);
        
        // Limit content length to prevent DoS attacks
        // Banner messages should be concise for better UX
        if (strlen($content) > CA_Banners_Constants::MAX_MESSAGE_LENGTH) {
            $content = substr($content, 0, CA_Banners_Constants::MAX_MESSAGE_LENGTH);
            
            // Log potential DoS attempt
            $this->log_error('Banner message truncated due to length limit (' . CA_Banners_Constants::MAX_MESSAGE_LENGTH . ' chars)', CA_Banners_Error_Handler::TYPE_SECURITY, CA_Banners_Error_Handler::SEVERITY_MEDIUM);
        }
        
        return $content;
    }
    
    /**
     * Validate and sanitize numeric input with enhanced validation
     *
     * @param mixed $value Raw numeric value
     * @param int $min Minimum allowed value
     * @param int $max Maximum allowed value
     * @param int $default Default value if invalid
     * @return int Validated numeric value
     */
    private function validate_numeric($value, $min, $max, $default) {
        // Convert to integer
        $value = intval($value);
        
        // Check for valid numeric range
        if ($value < $min || $value > $max) {
            return $default;
        }
        
        // Check for NaN or infinite values
        if (!is_finite($value)) {
            return $default;
        }
        
        return $value;
    }
    
    /**
     * Validate and sanitize color values with enhanced validation
     *
     * @param string $color Raw color value
     * @param string $default Default color if invalid
     * @return string Validated color value
     */
    private function validate_color($color, $default) {
        if (empty($color)) {
            return $default;
        }
        
        // Remove any whitespace
        $color = trim($color);
        
        // Check for valid hex color format
        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
            return $default;
        }
        
        // Additional security check for dangerous color values
        $dangerous_colors = CA_Banners_Constants::PROBLEMATIC_COLORS;
        if (in_array(strtolower($color), $dangerous_colors)) {
            // Allow these colors but log for monitoring
            $this->log_error('Potentially problematic color used: ' . $color, CA_Banners_Error_Handler::TYPE_VALIDATION, CA_Banners_Error_Handler::SEVERITY_LOW);
        }
        
        return $color;
    }
    
    /**
     * Validate and sanitize date values with enhanced validation
     *
     * @param string $date Raw date value
     * @return string Validated date value
     */
    private function validate_date($date) {
        if (empty($date)) {
            return '';
        }
        
        // Remove any whitespace
        $date = trim($date);
        
        // Check for valid date format (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return '';
        }
        
        // Validate the actual date
        $date_parts = explode('-', $date);
        if (count($date_parts) !== 3) {
            return '';
        }
        
        $year = intval($date_parts[0]);
        $month = intval($date_parts[1]);
        $day = intval($date_parts[2]);
        
        // Check for reasonable date ranges
        if ($year < CA_Banners_Constants::MIN_YEAR || $year > CA_Banners_Constants::MAX_YEAR) {
            return '';
        }
        
        if ($month < CA_Banners_Constants::MIN_MONTH || $month > CA_Banners_Constants::MAX_MONTH) {
            return '';
        }
        
        if ($day < CA_Banners_Constants::MIN_DAY || $day > CA_Banners_Constants::MAX_DAY) {
            return '';
        }
        
        // Check if date is valid
        if (!checkdate($month, $day, $year)) {
            return '';
        }
        
        return $date;
    }
    
    /**
     * Validate and sanitize text input with enhanced validation
     *
     * @param string $text Raw text input
     * @param int $max_length Maximum allowed length
     * @param string $default Default value if invalid
     * @return string Validated text input
     */
    private function validate_text($text, $max_length = 255, $default = '') {
        if (empty($text)) {
            return $default;
        }
        
        // Remove any whitespace
        $text = trim($text);
        
        // Check for valid UTF-8 encoding
        if (!mb_check_encoding($text, 'UTF-8')) {
            return $default;
        }
        
        // Remove any control characters
        $text = preg_replace('/[\x00-\x1F\x7F]/', '', $text);
        
        // Limit length
        if (strlen($text) > $max_length) {
            $text = substr($text, 0, $max_length);
        }
        
        // Basic XSS prevention
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        
        return $text;
    }
    
    /**
     * Validate and sanitize font family with enhanced validation
     *
     * @param string $font Raw font family
     * @param string $default Default font if invalid
     * @return string Validated font family
     */
    private function validate_font_family($font, $default = 'Arial') {
        if (empty($font)) {
            return $default;
        }
        
        // Remove any whitespace
        $font = trim($font);
        
        // Allowed font families
        $allowed_fonts = CA_Banners_Constants::ALLOWED_FONT_FAMILIES;
        
        // Check if font is in allowed list
        if (!in_array($font, $allowed_fonts)) {
            return $default;
        }
        
        return $font;
    }
    
    /**
     * Validate and sanitize CSS style values with enhanced validation
     *
     * @param string $style Raw CSS style value
     * @param string $default Default style if invalid
     * @return string Validated CSS style value
     */
    private function validate_css_style($style, $default = 'solid') {
        if (empty($style)) {
            return $default;
        }
        
        // Remove any whitespace
        $style = trim($style);
        
        // Allowed CSS styles
        $allowed_styles = CA_Banners_Constants::ALLOWED_BORDER_STYLES;
        
        // Check if style is in allowed list
        if (!in_array($style, $allowed_styles)) {
            return $default;
        }
        
        return $style;
    }
    
    /**
     * Validate and sanitize font weight with enhanced validation
     *
     * @param string $weight Raw font weight
     * @param string $default Default weight if invalid
     * @return string Validated font weight
     */
    private function validate_font_weight($weight, $default = '600') {
        if (empty($weight)) {
            return $default;
        }
        
        // Remove any whitespace
        $weight = trim($weight);
        
        // Allowed font weights
        $allowed_weights = CA_Banners_Constants::ALLOWED_FONT_WEIGHTS;
        
        // Check if weight is in allowed list
        if (!in_array($weight, $allowed_weights)) {
            return $default;
        }
        
        return $weight;
    }
    
    /**
     * Validate input length limits to prevent DoS attacks
     *
     * @param array $input Raw input data
     * @return array Input with length limits applied
     */
    private function apply_length_limits($input) {
        $length_limits = CA_Banners_Constants::get_length_limits();
        
        foreach ($length_limits as $field => $limit) {
            if (isset($input[$field]) && strlen($input[$field]) > $limit) {
                $original_length = strlen($input[$field]);
                $input[$field] = substr($input[$field], 0, $limit);
                
                // Log potential DoS attempt
                $this->log_error("Field '{$field}' truncated from {$original_length} to {$limit} characters", CA_Banners_Error_Handler::TYPE_SECURITY, CA_Banners_Error_Handler::SEVERITY_MEDIUM);
            }
        }
        
        return $input;
    }
    
    /**
     * Comprehensive input validation with error logging
     *
     * @param array $input Raw input data
     * @return array Validated input data
     */
    public function comprehensive_validate($input) {
        $validated = array();
        $errors = array();
        
        try {
            // Apply length limits first to prevent DoS attacks
            $input = $this->apply_length_limits($input);
            
            // Validate each field with enhanced validation
            $validated['enabled'] = isset($input['enabled']) ? (bool) $input['enabled'] : false;
            $validated['message'] = isset($input['message']) ? $this->validate_html_content($input['message']) : '';
            $validated['repeat'] = $this->validate_numeric($input['repeat'] ?? CA_Banners_Constants::DEFAULT_REPEAT, CA_Banners_Constants::MIN_REPEAT_VALUE, CA_Banners_Constants::MAX_REPEAT_VALUE, CA_Banners_Constants::DEFAULT_REPEAT);
            $validated['speed'] = $this->validate_numeric($input['speed'] ?? CA_Banners_Constants::DEFAULT_SPEED, CA_Banners_Constants::MIN_SPEED_VALUE, CA_Banners_Constants::MAX_SPEED_VALUE, CA_Banners_Constants::DEFAULT_SPEED);
            $validated['font_size'] = $this->validate_numeric($input['font_size'] ?? CA_Banners_Constants::DEFAULT_FONT_SIZE, CA_Banners_Constants::MIN_FONT_SIZE, CA_Banners_Constants::MAX_FONT_SIZE, CA_Banners_Constants::DEFAULT_FONT_SIZE);
            $validated['border_width'] = $this->validate_numeric($input['border_width'] ?? CA_Banners_Constants::DEFAULT_BORDER_WIDTH, CA_Banners_Constants::MIN_BORDER_WIDTH, CA_Banners_Constants::MAX_BORDER_WIDTH, CA_Banners_Constants::DEFAULT_BORDER_WIDTH);
            
            // Validate colors
            $validated['background_color'] = $this->validate_color($input['background_color'] ?? CA_Banners_Constants::DEFAULT_BACKGROUND_COLOR, CA_Banners_Constants::DEFAULT_BACKGROUND_COLOR);
            $validated['text_color'] = $this->validate_color($input['text_color'] ?? CA_Banners_Constants::DEFAULT_TEXT_COLOR, CA_Banners_Constants::DEFAULT_TEXT_COLOR);
            $validated['border_color'] = $this->validate_color($input['border_color'] ?? CA_Banners_Constants::DEFAULT_BORDER_COLOR, CA_Banners_Constants::DEFAULT_BORDER_COLOR);
            
            // Validate text fields
            $validated['font_family'] = $this->validate_font_family($input['font_family'] ?? CA_Banners_Constants::DEFAULT_FONT_FAMILY, CA_Banners_Constants::DEFAULT_FONT_FAMILY);
            $validated['border_style'] = $this->validate_css_style($input['border_style'] ?? CA_Banners_Constants::DEFAULT_BORDER_STYLE, CA_Banners_Constants::DEFAULT_BORDER_STYLE);
            $validated['font_weight'] = $this->validate_font_weight($input['font_weight'] ?? CA_Banners_Constants::DEFAULT_FONT_WEIGHT, CA_Banners_Constants::DEFAULT_FONT_WEIGHT);
            
            // Validate dates
            $validated['start_date'] = $this->validate_date($input['start_date'] ?? '');
            $validated['end_date'] = $this->validate_date($input['end_date'] ?? '');
            
            // Validate URLs
            $validated['urls'] = $this->validate_url_list($input['urls'] ?? '');
            $validated['exclude_urls'] = $this->validate_url_list($input['exclude_urls'] ?? '');
            $validated['image'] = isset($input['image']) ? esc_url_raw($input['image']) : '';
            
            // Validate button settings
            $validated['button_enabled'] = isset($input['button_enabled']) ? (bool) $input['button_enabled'] : false;
            $validated['button_text'] = $this->validate_text($input['button_text'] ?? '', CA_Banners_Constants::MAX_BUTTON_TEXT_LENGTH, '');
            $validated['button_link'] = $this->validate_button_link($input['button_link'] ?? '');
            $validated['button_color'] = $this->validate_color($input['button_color'] ?? CA_Banners_Constants::DEFAULT_BUTTON_COLOR, CA_Banners_Constants::DEFAULT_BUTTON_COLOR);
            $validated['button_text_color'] = $this->validate_color($input['button_text_color'] ?? CA_Banners_Constants::DEFAULT_BUTTON_TEXT_COLOR, CA_Banners_Constants::DEFAULT_BUTTON_TEXT_COLOR);
            $validated['button_border_color'] = $this->validate_color($input['button_border_color'] ?? CA_Banners_Constants::DEFAULT_BUTTON_BORDER_COLOR, CA_Banners_Constants::DEFAULT_BUTTON_BORDER_COLOR);
            
            // Validate button numeric values
            $validated['button_border_width'] = $this->validate_numeric($input['button_border_width'] ?? CA_Banners_Constants::DEFAULT_BUTTON_BORDER_WIDTH, CA_Banners_Constants::MIN_BUTTON_BORDER_WIDTH, CA_Banners_Constants::MAX_BUTTON_BORDER_WIDTH, CA_Banners_Constants::DEFAULT_BUTTON_BORDER_WIDTH);
            $validated['button_border_radius'] = $this->validate_numeric($input['button_border_radius'] ?? CA_Banners_Constants::DEFAULT_BUTTON_BORDER_RADIUS, CA_Banners_Constants::MIN_BUTTON_BORDER_RADIUS, CA_Banners_Constants::MAX_BUTTON_BORDER_RADIUS, CA_Banners_Constants::DEFAULT_BUTTON_BORDER_RADIUS);
            $validated['button_padding'] = $this->validate_numeric($input['button_padding'] ?? CA_Banners_Constants::DEFAULT_BUTTON_PADDING, CA_Banners_Constants::MIN_BUTTON_PADDING, CA_Banners_Constants::MAX_BUTTON_PADDING, CA_Banners_Constants::DEFAULT_BUTTON_PADDING);
            $validated['button_font_size'] = $this->validate_numeric($input['button_font_size'] ?? CA_Banners_Constants::DEFAULT_BUTTON_FONT_SIZE, CA_Banners_Constants::MIN_BUTTON_FONT_SIZE, CA_Banners_Constants::MAX_BUTTON_FONT_SIZE, CA_Banners_Constants::DEFAULT_BUTTON_FONT_SIZE);
            $validated['button_font_weight'] = $this->validate_font_weight($input['button_font_weight'] ?? CA_Banners_Constants::DEFAULT_BUTTON_FONT_WEIGHT, CA_Banners_Constants::DEFAULT_BUTTON_FONT_WEIGHT);
            $validated['button_lock_enabled'] = isset($input['button_lock_enabled']) ? (bool) $input['button_lock_enabled'] : false;
            $validated['button_lock_position'] = isset($input['button_lock_position']) && in_array($input['button_lock_position'], ['left', 'right']) ? $input['button_lock_position'] : 'left';
            $validated['button_gap'] = $this->validate_numeric($input['button_gap'] ?? 15, 0, 50, 15);
            $validated['button_new_window'] = isset($input['button_new_window']) ? (bool) $input['button_new_window'] : false;
            
            // Validate boolean fields
            $validated['sitewide'] = isset($input['sitewide']) ? (bool) $input['sitewide'] : CA_Banners_Constants::DEFAULT_SITEWIDE;
            $validated['disable_mobile'] = isset($input['disable_mobile']) ? (bool) $input['disable_mobile'] : CA_Banners_Constants::DEFAULT_DISABLE_MOBILE;
            $validated['sticky'] = isset($input['sticky']) ? (bool) $input['sticky'] : CA_Banners_Constants::DEFAULT_STICKY;
            $validated['vertical_padding'] = $this->validate_numeric($input['vertical_padding'] ?? 10, 0, 50, 10);
            
            // Additional validations
            $validated['link_color'] = $this->validate_color($input['link_color'] ?? '#0000ff', '#0000ff');
            $validated['image_start_date'] = $this->validate_date($input['image_start_date'] ?? '');
            $validated['image_end_date'] = $this->validate_date($input['image_end_date'] ?? '');
            
            // Log validation errors if any
            if (!empty($errors)) {
                $this->log_error('Validation errors: ' . implode(', ', $errors), CA_Banners_Error_Handler::TYPE_VALIDATION, CA_Banners_Error_Handler::SEVERITY_MEDIUM);
            }
            
        } catch (Exception $e) {
            $this->log_error('Comprehensive validation error', CA_Banners_Error_Handler::TYPE_VALIDATION, CA_Banners_Error_Handler::SEVERITY_MEDIUM, $e);
            // Return safe defaults
            return $this->get_safe_defaults();
        }
        
        return $validated;
    }
    
    /**
     * Get safe default values for all settings
     *
     * @return array Safe default values
     */
    private function get_safe_defaults() {
        return CA_Banners_Constants::get_default_settings();
    }
}
