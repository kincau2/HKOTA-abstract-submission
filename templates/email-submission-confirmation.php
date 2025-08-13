<?php
/**
 * Email template for submission confirmation
 * Available variables: $data array with all form data
 */
?>
<html>
<head>
    <title>Abstract Submission Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .details { background-color: #f8f9fa; padding: 15px; margin: 15px 0; }
        .section { margin: 20px 0; }
        h2 { color: #0073aa; }
        h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        h4 { color: #555; margin-top: 15px; }
        ul { padding-left: 20px; }
        li { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Abstract Submission Confirmation</h2>
    </div>
    
    <div class="content">
        <p>Dear <?php echo esc_html($data['title'] . ' ' . $data['surname'] . ', ' . $data['given_name']); ?>,</p>
        
        <p>Thank you for submitting your abstract to HKOTA. We have successfully received your submission.</p>
        
        <div class="section">
            <h3>Submission Details</h3>
            <div class="details">
                <ul>
                    <li><strong>Name:</strong> <?php echo esc_html($data['title'] . ' ' . $data['surname'] . ', ' . $data['given_name']); ?></li>
                    <li><strong>Contact Email:</strong> <?php echo esc_html($data['contact_email']); ?></li>
                    <li><strong>Contact Number:</strong> <?php echo esc_html($data['contact_number']); ?></li>
                    <li><strong>Organization:</strong> <?php echo esc_html($data['organization']); ?></li>
                    <li><strong>Theme:</strong> <?php echo esc_html($data['theme']); ?></li>
                    <li><strong>Presentation Preference:</strong> <?php echo esc_html($data['presentation_preference']); ?></li>
                    <li><strong>Abstract Title:</strong> <?php echo esc_html($data['abstract_title']); ?></li>
                    <li><strong>Submission Date:</strong> <?php echo current_time('Y-m-d H:i:s'); ?></li>
                </ul>
            </div>
        </div>
        
        <div class="section">
            <h3>Abstract Content</h3>
            
            <h4>Authors:</h4>
            <div class="details"><?php echo nl2br(esc_html($data['authors'])); ?></div>
            
            <h4>Affiliations:</h4>
            <div class="details"><?php echo nl2br(esc_html($data['affiliations'])); ?></div>
            
            <h4>Background:</h4>
            <div class="details"><?php echo nl2br(esc_html($data['background'])); ?></div>
            
            <h4>Methods:</h4>
            <div class="details"><?php echo nl2br(esc_html($data['methods'])); ?></div>
            
            <h4>Results and Findings:</h4>
            <div class="details"><?php echo nl2br(esc_html($data['results'])); ?></div>
            
            <h4>Conclusion:</h4>
            <div class="details"><?php echo nl2br(esc_html($data['conclusion'])); ?></div>
            
            <h4>Keywords:</h4>
            <div class="details"><?php echo esc_html($data['keywords']); ?></div>
        </div>
        
        <p>Your submission is currently under review. You will be notified once a decision has been made.</p>
        
        <p>If you need to make any changes to your submission before the deadline, you can log in to your account and edit your submission.</p>
        
        <p>Best regards,<br>
        HKOTA Team</p>
    </div>
</body>
</html>
