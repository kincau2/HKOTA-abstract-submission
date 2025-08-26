<?php
/**
 * Email template for submission rejection
 * Available variables: $submission object with all submission data
 */
?>
<html>
<head>
    <title>Hong Kong Occupational Therapy Conference 2025 - Abstract Submission Status</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .content { padding: 20px; }
        .details { background-color: #f8f9fa; padding: 15px; margin: 15px 0; }
        .section { margin: 20px 0; }
        h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
    </style>
</head>
<body>
    <div class="content">
        <p><strong>Subject: Hong Kong Occupational Therapy Conference 2025 - Abstract Submission Status</strong></p>
        
        <p>Dear <?php echo esc_html($submission->title . ' ' . $submission->surname . ', ' . $submission->given_name); ?>,</p>
        
        <p>Thank you for submitting your abstract titled "<?php echo esc_html($submission->abstract_title); ?>" for consideration. We appreciate your interest in the Hong Kong Occupational Therapy Conference 2025.</p>
        
        <p>After careful review, we regret to inform you that your abstract has not been accepted for this year's program. We received a large number of submissions, and the selection process was highly competitive.</p>
        
        <p>We encourage you to consider submitting to future events and wish you the best in your ongoing evidence-based practice.</p>
        
        <p>Thank you for your understanding.</p>
        
        <p>Best regards,<br>
        Scientific Committee,<br>
        Hong Kong Occupational Therapy Conference 2025</p>
    </div>
</body>
</html>