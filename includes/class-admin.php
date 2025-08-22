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
        add_action('wp_ajax_download_supporting_document', array($this, 'handle_supporting_document_download'));
        add_action('wp_ajax_delete_submission', array($this, 'handle_delete_submission'));
        
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
        // Handle form submission
        if (isset($_POST['submit_deadline_settings']) && check_admin_referer('hkota_deadline_settings', 'deadline_nonce')) {
            $this->handle_deadline_form_submission();
        }
        
        if (isset($_POST['clear_deadline']) && check_admin_referer('hkota_deadline_settings', 'deadline_nonce')) {
            $this->handle_clear_deadline_form();
        }
        
        // Handle document deadline form submission
        if (isset($_POST['submit_document_deadline_settings']) && check_admin_referer('hkota_document_deadline_settings', 'document_deadline_nonce')) {
            $this->handle_document_deadline_form_submission();
        }
        
        if (isset($_POST['clear_document_deadline']) && check_admin_referer('hkota_document_deadline_settings', 'document_deadline_nonce')) {
            $this->handle_clear_document_deadline_form();
        }
        
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
        
        // Map frontend status to backend status
        if ($status === 'accepted') {
            $status = 'awaiting_upload';
        }
        
        if (!in_array($status, array('awaiting_upload', 'rejected'))) {
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
     * Handle supporting document download
     */
    public function handle_supporting_document_download() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hkota_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions.');
        }
        
        $submission_id = intval($_POST['submission_id']);
        
        if (!$submission_id) {
            wp_send_json_error('Invalid submission ID.');
        }
        
        // Download the document
        HKOTA_File_Handler::download_document($submission_id);
    }
    
    /**
     * Handle submission deletion
     */
    public function handle_delete_submission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hkota_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions.');
        }
        
        $submission_id = intval($_POST['submission_id']);
        
        if (!$submission_id) {
            wp_send_json_error('Invalid submission ID.');
        }
        
        // Get submission details before deletion (for file cleanup)
        $submission = HKOTA_Database::get_submission_by_id($submission_id);
        
        if (!$submission) {
            wp_send_json_error('Submission not found.');
        }
        
        // Delete supporting document files if they exist
        $files_deleted = HKOTA_File_Handler::cleanup_submission_files($submission_id);
        
        // Delete submission from database
        $result = HKOTA_Database::delete_submission($submission_id);
        
        if ($result !== false) {
            $message = 'Submission deleted successfully.';
            if (!$files_deleted) {
                $message .= ' Note: Some supporting files could not be deleted and may need manual cleanup.';
            }
            wp_send_json_success($message);
        } else {
            wp_send_json_error('Failed to delete submission from database.');
        }
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
    
    /**
     * Check if submission deadline has passed
     */
    public static function is_deadline_passed() {
        $deadline = get_option('hkota_submission_deadline');
        
        if (empty($deadline)) {
            return false; // No deadline set
        }
        
        $deadline_obj = new DateTime($deadline, new DateTimeZone('UTC'));
        $now = new DateTime('now', new DateTimeZone('UTC'));
        
        return $deadline_obj < $now;
    }
    
    /**
     * Get deadline information
     */
    public static function get_deadline_info() {
        $deadline = get_option('hkota_submission_deadline');
        
        if (empty($deadline)) {
            return array(
                'has_deadline' => false,
                'is_passed' => false,
                'message' => '',
                'time_remaining' => null
            );
        }
        
        $deadline_obj = new DateTime($deadline, new DateTimeZone('UTC'));
        $deadline_hk = clone $deadline_obj;
        $deadline_hk->setTimezone(new DateTimeZone('Asia/Hong_Kong'));
        
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $is_passed = $deadline_obj < $now;
        
        $info = array(
            'has_deadline' => true,
            'is_passed' => $is_passed,
            'deadline_utc' => $deadline_obj->format('Y-m-d H:i:s'),
            'deadline_hk' => $deadline_hk->format('Y-m-d H:i:s'),
            'deadline_formatted' => $deadline_hk->format('F j, Y \a\t g:i A'),
            'message' => get_option('hkota_deadline_message', 'The submission deadline has passed. Abstract submissions are no longer accepted.')
        );
        
        if (!$is_passed) {
            $diff = $now->diff($deadline_obj);
            $info['time_remaining'] = array(
                'days' => $diff->days,
                'hours' => $diff->h,
                'minutes' => $diff->i
            );
        }
        
        return $info;
    }
    
    /**
     * Check if document deadline has passed
     */
    public static function is_document_deadline_passed() {
        $deadline = get_option('hkota_document_deadline');
        
        if (empty($deadline)) {
            return false; // No deadline set
        }
        
        $deadline_obj = new DateTime($deadline, new DateTimeZone('UTC'));
        $now = new DateTime('now', new DateTimeZone('UTC'));
        
        return $deadline_obj < $now;
    }
    
    /**
     * Get document deadline information
     */
    public static function get_document_deadline_info() {
        $deadline = get_option('hkota_document_deadline');
        
        if (empty($deadline)) {
            return array(
                'has_deadline' => false,
                'is_passed' => false,
                'message' => '',
                'time_remaining' => null
            );
        }
        
        $deadline_obj = new DateTime($deadline, new DateTimeZone('UTC'));
        $deadline_hk = clone $deadline_obj;
        $deadline_hk->setTimezone(new DateTimeZone('Asia/Hong_Kong'));
        
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $is_passed = $deadline_obj < $now;
        
        $info = array(
            'has_deadline' => true,
            'is_passed' => $is_passed,
            'deadline_utc' => $deadline_obj->format('Y-m-d H:i:s'),
            'deadline_hk' => $deadline_hk->format('Y-m-d H:i:s'),
            'deadline_formatted' => $deadline_hk->format('F j, Y \a\t g:i A'),
            'message' => get_option('hkota_document_deadline_message', 'The deadline for supporting document uploads has passed. You can no longer upload or update your supporting documents.')
        );
        
        if (!$is_passed) {
            $diff = $now->diff($deadline_obj);
            $info['time_remaining'] = array(
                'days' => $diff->days,
                'hours' => $diff->h,
                'minutes' => $diff->i
            );
        }
        
        return $info;
    }
    
    /**
     * Handle deadline form submission (non-AJAX)
     */
    private function handle_deadline_form_submission() {
        $deadline = sanitize_text_field($_POST['submission_deadline']);
        $deadline_message = sanitize_textarea_field($_POST['deadline_message']);
        
        // Validate deadline format if provided
        if (!empty($deadline)) {
            $deadline_obj = DateTime::createFromFormat('Y-m-d\TH:i', $deadline, new DateTimeZone('Asia/Hong_Kong'));
            if (!$deadline_obj) {
                add_settings_error('hkota_deadline', 'invalid_format', 'Invalid deadline format.', 'error');
                return;
            }
            
            // Convert to UTC for storage
            $deadline_obj->setTimezone(new DateTimeZone('UTC'));
            $deadline_utc = $deadline_obj->format('Y-m-d H:i:s');
        } else {
            $deadline_utc = '';
        }
        
        // Save settings
        update_option('hkota_submission_deadline', $deadline_utc);
        update_option('hkota_deadline_message', $deadline_message);
        
        add_settings_error('hkota_deadline', 'settings_saved', 'Deadline settings saved successfully.', 'success');
    }
    
    /**
     * Handle clear deadline form submission (non-AJAX)
     */
    private function handle_clear_deadline_form() {
        // Clear deadline settings
        delete_option('hkota_submission_deadline');
        
        add_settings_error('hkota_deadline', 'deadline_cleared', 'Deadline cleared successfully. Submissions are now unlimited.', 'success');
    }
    
    /**
     * Handle document deadline form submission (non-AJAX)
     */
    private function handle_document_deadline_form_submission() {
        $deadline = sanitize_text_field($_POST['document_deadline']);
        $deadline_message = sanitize_textarea_field($_POST['document_deadline_message']);
        
        // Validate deadline format if provided
        if (!empty($deadline)) {
            $deadline_obj = DateTime::createFromFormat('Y-m-d\TH:i', $deadline, new DateTimeZone('Asia/Hong_Kong'));
            if (!$deadline_obj) {
                add_settings_error('hkota_document_deadline', 'invalid_format', 'Invalid document deadline format.', 'error');
                return;
            }
            
            // Convert to UTC for storage
            $deadline_obj->setTimezone(new DateTimeZone('UTC'));
            $deadline_utc = $deadline_obj->format('Y-m-d H:i:s');
        } else {
            $deadline_utc = '';
        }
        
        // Save settings
        update_option('hkota_document_deadline', $deadline_utc);
        update_option('hkota_document_deadline_message', $deadline_message);
        
        add_settings_error('hkota_document_deadline', 'settings_saved', 'Document deadline settings saved successfully.', 'success');
    }
    
    /**
     * Handle clear document deadline form submission (non-AJAX)
     */
    private function handle_clear_document_deadline_form() {
        // Clear document deadline settings
        delete_option('hkota_document_deadline');
        
        add_settings_error('hkota_document_deadline', 'deadline_cleared', 'Document deadline cleared successfully. Document uploads are now unlimited for accepted submissions.', 'success');
    }
}
