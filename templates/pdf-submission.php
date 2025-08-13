<?php
/**
 * PDF template for submission details
 * Available variables: $submission object
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Abstract Submission - <?php echo esc_html($submission->surname . ', ' . $submission->given_name); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            color: #0073aa;
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
            font-size: 11px;
        }
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section h3 {
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
            margin-bottom: 12px;
            font-size: 14px;
        }
        .section h4 {
            color: #555;
            margin: 12px 0 8px 0;
            font-size: 12px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            width: 25%;
            background-color: #f9f9f9;
        }
        .status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .status.pending { background-color: #f39c12; }
        .status.accepted { background-color: #27ae60; }
        .status.rejected { background-color: #e74c3c; }
        .abstract-content {
            background-color: #f9f9f9;
            padding: 10px;
            border-left: 3px solid #0073aa;
            margin: 8px 0;
            font-size: 11px;
            line-height: 1.4;
        }
        .keywords {
            background-color: #e8f4f8;
            padding: 8px;
            border-radius: 3px;
            font-style: italic;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>HKOTA Abstract Submission</h1>
        <p>Submission ID: #<?php echo esc_html($submission->id); ?></p>
        <p>Generated on: <?php echo date('F j, Y g:i A'); ?></p>
    </div>
    
    <div class="section">
        <h3>Applicant Information</h3>
        <table class="info-table">
            <tr>
                <td class="info-label">Title:</td>
                <td><?php echo esc_html($submission->title); ?></td>
            </tr>
            <tr>
                <td class="info-label">Name:</td>
                <td><?php echo esc_html($submission->surname . ', ' . $submission->given_name); ?></td>
            </tr>
            <tr>
                <td class="info-label">Contact Email:</td>
                <td><?php echo esc_html($submission->contact_email); ?></td>
            </tr>
            <tr>
                <td class="info-label">Contact Number:</td>
                <td><?php echo esc_html($submission->contact_number); ?></td>
            </tr>
            <tr>
                <td class="info-label">Organization:</td>
                <td><?php echo esc_html($submission->organization); ?></td>
            </tr>
            <tr>
                <td class="info-label">Theme:</td>
                <td><?php echo esc_html($submission->theme); ?></td>
            </tr>
            <tr>
                <td class="info-label">Presentation:</td>
                <td><?php echo esc_html($submission->presentation_preference); ?></td>
            </tr>
            <tr>
                <td class="info-label">Submission Date:</td>
                <td><?php echo date('F j, Y g:i A', strtotime($submission->submission_date)); ?></td>
            </tr>
            <tr>
                <td class="info-label">Status:</td>
                <td><span class="status <?php echo esc_attr($submission->status); ?>"><?php echo esc_html(ucfirst($submission->status)); ?></span></td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <h3>Abstract Details</h3>
        
        <h4>Title:</h4>
        <div class="abstract-content"><?php echo esc_html($submission->abstract_title); ?></div>
        
        <h4>Authors:</h4>
        <div class="abstract-content"><?php echo nl2br(esc_html($submission->authors)); ?></div>
        
        <h4>Affiliations:</h4>
        <div class="abstract-content"><?php echo nl2br(esc_html($submission->affiliations)); ?></div>
        
        <h4>Background:</h4>
        <div class="abstract-content"><?php echo nl2br(esc_html($submission->background)); ?></div>
        
        <h4>Methods:</h4>
        <div class="abstract-content"><?php echo nl2br(esc_html($submission->methods)); ?></div>
        
        <h4>Results and Findings:</h4>
        <div class="abstract-content"><?php echo nl2br(esc_html($submission->results)); ?></div>
        
        <h4>Conclusion:</h4>
        <div class="abstract-content"><?php echo nl2br(esc_html($submission->conclusion)); ?></div>
        
        <h4>Keywords:</h4>
        <div class="keywords"><?php echo esc_html($submission->keywords); ?></div>
    </div>
    
    <?php if ($submission->admin_notes): ?>
    <div class="section">
        <h3>Admin Notes</h3>
        <div class="abstract-content"><?php echo nl2br(esc_html($submission->admin_notes)); ?></div>
    </div>
    <?php endif; ?>
    
    <div class="section" style="text-align: center; font-size: 10px; color: #666; margin-top: 30px; border-top: 1px solid #ccc; padding-top: 15px;">
        <p>This document was generated automatically by the HKOTA Abstract Submission system.</p>
        <p>Generated on: <?php echo date('F j, Y g:i A'); ?></p>
    </div>
</body>
</html>
