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
     * Sanitize plugin settings
     *
     * @param array $input Raw input data
     * @return array Sanitized data
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Boolean fields
        $boolean_fields = array('enabled', 'sitewide', 'disable_mobile');
        foreach ($boolean_fields as $field) {
            $sanitized[$field] = isset($input[$field]) ? (bool) $input[$field] : false;
        }
        
        // Text fields - Allow HTML for banner message (from rich text editor)
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
        $sanitized['font_weight'] = isset($input['font_weight']) ? sanitize_text_field($input['font_weight']) : '600';
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
     * Validate banner settings and return sanitized values
     *
     * @param array $settings Raw settings array
     * @return array Validated settings array
     */
    public function validate_settings($settings) {
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
}
