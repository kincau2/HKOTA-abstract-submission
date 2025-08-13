<?php

class HKOTA_Template_Helper {
    
    /**
     * Load a template file with variables
     */
    public static function load_template($template_name, $variables = array()) {
        $template_path = HKOTA_ABSTRACT_PLUGIN_PATH . 'templates/' . $template_name . '.php';
        
        if (!file_exists($template_path)) {
            error_log('HKOTA Template not found: ' . $template_path);
            return '<div class="error">Template not found: ' . esc_html($template_name) . '</div>';
        }
        
        // Extract variables to make them available in template
        if (!empty($variables)) {
            extract($variables);
        }
        
        ob_start();
        include $template_path;
        return ob_get_clean();
    }
    
    /**
     * Get email template content
     */
    public static function get_email_template($template_name, $variables = array()) {
        return self::load_template('email-' . $template_name, $variables);
    }
    
    /**
     * Get admin page template content
     */
    public static function get_admin_template($template_name, $variables = array()) {
        return self::load_template('admin-' . $template_name, $variables);
    }
    
    /**
     * Get PDF template content
     */
    public static function get_pdf_template($template_name, $variables = array()) {
        return self::load_template('pdf-' . $template_name, $variables);
    }
    
    /**
     * Render template directly (for admin pages)
     */
    public static function render_template($template_name, $variables = array()) {
        $template_path = HKOTA_ABSTRACT_PLUGIN_PATH . 'templates/' . $template_name . '.php';
        
        if (!file_exists($template_path)) {
            echo '<div class="notice notice-error"><p>Template not found: ' . esc_html($template_name) . '</p></div>';
            return;
        }
        
        // Extract variables to make them available in template
        extract($variables);
        
        include $template_path;
    }
}
