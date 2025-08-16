<?php

class HKOTA_Database {
    
    public function __construct() {
        $this->create_tables();
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
            submission_date datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(50) DEFAULT 'pending',
            admin_notes longtext DEFAULT '',
            supporting_document varchar(500) DEFAULT '',
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
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
                    'submission_date' => current_time('mysql')
                ),
                array('user_id' => $data['user_id']),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
                array('%d')
            );
        } else {
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
                    'submission_date' => current_time('mysql'),
                    'status' => 'pending'
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
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
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY submission_date DESC");
    }
    
    public static function update_submission_status($submission_id, $status, $admin_notes = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'hkota_abstract_submissions';
        
        return $wpdb->update(
            $table_name,
            array(
                'status' => $status,
                'admin_notes' => $admin_notes
            ),
            array('id' => $submission_id),
            array('%s', '%s'),
            array('%d')
        );
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
            array('supporting_document' => $file_path),
            array('id' => $submission_id),
            array('%s'),
            array('%d')
        );
    }
}
