<?php

class HKOTA_Database {
    
    public function __construct() {
        $this->create_tables();
        $this->upgrade_tables();
    }
    
    public static function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            title varchar(20) NOT NULL,
            surname varchar(255) NOT NULL,
            given_name varchar(255) NOT NULL,
            contact_number varchar(50) NOT NULL,
            contact_email varchar(255) NOT NULL,
            organization varchar(500) NOT NULL,
            theme varchar(500) NOT NULL,
            presentation_preference varchar(100) NOT NULL,
            abstract_title varchar(500) NOT NULL,
            authors longtext NOT NULL,
            affiliations longtext NOT NULL,
            background longtext NOT NULL,
            methods longtext NOT NULL,
            results longtext NOT NULL,
            conclusion longtext NOT NULL,
            keywords varchar(500) NOT NULL,
            submission_number varchar(20) DEFAULT NULL,
            submission_date datetime DEFAULT CURRENT_TIMESTAMP,
            last_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status varchar(50) DEFAULT 'pending',
            admin_notes longtext DEFAULT '',
            supporting_document varchar(500) DEFAULT '',
            PRIMARY KEY (id),
            KEY user_id (user_id),
            UNIQUE KEY submission_number (submission_number)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public static function upgrade_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        // Check if last_modified column exists
        $last_modified_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'last_modified'",
            DB_NAME,
            $table_name
        ));
        
        // Add last_modified column if it doesn't exist
        if (empty($last_modified_exists)) {
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN last_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER submission_date");
            
            // Initialize last_modified with submission_date for existing records
            $wpdb->query("UPDATE $table_name SET last_modified = submission_date WHERE last_modified IS NULL");
        }
        
        // Check if submission_number column exists
        $submission_number_exists = $wpdb->get_results($wpdb->prepare(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'submission_number'",
            DB_NAME,
            $table_name
        ));
        
        // Add submission_number column if it doesn't exist
        if (empty($submission_number_exists)) {
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN submission_number varchar(20) DEFAULT NULL AFTER keywords");
            $wpdb->query("ALTER TABLE $table_name ADD UNIQUE KEY submission_number (submission_number)");
            
            // Generate submission numbers for existing records
            self::generate_missing_submission_numbers();
        }
    }
    
    public static function insert_submission($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        // Check if user already has a submission
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE user_id = %d",
            $data['user_id']
        ));
        
        if ($existing) {
            // Get the existing submission details
            $existing_submission = $wpdb->get_row($wpdb->prepare(
                "SELECT submission_number, presentation_preference FROM $table_name WHERE user_id = %d",
                $data['user_id']
            ));
            
            // Generate submission number if it doesn't exist
            $submission_number = $existing_submission->submission_number;
            if (!$submission_number) {
                $submission_number = self::generate_submission_number($data['presentation_preference']);
            }
            // If presentation preference changed, generate new number
            elseif ($existing_submission->presentation_preference !== $data['presentation_preference']) {
                $submission_number = self::generate_submission_number($data['presentation_preference']);
            }
            
            // Update existing submission
            return $wpdb->update(
                $table_name,
                array(
                    'title' => $data['title'],
                    'surname' => $data['surname'],
                    'given_name' => $data['given_name'],
                    'contact_number' => $data['contact_number'],
                    'contact_email' => $data['contact_email'],
                    'organization' => $data['organization'],
                    'theme' => $data['theme'],
                    'presentation_preference' => $data['presentation_preference'],
                    'abstract_title' => $data['abstract_title'],
                    'authors' => $data['authors'],
                    'affiliations' => $data['affiliations'],
                    'background' => $data['background'],
                    'methods' => $data['methods'],
                    'results' => $data['results'],
                    'conclusion' => $data['conclusion'],
                    'keywords' => $data['keywords'],
                    'submission_number' => $submission_number,
                    'submission_date' => current_time('mysql'),
                    'last_modified' => current_time('mysql')
                ),
                array('user_id' => $data['user_id']),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
                array('%d')
            );
        } else {
            // Generate submission number for new submission
            $submission_number = self::generate_submission_number($data['presentation_preference']);
            
            // Insert new submission
            return $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $data['user_id'],
                    'title' => $data['title'],
                    'surname' => $data['surname'],
                    'given_name' => $data['given_name'],
                    'contact_number' => $data['contact_number'],
                    'contact_email' => $data['contact_email'],
                    'organization' => $data['organization'],
                    'theme' => $data['theme'],
                    'presentation_preference' => $data['presentation_preference'],
                    'abstract_title' => $data['abstract_title'],
                    'authors' => $data['authors'],
                    'affiliations' => $data['affiliations'],
                    'background' => $data['background'],
                    'methods' => $data['methods'],
                    'results' => $data['results'],
                    'conclusion' => $data['conclusion'],
                    'keywords' => $data['keywords'],
                    'submission_number' => $submission_number,
                    'submission_date' => current_time('mysql'),
                    'status' => 'pending'
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );
        }
    }
    
    public static function get_user_submission($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d",
            $user_id
        ));
    }
    
    public static function get_all_submissions() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY last_modified DESC, submission_date DESC");
    }
    
    public static function update_submission_status($submission_id, $status, $admin_notes = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        return $wpdb->update(
            $table_name,
            array(
                'status' => $status,
                'admin_notes' => $admin_notes,
                'last_modified' => current_time('mysql')
            ),
            array('id' => $submission_id),
            array('%s', '%s', '%s'),
            array('%d')
        );
    }
    
    public static function accept_submission_with_type($submission_id, $presentation_type) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        // Start transaction for data consistency
        $wpdb->query('START TRANSACTION');
        
        try {
            // Get current submission data
            $submission = self::get_submission_by_id($submission_id);
            if (!$submission) {
                throw new Exception('Submission not found');
            }
            
            // Determine the new type character for submission number
            $type_char = ($presentation_type === 'Oral Presentation') ? 'O' : 'P';
            $current_year = date('y');
            
            // Check if submission number needs to be updated
            $current_submission_number = $submission->submission_number;
            $needs_number_update = false;
            
            if ($current_submission_number) {
                // Parse current submission number format: YY-T-NNN
                $parts = explode('-', $current_submission_number);
                if (count($parts) === 3 && $parts[1] !== $type_char) {
                    $needs_number_update = true;
                }
            } else {
                $needs_number_update = true;
            }
            
            $new_submission_number = $current_submission_number;
            
            if ($needs_number_update) {
                // Generate new submission number with correct type
                $new_submission_number = self::generate_submission_number($presentation_type);
                if (!$new_submission_number) {
                    throw new Exception('Failed to generate submission number');
                }
            }
            
            // Update submission with new presentation type, status, and submission number
            $update_result = $wpdb->update(
                $table_name,
                array(
                    'presentation_preference' => $presentation_type,
                    'status' => 'awaiting_upload',
                    'submission_number' => $new_submission_number,
                    'last_modified' => current_time('mysql')
                ),
                array('id' => $submission_id),
                array('%s', '%s', '%s', '%s'),
                array('%d')
            );
            
            if ($update_result === false) {
                throw new Exception('Failed to update submission');
            }
            
            // Commit transaction
            $wpdb->query('COMMIT');
            
            return true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $wpdb->query('ROLLBACK');
            error_log('Error in accept_submission_with_type: ' . $e->getMessage());
            return false;
        }
    }
    
    public static function get_submission_by_id($submission_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $submission_id
        ));
    }
    
    public static function update_supporting_document($submission_id, $file_path) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        return $wpdb->update(
            $table_name,
            array(
                'supporting_document' => $file_path,
                'last_modified' => current_time('mysql')
            ),
            array('id' => $submission_id),
            array('%s', '%s'),
            array('%d')
        );
    }
    
    public static function delete_submission($submission_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        return $wpdb->delete(
            $table_name,
            array('id' => $submission_id),
            array('%d')
        );
    }
    
    /**
     * Generate a unique submission number with format: YY-T-NNN
     * YY = Year (e.g., 25 for 2025)
     * T = Type (O for Oral, P for E-poster)
     * NNN = Sequential number (001, 002, etc.)
     */
    public static function generate_submission_number($presentation_preference) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        $current_year = date('y'); // 2-digit year
        
        // Determine type code
        $type_code = ($presentation_preference === 'Oral Presentation') ? 'O' : 'P';
        
        // Use database transaction to prevent race conditions
        $wpdb->query('START TRANSACTION');
        
        try {
            // Get the highest sequence number for this year and type
            $prefix = $current_year . '-' . $type_code . '-';
            
            $max_number = $wpdb->get_var($wpdb->prepare(
                "SELECT MAX(CAST(SUBSTRING(submission_number, %d) AS UNSIGNED)) 
                 FROM $table_name 
                 WHERE submission_number LIKE %s",
                strlen($prefix) + 1,
                $prefix . '%'
            ));
            
            // Generate next number
            $next_number = $max_number ? $max_number + 1 : 1;
            $submission_number = $prefix . sprintf('%03d', $next_number);
            
            // Verify uniqueness (extra safety check)
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE submission_number = %s",
                $submission_number
            ));
            
            if ($exists) {
                // If somehow exists, try next number
                $next_number++;
                $submission_number = $prefix . sprintf('%03d', $next_number);
            }
            
            $wpdb->query('COMMIT');
            return $submission_number;
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            throw $e;
        }
    }
    
    /**
     * Generate submission numbers for existing records that don't have them
     */
    public static function generate_missing_submission_numbers() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        // Get all submissions without submission numbers, ordered by submission date
        $submissions = $wpdb->get_results(
            "SELECT id, presentation_preference, submission_date 
             FROM $table_name 
             WHERE submission_number IS NULL 
             ORDER BY submission_date ASC"
        );
        
        foreach ($submissions as $submission) {
            $submission_number = self::generate_submission_number($submission->presentation_preference);
            
            $wpdb->update(
                $table_name,
                array('submission_number' => $submission_number),
                array('id' => $submission->id),
                array('%s'),
                array('%d')
            );
        }
    }
}
