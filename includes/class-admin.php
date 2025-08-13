<?php

/**
 * HKOTA Admin Management Class
 * 
 * Handles all WordPress admin panel functionality including:
 * - Submission listing and management
 * - Status updates and notifications
 * - PDF downloads
 * - Reviewer capability management (NEW)
 * 
 * Reviewer Management Features:
 * - AJAX-powered user search by email
 * - Add/remove reviewer capabilities
 * - Real-time reviewer list updates
 * - Granular permission controls
 */
class HKOTA_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_update_submission_status', array($this, 'handle_status_update'));
        add_action('wp_ajax_download_submission_pdf', array($this, 'handle_pdf_download'));
        
        // Reviewer management AJAX handlers
        add_action('wp_ajax_search_users_for_reviewer', array($this, 'handle_user_search'));
        add_action('wp_ajax_add_reviewer', array($this, 'handle_add_reviewer'));
        add_action('wp_ajax_remove_reviewer', array($this, 'handle_remove_reviewer'));
    }
    
    public function add_admin_menu() {
        // Main menu item
        add_menu_page(
            'Abstract Submission',
            'Abstract Submission',
            'manage_options',
            'hkota-abstract-submissions',
            array($this, 'submissions_page'),
            'dashicons-edit-page',
            30
        );
        
        // Submenu items
        add_submenu_page(
            'hkota-abstract-submissions',
            'Abstract Submissions',
            'Abstract Submissions',
            'manage_options',
            'hkota-abstract-submissions',
            array($this, 'submissions_page')
        );
        
        add_submenu_page(
            'hkota-abstract-submissions',
            'Submission Settings',
            'Submission Settings',
            'manage_options',
            'hkota-submission-settings',
            array($this, 'settings_page')
        );
    }
    
    public function enqueue_admin_scripts($hook) {

        if (strpos($hook, 'hkota') === false) {
            return;
        }
        
        wp_enqueue_style('hkota-admin-style', HKOTA_ABSTRACT_PLUGIN_URL . 'assets/admin-style.css', array(), HKOTA_ABSTRACT_VERSION);
        wp_enqueue_script('hkota-admin-script', HKOTA_ABSTRACT_PLUGIN_URL . 'assets/admin-script.js', array('jquery'), HKOTA_ABSTRACT_VERSION, true);
        
        wp_localize_script('hkota-admin-script', 'hkota_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hkota_admin_nonce')
        ));
    }
    
    public function submissions_page() {
        $submissions = HKOTA_Database::get_all_submissions();
        
        HKOTA_Template_Helper::render_template('admin-submissions-list', array(
            'submissions' => $submissions
        ));
    }
    
    public function settings_page() {
        HKOTA_Template_Helper::render_template('admin-settings');
    }
    
    public function handle_status_update() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hkota_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions.');
        }
        
        $submission_id = intval($_POST['submission_id']);
        $status = sanitize_text_field($_POST['status']);
        
        if (!in_array($status, array('accepted', 'rejected'))) {
            wp_send_json_error('Invalid status.');
        }
        
        // Get submission details
        $submission = HKOTA_Database::get_submission_by_id($submission_id);
        if (!$submission) {
            wp_send_json_error('Submission not found.');
        }
        
        // Update status
        $result = HKOTA_Database::update_submission_status($submission_id, $status);
        
        if ($result !== false) {
            // Send email notification to applicant
            HKOTA_Email::send_status_notification($submission, $status);
            
            wp_send_json_success('Status updated successfully and email sent to applicant.');
        } else {
            wp_send_json_error('Failed to update status.');
        }
    }
    
    public function handle_pdf_download() {
        // Verify nonce
        if (!wp_verify_nonce($_GET['nonce'], 'hkota_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }
        
        $submission_id = intval($_GET['submission_id']);
        $submission = HKOTA_Database::get_submission_by_id($submission_id);
        
        if (!$submission) {
            wp_die('Submission not found.');
        }
        
        // Generate and download PDF
        HKOTA_PDF_Generator::generate_submission_pdf($submission);
    }
    
    /**
     * Handle AJAX user search for reviewer selection
     */
    public function handle_user_search() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hkota_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions.');
        }
        
        $search_term = sanitize_text_field($_POST['search_term']);
        
        if (strlen($search_term) < 2) {
            wp_send_json_error('Search term too short.');
        }
        
        // Search for users by email or display name
        $users = get_users(array(
            'search' => '*' . $search_term . '*',
            'search_columns' => array('user_email', 'display_name'),
            'number' => 10,
            'fields' => array('ID', 'user_email', 'display_name')
        ));
        
        $results = array();
        foreach ($users as $user) {
            // Skip users who already have the capability
            if (!user_can($user->ID, 'hkota_reviewer')) {
                $results[] = array(
                    'id' => $user->ID,
                    'email' => $user->user_email,
                    'name' => $user->display_name,
                    'label' => $user->display_name . ' (' . $user->user_email . ')'
                );
            }
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * Handle adding reviewer capability to a user
     */
    public function handle_add_reviewer() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hkota_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions.');
        }
        
        $user_id = intval($_POST['user_id']);
        $user = get_user_by('ID', $user_id);
        
        if (!$user) {
            wp_send_json_error('User not found.');
        }
        
        // Add reviewer capability
        $user->add_cap('hkota_reviewer');
        
        wp_send_json_success(array(
            'message' => 'Reviewer added successfully.',
            'user' => array(
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email
            )
        ));
    }
    
    /**
     * Handle removing reviewer capability from a user
     */
    public function handle_remove_reviewer() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hkota_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions.');
        }
        
        $user_id = intval($_POST['user_id']);
        $user = get_user_by('ID', $user_id);
        
        if (!$user) {
            wp_send_json_error('User not found.');
        }
        
        // Remove reviewer capability
        $user->remove_cap('hkota_reviewer');
        
        wp_send_json_success('Reviewer removed successfully.');
    }
    
    /**
     * Get all users with reviewer capability
     */
    public static function get_reviewers() {
        $all_users = get_users();
        $reviewers = array();
        
        foreach ($all_users as $user) {
            if (user_can($user->ID, 'hkota_reviewer')) {
                $reviewers[] = array(
                    'id' => $user->ID,
                    'name' => $user->display_name,
                    'email' => $user->user_email
                );
            }
        }
        
        return $reviewers;
    }
}
