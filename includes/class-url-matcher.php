<?php
/**
 * URL Matcher class for handling URL matching logic
 *
 * @package CA_Banners
 * @since 1.2.7
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CA Banners URL Matcher class
 */
class CA_Banners_URL_Matcher {
    
    /**
     * Normalize URL for consistent comparison
     * 
     * Standardizes URL format by ensuring proper leading/trailing slashes
     * and handling edge cases like empty URLs and root paths.
     * 
     * @since 1.2.7
     * @param string $url Raw URL to normalize
     * @return string Normalized URL with consistent format
     */
    public function normalize_url($url) {
        $url = trim($url);
        
        // Handle empty or root URLs
        if ($url === '' || $url === '/') {
            return '/';
        }
        
        // Remove leading slash if present, then add trailing slash
        $url = ltrim($url, '/');
        $url = '/' . rtrim($url, '/') . '/';
        
        return $url;
    }
    
    /**
     * Process URL list from textarea input
     * 
     * Converts a newline-separated URL list into an array of normalized URLs
     * with duplicates removed.
     * 
     * @since 1.2.7
     * @param string $url_list Raw URL list from textarea
     * @return array Array of normalized URLs
     */
    public function process_url_list($url_list) {
        if (empty($url_list)) {
            return array();
        }
        
        $urls = preg_split('/\r\n|\r|\n/', $url_list);
        $urls = array_map(array($this, 'normalize_url'), $urls);
        $urls = array_filter($urls, function($url) {
            return !empty($url);
        });
        
        return array_unique($urls);
    }
    
    /**
     * Check if current URL matches any URL in the list
     * 
     * Supports exact matches, wildcard matches (ending with *), and partial
     * matches for subdirectories.
     * 
     * @since 1.2.7
     * @param string $current_url Current normalized URL
     * @param array $url_list Array of URLs to match against
     * @return bool True if match found
     */
    public function url_matches($current_url, $url_list) {
        if (empty($url_list) || empty($current_url)) {
            return false;
        }
        
        foreach ($url_list as $url) {
            // Exact match
            if ($current_url === $url) {
                return true;
            }
            
            // Wildcard match (if URL ends with *)
            if (substr($url, -1) === '*') {
                $pattern = rtrim($url, '*');
                if (strpos($current_url, $pattern) === 0) {
                    return true;
                }
            }
            
            // Partial match for subdirectories
            if (strpos($current_url, $url) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if banner should be displayed based on URL settings
     * 
     * Determines banner visibility based on sitewide setting, include URLs,
     * and exclude URLs. Exclude URLs take precedence over include URLs.
     * 
     * @since 1.2.7
     * @param string $current_url Current page URL
     * @param bool $sitewide Whether sitewide display is enabled
     * @param string $include_urls Include URLs setting
     * @param string $exclude_urls Exclude URLs setting
     * @return bool True if banner should be displayed
     */
    public function should_display_banner($current_url, $sitewide, $include_urls, $exclude_urls) {
        // Normalize current URL
        $normalized_url = $this->normalize_url($current_url);
        
        // Process exclude URLs
        $exclude_urls_array = $this->process_url_list($exclude_urls);
        
        // Process include URLs
        $include_urls_array = $this->process_url_list($include_urls);
        
        // Check if the banner should be displayed
        $should_display = false;
        
        if ($sitewide) {
            // If sitewide is enabled, display everywhere except excluded pages
            $should_display = !$this->url_matches($normalized_url, $exclude_urls_array);
        } else {
            // If not sitewide, check if current URL is in the include list
            // If no URLs specified, show everywhere (backward compatibility)
            $should_display = empty($include_urls_array) || $this->url_matches($normalized_url, $include_urls_array);
        }
        
        // Apply exclude filter to both modes (exclude always takes precedence)
        if ($should_display && !empty($exclude_urls_array)) {
            $should_display = !$this->url_matches($normalized_url, $exclude_urls_array);
        }
        
        return $should_display;
    }
}
