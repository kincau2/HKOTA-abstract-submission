<?php

class HKOTA_Database {
    
    public function __construct() {
        $this->create_tables();
    }
    
    public static function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        $ratings_table = $wpdb->prefix . 'hkota_reviewer_ratings';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Main submissions table
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
        
        // Reviewer ratings table
        $ratings_sql = "CREATE TABLE $ratings_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            submission_id int(11) NOT NULL,
            reviewer_user_id bigint(20) NOT NULL,
            innovation_rating tinyint(1) NOT NULL,
            scientific_merit_rating tinyint(1) NOT NULL,
            knowledge_contribution_rating tinyint(1) NOT NULL,
            clinical_application_rating tinyint(1) NOT NULL,
            reviewer_comments longtext DEFAULT '',
            total_score decimal(5,2) NOT NULL,
            rating_date datetime DEFAULT CURRENT_TIMESTAMP,
            last_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_reviewer_submission (submission_id, reviewer_user_id),
            KEY submission_id (submission_id),
            KEY reviewer_user_id (reviewer_user_id),
            FOREIGN KEY (submission_id) REFERENCES $table_name(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta($ratings_sql);
    }
    
    public static function insert_submission($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        // Check if this is an update (submission_id provided) or new submission
        if (isset($data['submission_id']) && $data['submission_id']) {
            // Update existing submission
            $submission_id = intval($data['submission_id']);
            
            // Get the existing submission details
            $existing_submission = $wpdb->get_row($wpdb->prepare(
                "SELECT submission_number, presentation_preference FROM $table_name WHERE id = %d AND user_id = %d",
                $submission_id,
                $data['user_id']
            ));
            
            if (!$existing_submission) {
                return false; // Submission not found or doesn't belong to user
            }
            
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
                    'last_modified' => current_time('mysql')
                ),
                array('id' => $submission_id, 'user_id' => $data['user_id']),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
                array('%d', '%d')
            );
        } else {
            // Insert new submission
            $submission_number = self::generate_submission_number($data['presentation_preference']);
            
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
    
    public static function get_user_submissions($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY submission_date DESC",
            $user_id
        ));
    }
    
    public static function get_user_submission($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY submission_date DESC LIMIT 1",
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
    
    public static function delete_user_submission($submission_id, $user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        return $wpdb->delete(
            $table_name,
            array('id' => $submission_id, 'user_id' => $user_id),
            array('%d', '%d')
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
    
    /**
     * Insert or update a reviewer rating
     */
    public static function save_reviewer_rating($data) {
        global $wpdb;
        
        $ratings_table = $wpdb->prefix . 'hkota_reviewer_ratings';
        
        // Calculate total score (weighted)
        $total_score = (
            ($data['innovation_rating'] * 3) +
            ($data['scientific_merit_rating'] * 5) +
            ($data['knowledge_contribution_rating'] * 6) +
            ($data['clinical_application_rating'] * 6)
        );
        
        // Convert to percentage (max possible score is 20 * 5 = 100)
        $total_score_percentage = ($total_score / 100) * 100;
        
        $rating_data = array(
            'submission_id' => $data['submission_id'],
            'reviewer_user_id' => $data['reviewer_user_id'],
            'innovation_rating' => $data['innovation_rating'],
            'scientific_merit_rating' => $data['scientific_merit_rating'],
            'knowledge_contribution_rating' => $data['knowledge_contribution_rating'],
            'clinical_application_rating' => $data['clinical_application_rating'],
            'reviewer_comments' => $data['reviewer_comments'],
            'total_score' => $total_score_percentage,
            'last_modified' => current_time('mysql')
        );
        
        // Check if rating already exists
        $existing_rating = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $ratings_table WHERE submission_id = %d AND reviewer_user_id = %d",
            $data['submission_id'],
            $data['reviewer_user_id']
        ));
        
        if ($existing_rating) {
            // Update existing rating
            return $wpdb->update(
                $ratings_table,
                $rating_data,
                array(
                    'submission_id' => $data['submission_id'],
                    'reviewer_user_id' => $data['reviewer_user_id']
                ),
                array('%d', '%d', '%d', '%d', '%d', '%d', '%s', '%.2f', '%s'),
                array('%d', '%d')
            );
        } else {
            // Insert new rating
            $rating_data['rating_date'] = current_time('mysql');
            return $wpdb->insert(
                $ratings_table,
                $rating_data,
                array('%d', '%d', '%d', '%d', '%d', '%d', '%s', '%.2f', '%s', '%s')
            );
        }
    }
    
    /**
     * Get reviewer rating for a specific submission and reviewer
     */
    public static function get_reviewer_rating($reviewer_user_id, $submission_id) {
        global $wpdb;
        
        $ratings_table = $wpdb->prefix . 'hkota_reviewer_ratings';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $ratings_table WHERE submission_id = %d AND reviewer_user_id = %d",
            $submission_id,
            $reviewer_user_id
        ));
    }
    
    /**
     * Get all ratings for a specific submission
     */
    public static function get_submission_ratings($submission_id) {
        global $wpdb;
        
        $ratings_table = $wpdb->prefix . 'hkota_reviewer_ratings';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT r.*, 
                    u.display_name as reviewer_name,
                    u.user_email as reviewer_email,
                    um_first.meta_value as reviewer_first_name,
                    um_last.meta_value as reviewer_last_name
             FROM $ratings_table r 
             LEFT JOIN {$wpdb->users} u ON r.reviewer_user_id = u.ID 
             LEFT JOIN {$wpdb->usermeta} um_first ON u.ID = um_first.user_id AND um_first.meta_key = 'first_name'
             LEFT JOIN {$wpdb->usermeta} um_last ON u.ID = um_last.user_id AND um_last.meta_key = 'last_name'
             WHERE r.submission_id = %d 
             ORDER BY r.rating_date DESC",
            $submission_id
        ));
    }
    
    /**
     * Get all ratings by a specific reviewer
     */
    public static function get_reviewer_ratings($reviewer_user_id) {
        global $wpdb;
        
        $ratings_table = $wpdb->prefix . 'hkota_reviewer_ratings';
        $submissions_table = $wpdb->prefix . 'hkota_abstract_submissions';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT r.*, s.abstract_title, s.submission_number 
             FROM $ratings_table r 
             LEFT JOIN $submissions_table s ON r.submission_id = s.id 
             WHERE r.reviewer_user_id = %d 
             ORDER BY r.rating_date DESC",
            $reviewer_user_id
        ));
    }
    
    /**
     * Get average rating for a submission
     */
    public static function get_submission_average_rating($submission_id) {
        global $wpdb;
        
        $ratings_table = $wpdb->prefix . 'hkota_reviewer_ratings';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_ratings,
                AVG(total_score) as average_score,
                AVG(innovation_rating) as avg_innovation,
                AVG(scientific_merit_rating) as avg_scientific_merit,
                AVG(knowledge_contribution_rating) as avg_knowledge_contribution,
                AVG(clinical_application_rating) as avg_clinical_application
             FROM $ratings_table 
             WHERE submission_id = %d",
            $submission_id
        ));
    }
    
    /**
     * Delete a reviewer rating
     */
    public static function delete_reviewer_rating($submission_id, $reviewer_user_id) {
        global $wpdb;
        
        $ratings_table = $wpdb->prefix . 'hkota_reviewer_ratings';
        
        return $wpdb->delete(
            $ratings_table,
            array(
                'submission_id' => $submission_id,
                'reviewer_user_id' => $reviewer_user_id
            ),
            array('%d', '%d')
        );
    }
}
