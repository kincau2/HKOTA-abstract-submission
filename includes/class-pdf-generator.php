<?php

require_once HKOTA_ABSTRACT_PLUGIN_PATH . 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * PDF Generator Class using DomPDF
 * 
 * This class handles PDF generation for abstract submissions using DomPDF library.
 * DomPDF converts HTML and CSS to PDF format, providing professional-quality output.
 * 
 * Features:
 * - Generates actual PDF files (not HTML)
 * - Supports CSS styling optimized for PDF output
 * - Handles automatic downloads
 * - Can generate PDF strings for email attachments
 * 
 * Requirements:
 * - DomPDF library (installed via Composer)
 * - PHP 7.2+ (recommended 8.1+)
 */
class HKOTA_PDF_Generator {
    
    public static function generate_submission_pdf($submission) {
        // Generate HTML content using template
        $html = HKOTA_Template_Helper::get_pdf_template('submission', array('submission' => $submission));
        
        // Configure DomPDF options
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        
        // Initialize DomPDF
        $dompdf = new Dompdf($options);
        
        // Load HTML content
        $dompdf->loadHtml($html);
        
        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');
        
        // Render PDF
        $dompdf->render();
        
        // Generate filename
        $filename = 'abstract_submission_' . $submission->id . '_' . sanitize_title($submission->surname . '_' . $submission->given_name) . '.pdf';
        
        // Output PDF with proper headers
        $dompdf->stream($filename, array(
            'Attachment' => true,  // Force download
            'compress' => true
        ));
        
        exit;
    }
    
    /**
     * Generate PDF and return as string (for email attachments)
     */
    public static function generate_submission_pdf_string($submission) {
        // Generate HTML content using template
        $html = HKOTA_Template_Helper::get_pdf_template('submission', array('submission' => $submission));
        
        // Configure DomPDF options
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        
        // Initialize DomPDF
        $dompdf = new Dompdf($options);
        
        // Load HTML content
        $dompdf->loadHtml($html);
        
        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');
        
        // Render PDF
        $dompdf->render();
        
        // Return PDF as string
        return $dompdf->output();
    }
}
