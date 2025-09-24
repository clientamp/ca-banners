<?php
/**
 * Centralized Error Handler for CA Banners Plugin
 *
 * @package CA_Banners
 * @since 1.2.8
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CA Banners Error Handler class
 * 
 * Provides centralized error handling, logging, and user feedback
 * for consistent error management across the plugin.
 */
class CA_Banners_Error_Handler {
    
    /**
     * Error severity levels
     */
    const SEVERITY_LOW = CA_Banners_Constants::ERROR_SEVERITY_LOW;
    const SEVERITY_MEDIUM = CA_Banners_Constants::ERROR_SEVERITY_MEDIUM;
    const SEVERITY_HIGH = CA_Banners_Constants::ERROR_SEVERITY_HIGH;
    const SEVERITY_CRITICAL = CA_Banners_Constants::ERROR_SEVERITY_CRITICAL;
    
    /**
     * Error types
     */
    const TYPE_VALIDATION = CA_Banners_Constants::ERROR_TYPE_VALIDATION;
    const TYPE_SECURITY = CA_Banners_Constants::ERROR_TYPE_SECURITY;
    const TYPE_PERMISSION = CA_Banners_Constants::ERROR_TYPE_PERMISSION;
    const TYPE_SYSTEM = CA_Banners_Constants::ERROR_TYPE_SYSTEM;
    const TYPE_USER = CA_Banners_Constants::ERROR_TYPE_USER;
    const TYPE_CACHE = CA_Banners_Constants::ERROR_TYPE_CACHE;
    const TYPE_DATABASE = CA_Banners_Constants::ERROR_TYPE_DATABASE;
    
    /**
     * Instance of the error handler
     */
    private static $instance = null;
    
    /**
     * Error log entries for admin display
     */
    private $error_log = array();
    
    /**
     * Maximum number of errors to keep in memory
     */
    private $max_errors = CA_Banners_Constants::MAX_ERROR_LOG_ENTRIES;
    
    /**
     * Get singleton instance of the error handler
     * 
     * Implements the singleton pattern to ensure only one error handler
     * instance exists throughout the plugin lifecycle.
     * 
     * @since 1.2.8
     * @return CA_Banners_Error_Handler The singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Private constructor for singleton pattern
     * 
     * Initializes the error handler by registering PHP error handlers
     * and setting up error logging infrastructure.
     * 
     * @since 1.2.8
     */
    private function __construct() {
        // Register error handlers
        $this->register_error_handlers();
    }
    
    /**
     * Register error handlers
     * 
     * Sets up PHP error handlers, exception handlers, and shutdown functions
     * to catch and log various types of errors.
     * 
     * @since 1.2.8
     */
    private function register_error_handlers() {
        // Register shutdown function for fatal errors
        register_shutdown_function(array($this, 'handle_fatal_error'));
        
        // Register custom error handler for PHP errors
        set_error_handler(array($this, 'handle_php_error'), E_ALL);
        
        // Register exception handler
        set_exception_handler(array($this, 'handle_exception'));
    }
    
    /**
     * Log an error with context and severity
     * 
     * Centralized error logging method that stores errors in memory for
     * admin display and optionally logs to WordPress debug log.
     * 
     * @since 1.2.8
     * @param string $message Error message
     * @param string $type Error type (use class constants)
     * @param string $severity Error severity (use class constants)
     * @param Exception|null $exception Exception object if available
     * @param array $context Additional context data
     * @param bool $show_to_user Whether to show error to user
     */
    public function log_error($message, $type = self::TYPE_SYSTEM, $severity = self::SEVERITY_MEDIUM, $exception = null, $context = array(), $show_to_user = false) {
        // Only log if WP_DEBUG is enabled or severity is high/critical
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            if (!in_array($severity, array(self::SEVERITY_HIGH, self::SEVERITY_CRITICAL))) {
                return;
            }
        }
        
