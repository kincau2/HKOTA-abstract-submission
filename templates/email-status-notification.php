<?php
/**
 * Email template for status notification
 * Available variables: $submission object, $status string
 * 
 * This template routes to specific email templates based on status
 */

// Route to specific email templates based on status
switch ($status) {
    case 'accepted_oral':
        include(dirname(__FILE__) . '/email-accept-oral.php');
        return;
        
    case 'accepted_poster':
        include(dirname(__FILE__) . '/email-accept-poster.php');
        return;
        
    case 'rejected':
        include(dirname(__FILE__) . '/email-reject.php');
        return;
        
    case 'awaiting_upload':
        // Use the existing awaiting upload template below
        break;
        
    default:
        // Use the existing template for other statuses
        break;
}

// Existing template for awaiting_upload and other statuses
$status_messages = array(
    'awaiting_upload' => 'Congratulations! Your abstract has been accepted and is now awaiting supporting document upload.',
    'completed' => 'Congratulations! Your abstract submission is complete.',
    'rejected' => 'Unfortunately, your abstract has not been accepted at this time.'
);

$status_text = ($status === 'awaiting_upload') ? 'Accepted - Awaiting Upload' : (($status === 'completed') ? 'Completed' : ucfirst($status));
$status_message = isset($status_messages[$status]) ? $status_messages[$status] : 'Your submission status has been updated.';
?>
<html>
<head>
    <title>Abstract Submission Update</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .details { background-color: #f8f9fa; padding: 15px; margin: 15px 0; }
        .section { margin: 20px 0; }
        h2 { color: #0073aa; }
        h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        ul { padding-left: 20px; }
        li { margin: 5px 0; }
        .status-completed { color: #27ae60; font-weight: bold; }
        .status-awaiting_upload { color: #f39c12; font-weight: bold; }
        .status-rejected { color: #e74c3c; font-weight: bold; }
        .next-steps { background-color: #e8f5e8; padding: 15px; margin: 15px 0; border-left: 4px solid #27ae60; }
        .important { background-color: #fff3cd; padding: 15px; margin: 15px 0; border-left: 4px solid #ffc107; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Abstract Submission Update</h2>
    </div>
    
    <div class="content">
        <p>Dear <?php echo esc_html($submission->title . ' ' . $submission->surname . ', ' . $submission->given_name); ?>,</p>
        
        <p><?php echo esc_html($status_message); ?></p>
        
        <div class="section">
            <h3>Submission Details</h3>
            <div class="details">
                <ul>
                    <li><strong>Abstract Title:</strong> <?php echo esc_html($submission->abstract_title); ?></li>
                    <li><strong>Status:</strong> <span class="status-<?php echo esc_attr($status); ?>"><?php echo esc_html($status_text); ?></span></li>
                    <li><strong>Decision Date:</strong> <?php echo current_time('Y-m-d H:i:s'); ?></li>
                </ul>
            </div>
        </div>
        
        <?php if ($status === 'awaiting_upload'): ?>
        <div class="section">
            <h3>Next Steps Required</h3>
            <div class="next-steps">
                <p><strong>Important: Your submission is not yet complete.</strong></p>
                <p>To finalize your abstract submission, you must upload a supporting document. Please follow these steps:</p>
                <ol>
                    <li>Visit the submission page again at: <a href="<?php echo home_url('/abstract-submission/'); ?>">Abstract Submission Portal</a></li>
                    <li>Log in with your account credentials</li>
                    <li>Upload your supporting document (PDF format only, maximum 10MB)</li>
                    <li>Wait for final confirmation</li>
                </ol>
                
                <?php
                $document_deadline_info = HKOTA_Admin::get_document_deadline_info();
                if ($document_deadline_info['has_deadline'] && !$document_deadline_info['is_passed']):
                ?>
                <div class="important">
                    <p><strong>Document Upload Deadline:</strong> <?php echo esc_html($document_deadline_info['deadline_formatted']); ?> (Hong Kong Time)</p>
                    <p>Please ensure you upload your supporting document before this deadline.</p>
                </div>
                <?php endif; ?>
                
                <p><strong>Note:</strong> Your submission will remain incomplete until the supporting document is uploaded successfully.</p>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($submission->admin_notes): ?>
        <div class="section">
            <h3>Additional Notes</h3>
            <div class="details">
                <?php echo nl2br(esc_html($submission->admin_notes)); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <p>Thank you for your interest in HKOTA.</p>
        
        <p>Best regards,<br>
        HKOTA Team</p>
    </div>
</body>
</html>
