<?php

class HKOTA_Email {
    
    public static function send_submission_confirmation($data) {
        $to = $data['contact_email'];
        $subject = 'Hong Kong Occupational Therapy Conference 2025 - Abstract Submission Confirmation';
        
        $message = HKOTA_Template_Helper::get_email_template('submission-confirmation', array('data' => $data));
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($to, $subject, $message, $headers);
        
        // Also send notification to admin
        self::send_admin_notification($data);
    }
    
    public static function send_status_notification($submission, $status) {
        $to = $submission->contact_email;
        
        // Determine email type based on status and presentation type
        if ($status === 'awaiting_upload') {
            if ($submission->presentation_preference === 'Oral Presentation') {
                $subject = 'Hong Kong Occupational Therapy Conference 2025 – Acceptance of Oral Presentation';
                $template = 'accept-oral';
            } elseif ($submission->presentation_preference === 'E-poster presentation') {
                $subject = 'Hong Kong Occupational Therapy Conference 2025 – Acceptance of Poster Presentation';
                $template = 'accept-poster';
            } else {
                // Fallback for general acceptance
                $subject = 'Hong Kong Occupational Therapy Conference 2025 - Submission Accepted';
                $template = 'status-notification';
            }
        } elseif ($status === 'rejected') {
            $subject = 'Hong Kong Occupational Therapy Conference 2025 - Abstract Submission Status';
            $template = 'reject';
        } else {
            // Fallback for other statuses
            $subject = 'Hong Kong Occupational Therapy Conference 2025 - Submission Update';
            $template = 'status-notification';
        }
        
        $message = HKOTA_Template_Helper::get_email_template($template, array(
            'submission' => $submission,
            'status' => $status
        ));
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($to, $subject, $message, $headers);
    }
    
    public static function send_admin_notification($data) {
        $admin_email = get_option('admin_email');
        $subject = 'New Abstract Submission - HKOTA';
        
        $message = HKOTA_Template_Helper::get_email_template('admin-notification', array('data' => $data));
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        wp_mail($admin_email, $subject, $message, $headers);
    }
    
    /**
     * Send email with PDF attachment
     */
    public static function send_email_with_pdf($to, $subject, $message, $submission) {
        // Generate PDF content
        $pdf_content = HKOTA_PDF_Generator::generate_submission_pdf_string($submission);
        
        // Create temporary file for attachment
        $temp_file = wp_tempnam('hkota_pdf_');
        file_put_contents($temp_file, $pdf_content);
        
        // Prepare email
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $filename = 'abstract_submission_' . $submission->id . '_' . sanitize_title($submission->surname . '_' . $submission->given_name) . '.pdf';
        
        // Send email with attachment
        $result = wp_mail($to, $subject, $message, $headers, array($temp_file));
        
        // Clean up temporary file
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
        
        return $result;
    }
}
