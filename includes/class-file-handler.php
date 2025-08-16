<?php

class HKOTA_File_Handler {
    
    private static $upload_dir = 'hkota-submissions';
    
    public function __construct() {
        add_action('wp_ajax_upload_supporting_document', array($this, 'handle_file_upload'));
        add_action('wp_ajax_download_supporting_document', array($this, 'handle_file_download'));
        add_action('wp_ajax_nopriv_upload_supporting_document', array($this, 'handle_file_upload'));
    }
    
    /**
     * Create upload directory and .htaccess file
     */
    public static function create_upload_directory() {
        $wp_upload_dir = wp_upload_dir();
        $upload_path = $wp_upload_dir['basedir'] . '/' . self::$upload_dir;
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_path)) {
            wp_mkdir_p($upload_path);
        }
        
        // Create .htaccess file to protect direct access
        $htaccess_file = $upload_path . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "Order deny,allow\nDeny from all\n";
            file_put_contents($htaccess_file, $htaccess_content);
        }
        
        return $upload_path;
    }
    
    /**
     * Handle file upload via AJAX
     */
    public function handle_file_upload() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['file_upload_nonce'], 'hkota_file_upload_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to upload files.');
        }
        
        $current_user = wp_get_current_user();
        $submission = HKOTA_Database::get_user_submission($current_user->ID);
        
        if (!$submission) {
            wp_send_json_error('No submission found for this user.');
        }
        
        if ($submission->status !== 'accepted') {
            wp_send_json_error('File upload is only allowed for accepted submissions.');
        }
        
        // Check if deadline has passed
        if (!HKOTA_Admin::is_deadline_passed()) {
            wp_send_json_error('File upload is only available after the submission deadline.');
        }
        
        // Validate file
        if (!isset($_FILES['supporting_document']) || $_FILES['supporting_document']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('Please select a valid PDF file.');
        }
        
        $file = $_FILES['supporting_document'];
        
        // Check file type (PDF only)
        $allowed_types = array('application/pdf');
        $file_info = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
        
        if (!in_array($file_info['type'], $allowed_types)) {
            wp_send_json_error('Only PDF files are allowed.');
        }
        
        // Check file size (max 10MB)
        $max_size = 10 * 1024 * 1024; // 10MB in bytes
        if ($file['size'] > $max_size) {
            wp_send_json_error('File size must be less than 10MB.');
        }
        
        // Create upload directory
        $upload_path = self::create_upload_directory();
        
        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'submission_' . $submission->id . '_' . sanitize_file_name($submission->surname . '_' . $submission->given_name) . '_' . time() . '.' . $file_extension;
        $file_path = $upload_path . '/' . $new_filename;
        
        // Remove old file if exists
        if (!empty($submission->supporting_document)) {
            $old_file_path = $upload_path . '/' . basename($submission->supporting_document);
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            // Update database
            $result = HKOTA_Database::update_supporting_document($submission->id, $new_filename);
            
            if ($result !== false) {
                wp_send_json_success('Supporting document uploaded successfully.');
            } else {
                // Remove uploaded file if database update fails
                unlink($file_path);
                wp_send_json_error('Failed to save file information.');
            }
        } else {
            wp_send_json_error('Failed to upload file.');
        }
    }
    
    /**
     * Handle file download
     */
    public function handle_file_download() {
        // Verify nonce
        if (!wp_verify_nonce($_GET['nonce'], 'hkota_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        $submission_id = intval($_GET['submission_id']);
        
        // Check permissions - admin or the user who submitted
        if (!current_user_can('manage_options')) {
            if (!is_user_logged_in()) {
                wp_die('Access denied');
            }
            
            $current_user = wp_get_current_user();
            $submission = HKOTA_Database::get_submission_by_id($submission_id);
            
            if (!$submission || $submission->user_id != $current_user->ID) {
                wp_die('Access denied');
            }
        } else {
            $submission = HKOTA_Database::get_submission_by_id($submission_id);
        }
        
        if (!$submission || empty($submission->supporting_document)) {
            wp_die('File not found');
        }
        
        $upload_path = self::create_upload_directory();
        $file_path = $upload_path . '/' . basename($submission->supporting_document);
        
        if (!file_exists($file_path)) {
            wp_die('File not found');
        }
        
        // Send file
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($submission->supporting_document) . '"');
        header('Content-Length: ' . filesize($file_path));
        
        readfile($file_path);
        exit;
    }
    
    /**
     * Get file upload status for a submission
     */
    public static function get_file_status($submission_id) {
        $submission = HKOTA_Database::get_submission_by_id($submission_id);
        
        if (!$submission) {
            return false;
        }
        
        return array(
            'has_file' => !empty($submission->supporting_document),
            'filename' => $submission->supporting_document,
            'can_upload' => ($submission->status === 'accepted' && HKOTA_Admin::is_deadline_passed())
        );
    }
}
