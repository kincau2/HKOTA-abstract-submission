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
        
        // Get submission ID from POST data
        $submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
        if (!$submission_id) {
            wp_send_json_error('Submission ID is required.');
        }
        
        // Get the specific submission
        $submission = HKOTA_Database::get_submission_by_id($submission_id);
        
        if (!$submission) {
            wp_send_json_error('Submission not found.');
        }
        
        // Verify this submission belongs to the current user
        if ($submission->user_id !== $current_user->ID) {
            wp_send_json_error('You can only upload documents for your own submissions.');
        }
        
        if (!in_array($submission->status, array('accepted', 'completed'))) {
            wp_send_json_error('File upload is only allowed for accepted submissions.');
        }
        
        // Check if document deadline has passed (if set)
        if (HKOTA_Admin::is_document_deadline_passed()) {
            wp_send_json_error('The deadline for supporting document uploads has passed.');
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
            // Update database with file information
            $result = HKOTA_Database::update_supporting_document($submission->id, $new_filename);
            
            if ($result !== false) {
                // If submission is accepted, change status to completed
                if ($submission->status === 'accepted') {
                    HKOTA_Database::update_submission_status($submission->id, 'completed');
                }
                
                wp_send_json_success('Supporting document uploaded successfully. Your submission is now complete!');
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
        // Verify nonce - check both admin and frontend nonces
        $nonce_verified = false;
        if (isset($_GET['nonce'])) {
            // Check admin nonce first
            if (wp_verify_nonce($_GET['nonce'], 'hkota_admin_nonce')) {
                $nonce_verified = true;
            }
            // Check frontend nonce if admin nonce fails
            elseif (wp_verify_nonce($_GET['nonce'], 'hkota_abstract_nonce')) {
                $nonce_verified = true;
            }
        }
        
        if (!$nonce_verified) {
            wp_die('Security check failed');
        }
        
        $submission_id = intval($_GET['submission_id']);
        
        // Check permissions - admin, reviewer, or the user who submitted
        if (!current_user_can('manage_options') && !current_user_can('hkota_reviewer')) {
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
            'can_upload' => (in_array($submission->status, array('awaiting_upload', 'completed')) && !HKOTA_Admin::is_document_deadline_passed())
        );
    }
    
    /**
     * Delete supporting document file for a submission
     */
    public static function delete_supporting_document($submission_id) {
        $submission = HKOTA_Database::get_submission_by_id($submission_id);
        
        if (!$submission || empty($submission->supporting_document)) {
            return true; // No file to delete
        }
        
        $upload_path = self::create_upload_directory();
        $file_path = $upload_path . '/' . basename($submission->supporting_document);
        
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                error_log('HKOTA: Successfully deleted supporting document: ' . $file_path);
                return true;
            } else {
                error_log('HKOTA: Failed to delete supporting document: ' . $file_path);
                return false;
            }
        }
        
        return true; // File didn't exist, consider it "deleted"
    }
    
    /**
     * Clean up all files for a submission (when deleting submission)
     */
    public static function cleanup_submission_files($submission_id) {
        return self::delete_supporting_document($submission_id);
    }
}
