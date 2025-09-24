<?php
/**
 * Scheduler class for handling banner scheduling logic
 *
 * @package CA_Banners
 * @since 1.2.7
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CA Banners Scheduler class
 */
class CA_Banners_Scheduler {
    
    /**
     * Check if current time is within date range
     *
     * @param string $start_date Start date (datetime-local format)
     * @param string $end_date End date (datetime-local format)
     * @return bool True if within date range
     */
    public function is_within_date_range($start_date, $end_date) {
        if (empty($start_date) && empty($end_date)) {
            return true;
        }
        
        $current_time = current_time('timestamp');
        
        if (!empty($start_date)) {
            $start_timestamp = strtotime($start_date);
            if ($start_timestamp === false || $current_time < $start_timestamp) {
                return false;
            }
        }
        
        if (!empty($end_date)) {
            $end_timestamp = strtotime($end_date);
            if ($end_timestamp === false || $current_time > $end_timestamp) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if banner should be displayed based on scheduling
     *
     * @param array $settings Banner settings
     * @return bool True if banner should be displayed
     */
    public function is_banner_scheduled($settings) {
        return $this->is_within_date_range(
            isset($settings['start_date']) ? $settings['start_date'] : '',
            isset($settings['end_date']) ? $settings['end_date'] : ''
        );
    }
    
    /**
     * Check if image banner should be displayed based on scheduling
     *
     * @param array $settings Banner settings
     * @return bool True if image banner should be displayed
     */
    public function is_image_banner_scheduled($settings) {
        return $this->is_within_date_range(
            isset($settings['image_start_date']) ? $settings['image_start_date'] : '',
            isset($settings['image_end_date']) ? $settings['image_end_date'] : ''
        );
    }
    
    /**
     * Get formatted date for display
     *
     * @param string $date Date string
     * @param string $format Date format
     * @return string Formatted date
     */
    public function format_date($date, $format = 'Y-m-d H:i') {
        if (empty($date)) {
            return '';
        }
        
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }
        
        return date($format, $timestamp);
    }
    
    /**
     * Get time until banner starts
     *
     * @param string $start_date Start date
     * @return string Time until start
     */
    public function get_time_until_start($start_date) {
        if (empty($start_date)) {
            return '';
        }
        
        $start_timestamp = strtotime($start_date);
        $current_timestamp = current_time('timestamp');
        
        if ($start_timestamp === false || $current_timestamp >= $start_timestamp) {
            return '';
        }
        
        $diff = $start_timestamp - $current_timestamp;
        
        if ($diff < 3600) {
            return sprintf(__('%d minutes', 'ca-banners'), round($diff / 60));
        } elseif ($diff < 86400) {
            return sprintf(__('%d hours', 'ca-banners'), round($diff / 3600));
        } else {
            return sprintf(__('%d days', 'ca-banners'), round($diff / 86400));
        }
    }
    
    /**
     * Get time until banner ends
     *
     * @param string $end_date End date
     * @return string Time until end
     */
    public function get_time_until_end($end_date) {
        if (empty($end_date)) {
            return '';
        }
        
        $end_timestamp = strtotime($end_date);
        $current_timestamp = current_time('timestamp');
        
        if ($end_timestamp === false || $current_timestamp >= $end_timestamp) {
            return '';
        }
        
        $diff = $end_timestamp - $current_timestamp;
        
        if ($diff < 3600) {
            return sprintf(__('%d minutes', 'ca-banners'), round($diff / 60));
        } elseif ($diff < 86400) {
            return sprintf(__('%d hours', 'ca-banners'), round($diff / 3600));
        } else {
            return sprintf(__('%d days', 'ca-banners'), round($diff / 86400));
        }
    }
}