        $error_data = array(
            'message' => $message,
            'type' => $type,
            'severity' => $severity,
            'timestamp' => current_time('mysql'),
            'context' => $this->get_error_context($context),
            'exception' => $exception,
            'show_to_user' => $show_to_user,
            'user_id' => get_current_user_id(),
            'url' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'ip_address' => $this->get_client_ip(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        );
        
        // Add exception details if available
        if ($exception instanceof Exception) {
            $error_data['exception_message'] = $exception->getMessage();
            $error_data['exception_file'] = $exception->getFile();
            $error_data['exception_line'] = $exception->getLine();
            $error_data['exception_trace'] = $exception->getTraceAsString();
        }
        
        // Store error in memory for admin display
        $this->store_error_in_memory($error_data);
        
        // Log to WordPress error log
        $this->write_to_error_log($error_data);
        
        // Show to user if requested and appropriate
        if ($show_to_user && $this->should_show_to_user($severity)) {
            $this->show_user_notice($error_data);
        }
        
        // Fire action for other plugins to handle errors
        do_action('ca_banners_error_logged', $error_data);
    }
    
    /**
     * Handle fatal errors
     */
    public function handle_fatal_error() {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
            $this->log_error(
                'Fatal error occurred: ' . $error['message'],
                self::TYPE_SYSTEM,
                self::SEVERITY_CRITICAL,
                new Exception($error['message']),
                array('file' => $error['file'], 'line' => $error['line'])
            );
        }
    }
    
    /**
     * Handle PHP errors
     */
    public function handle_php_error($errno, $errstr, $errfile, $errline) {
        $severity = $this->get_severity_from_error_level($errno);
        
        $this->log_error(
            'PHP Error: ' . $errstr,
            self::TYPE_SYSTEM,
            $severity,
            null,
            array('file' => $errfile, 'line' => $errline, 'errno' => $errno)
        );
        
        // Don't execute PHP internal error handler
        return true;
    }
    
    /**
     * Handle uncaught exceptions
     */
    public function handle_exception($exception) {
        $this->log_error(
            'Uncaught exception: ' . $exception->getMessage(),
            self::TYPE_SYSTEM,
            self::SEVERITY_CRITICAL,
            $exception
        );
    }
    
    /**
     * Get error context information
     */
    private function get_error_context($additional_context = array()) {
        $context = array();
        
        // Basic context
        $context['is_admin'] = is_admin();
        $context['is_ajax'] = wp_doing_ajax();
        $context['is_cron'] = wp_doing_cron();
        $context['is_rest'] = defined('REST_REQUEST') && REST_REQUEST;
        
        // User context
        if (function_exists('wp_get_current_user')) {
            $user = wp_get_current_user();
            if ($user && $user->ID) {
                $context['user_id'] = $user->ID;
                $context['user_login'] = $user->user_login;
                $context['user_capabilities'] = $user->allcaps;
            } else {
                $context['user_type'] = 'guest';
            }
        }
        
        // Request context
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $context['request_method'] = $_SERVER['REQUEST_METHOD'];
        }
        
        if (isset($_SERVER['HTTP_REFERER'])) {
            $context['referer'] = $_SERVER['HTTP_REFERER'];
        }
        
        // Plugin context
        $context['plugin_version'] = defined('CA_BANNERS_VERSION') ? CA_BANNERS_VERSION : 'unknown';
        $context['wordpress_version'] = get_bloginfo('version');
        $context['php_version'] = PHP_VERSION;
        
        // Merge additional context
        $context = array_merge($context, $additional_context);
        
        return $context;
    }
    
    /**
     * Store error in memory for admin display
     */
    private function store_error_in_memory($error_data) {
        // Add to beginning of array
        array_unshift($this->error_log, $error_data);
        
        // Keep only the most recent errors
        if (count($this->error_log) > $this->max_errors) {
            $this->error_log = array_slice($this->error_log, 0, $this->max_errors);
        }
    }
    
    /**
     * Write error to WordPress error log
     */
    private function write_to_error_log($error_data) {
        $log_message = sprintf(
            '[CA Banners %s] %s | Type: %s | Severity: %s | Context: %s',
            $error_data['severity'],
            $error_data['message'],
            $error_data['type'],
            $error_data['severity'],
            json_encode($error_data['context'])
        );
        
        if (isset($error_data['exception_message'])) {
            $log_message .= sprintf(
                ' | Exception: %s in %s:%d',
                $error_data['exception_message'],
                $error_data['exception_file'],
                $error_data['exception_line']
            );
        }
        
        error_log($log_message);
    }
    
    /**
     * Get severity level from PHP error level
     */
    private function get_severity_from_error_level($errno) {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return self::SEVERITY_CRITICAL;
                
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                return self::SEVERITY_HIGH;
                
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return self::SEVERITY_MEDIUM;
                
            default:
                return self::SEVERITY_LOW;
        }
    }
    
    /**
     * Determine if error should be shown to user
     */
    private function should_show_to_user($severity) {
        // Only show high/critical errors to users
        return in_array($severity, array(self::SEVERITY_HIGH, self::SEVERITY_CRITICAL));
    }
    
    /**
     * Show user notice for errors
     */
    private function show_user_notice($error_data) {
        if (!is_admin() || !current_user_can(CA_Banners_Constants::CAPABILITY_MANAGE)) {
            return;
        }
        
        $class = 'notice notice-error';
        $message = sprintf(
            __('CA Banners Error: %s', CA_Banners_Constants::TEXT_DOMAIN),
            esc_html($error_data['message'])
        );
        
        printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    }
    
    /**
     * Get error log for admin display
     */
    public function get_error_log($limit = 20) {
        return array_slice($this->error_log, 0, $limit);
    }
    
    /**
     * Clear error log
     */
    public function clear_error_log() {
        $this->error_log = array();
    }
    
    /**
     * Get error statistics
     */
    public function get_error_stats() {
        $stats = array(
            'total_errors' => count($this->error_log),
            'by_severity' => array(),
            'by_type' => array(),
            'recent_errors' => 0
        );
        
        $one_hour_ago = strtotime('-1 hour');
        
        foreach ($this->error_log as $error) {
            // Count by severity
            if (!isset($stats['by_severity'][$error['severity']])) {
                $stats['by_severity'][$error['severity']] = 0;
            }
            $stats['by_severity'][$error['severity']]++;
            
            // Count by type
            if (!isset($stats['by_type'][$error['type']])) {
                $stats['by_type'][$error['type']] = 0;
            }
            $stats['by_type'][$error['type']]++;
            
            // Count recent errors
            if (strtotime($error['timestamp']) > $one_hour_ago) {
                $stats['recent_errors']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Handle security errors with appropriate response
     */
    public function handle_security_error($message, $context = array()) {
        $this->log_error(
            $message,
            self::TYPE_SECURITY,
            self::SEVERITY_HIGH,
            null,
            $context,
            false // Don't show to user for security
        );
        
        // Log security event
        if (function_exists('wp_log_security_event')) {
            wp_log_security_event('ca_banners_security', $message, $context);
        }
    }
    
    /**
     * Handle validation errors
     */
    public function handle_validation_error($message, $field = null, $value = null) {
        $context = array();
        if ($field) {
            $context['field'] = $field;
        }
        if ($value !== null) {
            $context['value'] = $value;
        }
        
        $this->log_error(
            $message,
            self::TYPE_VALIDATION,
            self::SEVERITY_MEDIUM,
            null,
            $context
        );
    }
    
    /**
     * Handle permission errors
     */
    public function handle_permission_error($message, $required_capability = null) {
        $context = array();
        if ($required_capability) {
            $context['required_capability'] = $required_capability;
            $context['user_capabilities'] = wp_get_current_user()->allcaps;
        }
        
        $this->log_error(
            $message,
            self::TYPE_PERMISSION,
            self::SEVERITY_HIGH,
            null,
            $context
        );
    }
}
