<?php

class HKOTA_Shortcode {
    
    public function __construct() {
        add_shortcode('hkota_abstract_form', array($this, 'render_abstract_form'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_submit_abstract', array($this, 'handle_form_submission'));
        add_action('wp_ajax_nopriv_submit_abstract', array($this, 'handle_form_submission'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('hkota-abstract-style', HKOTA_ABSTRACT_PLUGIN_URL . 'assets/style.css', array(), HKOTA_ABSTRACT_VERSION);
        wp_enqueue_script('hkota-abstract-script', HKOTA_ABSTRACT_PLUGIN_URL . 'assets/script.js', array('jquery'), HKOTA_ABSTRACT_VERSION, true);
        
        wp_localize_script('hkota-abstract-script', 'hkota_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hkota_abstract_nonce')
        ));
    }
    
    public function render_abstract_form($atts) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return HKOTA_Template_Helper::load_template('form-login-required', array(
                'login_url' => wp_login_url(get_permalink())
            ));
        }
        
        $current_user = wp_get_current_user();
        
        // Check user role and render accordingly
        if (current_user_can('hkota_reviewer')) {
            return HKOTA_Template_Helper::load_template('form-reviewer-interface');
        } else {
            return $this->render_user_form($current_user);
        }
    }
    
    private function render_user_form($user) {
        // Get existing submission if any
        $existing_submission = HKOTA_Database::get_user_submission($user->ID);
        
        return HKOTA_Template_Helper::load_template('form-user-submission', array(
            'user' => $user,
            'existing_submission' => $existing_submission
        ));
    }
    
    public function handle_form_submission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['hkota_nonce'], 'hkota_abstract_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to submit an abstract.');
        }
        
        $current_user = wp_get_current_user();
        
        // Sanitize form data
        $data = array(
            'user_id' => $current_user->ID,
            'title' => sanitize_text_field($_POST['title']),
            'surname' => sanitize_text_field($_POST['surname']),
            'given_name' => sanitize_text_field($_POST['given_name']),
            'contact_number' => sanitize_text_field($_POST['contact_number']),
            'contact_email' => sanitize_email($_POST['contact_email']),
            'organization' => sanitize_text_field($_POST['organization']),
            'theme' => sanitize_text_field($_POST['theme']),
            'presentation_preference' => sanitize_text_field($_POST['presentation_preference']),
            'abstract_title' => sanitize_text_field($_POST['abstract_title']),
            'authors' => sanitize_textarea_field($_POST['authors']),
            'affiliations' => sanitize_textarea_field($_POST['affiliations']),
            'background' => sanitize_textarea_field($_POST['background']),
            'methods' => sanitize_textarea_field($_POST['methods']),
            'results' => sanitize_textarea_field($_POST['results']),
            'conclusion' => sanitize_textarea_field($_POST['conclusion']),
            'keywords' => sanitize_text_field($_POST['keywords'])
        );
        
        // Validate required fields
        $required_fields = ['title', 'surname', 'given_name', 'contact_number', 'contact_email', 
                           'organization', 'theme', 'presentation_preference', 'abstract_title', 
                           'authors', 'affiliations', 'background', 'methods', 'results', 
                           'conclusion', 'keywords'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                wp_send_json_error('Please fill in all required fields.');
            }
        }
        
        // Validate keywords (should be exactly 5)
        $keywords_array = array_map('trim', explode(',', $data['keywords']));
        if (count($keywords_array) != 5) {
            wp_send_json_error('Please provide exactly 5 keywords separated by commas.');
        }
        
        // Insert/update submission
        $result = HKOTA_Database::insert_submission($data);
        
        if ($result !== false) {
            // Send confirmation email
            HKOTA_Email::send_submission_confirmation($data);
            
            wp_send_json_success('Your abstract has been submitted successfully. You will receive a confirmation email shortly.');
        } else {
            wp_send_json_error('There was an error submitting your abstract. Please try again.');
        }
    }
}
