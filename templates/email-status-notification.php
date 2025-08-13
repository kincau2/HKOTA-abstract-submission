<?php
/**
 * Email template for status notification
 * Available variables: $submission object, $status string
 */
$status_text = ($status === 'accepted') ? 'accepted' : 'rejected';
$status_message = ($status === 'accepted') 
    ? 'Congratulations! Your abstract has been accepted.' 
    : 'Unfortunately, your abstract has not been accepted at this time.';
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
        .status-accepted { color: #27ae60; font-weight: bold; }
        .status-rejected { color: #e74c3c; font-weight: bold; }
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
                    <li><strong>Status:</strong> <span class="status-<?php echo esc_attr($status); ?>"><?php echo esc_html(ucfirst($status_text)); ?></span></li>
                    <li><strong>Decision Date:</strong> <?php echo current_time('Y-m-d H:i:s'); ?></li>
                </ul>
            </div>
        </div>
        
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
