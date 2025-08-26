<?php
/**
 * Plugin Name: HKOTA Abstract Submission
 * Plugin URI: https://hkota.org
 * Description: A plugin for managing abstract submissions with user roles and admin panel management.
 * Version: 1.0.0
 * Author: HKOTA
 * License: GPL v2 or later
 * Text Domain: hkota-abstract-submission
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('HKOTA_ABSTRACT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HKOTA_ABSTRACT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HKOTA_ABSTRACT_VERSION', '1.0.0');

class HKOTAAbstractSubmission {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    } 
    
    public function init() {
        // Load plugin files
        $this->load_includes();
        
        // Initialize components conditionally
        new HKOTA_Shortcode();
        new HKOTA_File_Handler();
        
        // Only initialize admin components in admin context
        if (is_admin()) {
            new HKOTA_Admin();
        }

    }
    
    private function load_includes() {
        require HKOTA_ABSTRACT_PLUGIN_PATH . 'includes/class-template-helper.php';
        require HKOTA_ABSTRACT_PLUGIN_PATH . 'includes/class-database.php';
        require HKOTA_ABSTRACT_PLUGIN_PATH . 'includes/class-shortcode.php';
        require HKOTA_ABSTRACT_PLUGIN_PATH . 'includes/class-admin.php';
        require HKOTA_ABSTRACT_PLUGIN_PATH . 'includes/class-email.php';
        require HKOTA_ABSTRACT_PLUGIN_PATH . 'includes/class-pdf-generator.php';
        require HKOTA_ABSTRACT_PLUGIN_PATH . 'includes/class-file-handler.php';
        require HKOTA_ABSTRACT_PLUGIN_PATH . 'vendor/autoload.php';
    }
    
    public function activate() {
        
        // Load plugin files
        $this->load_includes();

        // Create necessary database tables
        HKOTA_Database::create_tables();
        
        // Create upload directory and .htaccess
        HKOTA_File_Handler::create_upload_directory();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Clean up if needed
        flush_rewrite_rules();
    }
}

// Initialize the plugin
new HKOTAAbstractSubmission();
