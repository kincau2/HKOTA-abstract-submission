<?php
/**
 * Email template for admin notification
 * Available variables: $data array with all form data
 */
?>
<html>
<head>
    <title>New Abstract Submission</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background-color: #0073aa; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .details { background-color: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #0073aa; }
        .section { margin: 20px 0; }
        h2 { margin: 0; }
        h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        h4 { color: #555; margin-top: 15px; }
        ul { padding-left: 20px; }
        li { margin: 5px 0; }
        .button { 
            background-color: #0073aa; 
            color: white; 
            padding: 10px 20px; 
            text-decoration: none; 
            border-radius: 4px; 
            display: inline-block; 
            margin-top: 15px; 
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>New Abstract Submission Received</h2>
    </div>
    
    <div class="content">
        <p>A new abstract submission has been received and requires your review.</p>
        
        <div class="section">
            <h3>Applicant Information</h3>
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
        
        <p>Please log in to the admin panel to review and manage this submission.</p>
        
        <p><a href="<?php echo admin_url('admin.php?page=hkota-abstract-submissions'); ?>" class="button">View Submissions</a></p>
    </div>
</body>
</html>
