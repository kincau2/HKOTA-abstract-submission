<?php

class HKOTA_Shortcode {
    
    public function __construct() {
        add_shortcode('hkota_abstract_form', array($this, 'render_abstract_form'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_submit_abstract', array($this, 'handle_form_submission'));
        add_action('wp_ajax_nopriv_submit_abstract', array($this, 'handle_form_submission'));
        add_filter('login_redirect', array($this, 'login_redirect'), 10);
        $this->check_redirect_after_login();
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
            // Set cookie with current URL for post-login redirect
            $current_url = $this->get_current_url();
            $this->set_redirect_cookie($current_url);
            
            return HKOTA_Template_Helper::load_template('form-login-required', array(
                'login_url' => home_url('/login')
            ));
        }
        $current_user = wp_get_current_user();
        // Check deadline
        $deadline_info = HKOTA_Admin::get_deadline_info();
        
        if ($deadline_info['has_deadline'] && $deadline_info['is_passed']) {
            // Get existing submission for deadline passed page
            $existing_submission = HKOTA_Database::get_user_submission($current_user->ID);
            return HKOTA_Template_Helper::load_template('form-deadline-passed', array(
                'deadline_info' => $deadline_info,
                'existing_submission' => $existing_submission
            ));
        }
        
        // Check user role and render accordingly
        if (current_user_can('hkota_reviewer')) {
            return HKOTA_Template_Helper::load_template('form-reviewer-interface');
        } else {
            return $this->render_user_form($current_user, $deadline_info);
        }
    }
    
    private function render_user_form($user, $deadline_info = null) {
        // Get existing submission if any
        $existing_submission = HKOTA_Database::get_user_submission($user->ID);
        
        if ($deadline_info === null) {
            $deadline_info = HKOTA_Admin::get_deadline_info();
        }
        
        return HKOTA_Template_Helper::load_template('form-user-submission', array(
            'user' => $user,
            'existing_submission' => $existing_submission,
            'deadline_info' => $deadline_info
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
        
        // Check deadline
        if (HKOTA_Admin::is_deadline_passed()) {
            wp_send_json_error('The submission deadline has passed. You can no longer submit or edit abstracts.');
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
        );
        
        // Handle keywords - support both individual fields and combined field
        $keywords = '';
        if (!empty($_POST['keywords'])) {
            // Use combined keywords field (from JavaScript)
            $keywords = sanitize_text_field($_POST['keywords']);
        } else {
            // Fallback to individual keyword fields
            $individual_keywords = array();
            for ($i = 1; $i <= 5; $i++) {
                if (!empty($_POST["keyword_$i"])) {
                    $individual_keywords[] = sanitize_text_field($_POST["keyword_$i"]);
                }
            }
            $keywords = implode(', ', $individual_keywords);
        }
        
        $data['keywords'] = $keywords;
        
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
        $keywords_array = array_filter($keywords_array); // Remove empty values
        
        if (count($keywords_array) != 5) {
            wp_send_json_error('Please provide exactly 5 keywords. You provided ' . count($keywords_array) . '.');
        }
        
        // Check for duplicate keywords
        $lowercase_keywords = array_map('strtolower', $keywords_array);
        if (count($lowercase_keywords) !== count(array_unique($lowercase_keywords))) {
            wp_send_json_error('Please ensure all keywords are unique.');
        }
        
        // Validate keyword length
        foreach ($keywords_array as $keyword) {
            if (strlen($keyword) > 50) {
                wp_send_json_error('Each keyword must be 50 characters or less.');
            }
        }
        
        // Validate word limits
        $validation_result = $this->validate_word_limits($data);
        if ($validation_result !== true) {
            wp_send_json_error($validation_result);
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
    
    /**
     * Get current page URL for login redirect
     */
    private function get_current_url() {
        global $wp;
        
        // Use WordPress's current URL detection
        if (isset($wp->request)) {
            return home_url(add_query_arg(array(), $wp->request));
        }
        
        // Fallback to server variables
        $protocol = is_ssl() ? 'https://' : 'http://';
        $current_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        return $current_url;
    }
    
    /**
     * Set redirect cookie when user visits login required page
     */
    private function set_redirect_cookie($url) {
        // Set cookie for 30 minutes
        $expire_time = time() + ( 60 * 60 * 24);
        
        // Use secure and httponly flags for security
        setcookie(
            'hkota_redirect_after_login', 
            $url, 
            $expire_time, 
            '/', 
            '', 
            is_ssl(), 
            true
        );
    }

    /**
     * Handle login redirect for abstract form
     */
    public function login_redirect() {
        
        // Check if redirect cookie exists
        if (isset($_COOKIE['hkota_redirect_after_login'])) {
            $redirect_url = $_COOKIE['hkota_redirect_after_login'];
            // Clear the cookie
            setcookie('hkota_redirect_after_login', '', time() - 3600, '/');
        }
        
        // Default behavior for other cases
        return $redirect_url . '/?import-check=true' ;
    }

     public function check_redirect_after_login() {

        // Only for logged-in users
        if (!is_user_logged_in()) {
            return;
        }
        // Check if redirect cookie exists
        if (isset($_COOKIE['hkota_redirect_after_login'])) {
            $redirect_url = $_COOKIE['hkota_redirect_after_login'] . '/?import-check=true' ;
            setcookie('hkota_redirect_after_login', '', time() - 3600, '/');
            wp_safe_redirect($redirect_url);
            exit;
        }
    }
    
    /**
     * Validate word limits for form fields
     */
    private function validate_word_limits($data) {
        $errors = array();
        
        // Validate title (20 words max)
        $title_words = $this->count_words($data['abstract_title']);
        if ($title_words > 20) {
            $errors[] = "Abstract title exceeds 20 words limit (current: {$title_words} words)";
        }
        
        // Validate authors (8 authors max)
        $author_count = $this->count_authors($data['authors']);
        if ($author_count > 8) {
            $errors[] = "Authors field exceeds 8 authors limit (current: {$author_count} authors)";
        }
        
        // Validate background (500 words max)
        $background_words = $this->count_words($data['background']);
        if ($background_words > 500) {
            $errors[] = "Background section exceeds 500 words limit (current: {$background_words} words)";
        }
        
        // Validate methods (500 words max)
        $methods_words = $this->count_words($data['methods']);
        if ($methods_words > 500) {
            $errors[] = "Methods section exceeds 500 words limit (current: {$methods_words} words)";
        }
        
        // Validate results (500 words max)
        $results_words = $this->count_words($data['results']);
        if ($results_words > 500) {
            $errors[] = "Results and Findings section exceeds 500 words limit (current: {$results_words} words)";
        }
        
        // Validate conclusion (500 words max)
        $conclusion_words = $this->count_words($data['conclusion']);
        if ($conclusion_words > 500) {
            $errors[] = "Conclusion section exceeds 500 words limit (current: {$conclusion_words} words)";
        }
        
        if (!empty($errors)) {
            return implode(' ', $errors);
        }
        
        return true;
    }
    
    /**
     * Count words in a text
     */
    private function count_words($text) {
        if (empty($text) || trim($text) === '') {
            return 0;
        }
        return str_word_count(trim($text));
    }
    
    /**
     * Count authors in authors field
     */
    private function count_authors($text) {
        if (empty($text) || trim($text) === '') {
            return 0;
        }
        
        // Count patterns like "Name(1)" or "Name (1)" or names separated by commas
        // Split by commas and count non-empty entries
        $author_parts = explode(',', $text);
        $author_count = 0;
        
        foreach ($author_parts as $part) {
            $trimmed = trim($part);
            if (!empty($trimmed)) {
                $author_count++;
            }
        }
        
        return $author_count;
    }
    
}
