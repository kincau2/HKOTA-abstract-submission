<?php
/**
 * Template for deadline passed message
 * Variables available: $deadline_info, $existing_submission
 */

?>

<div class="hkota-deadline-passed-container">
    <div class="hkota-notice hkota-notice-warning">
        <h3>Submission Deadline Has Passed</h3>
        
        <div class="deadline-info">
            <p><strong>The deadline for abstract submissions was:</strong></p>
            <p class="deadline-date">
                <i class="dashicons dashicons-calendar-alt"></i>
                <?php echo esc_html($deadline_info['deadline_formatted']); ?> (Hong Kong Time)
            </p>
        </div>
        
        <?php if ($existing_submission): ?>
            <div class="submission-status-section">
                <h4>Your Submission Status</h4>
                <div class="status-info">
                    <p><strong>Abstract Title:</strong> <?php echo esc_html($existing_submission->abstract_title); ?></p>
                    <p><strong>Submission Date:</strong> <?php echo esc_html(date('F j, Y \a\t g:i A', strtotime($existing_submission->submission_date))); ?></p>
                    <p><strong>Current Status:</strong> 
                        <span class="status-badge status-<?php echo esc_attr($existing_submission->status); ?>">
                            <?php 
                            $status_text = $existing_submission->status;
                            if ($status_text === 'awaiting_upload') {
                                $status_text = 'Accepted - Awaiting Upload';
                            } else {
                                $status_text = ucfirst($status_text);
                            }
                            echo esc_html($status_text); 
                            ?>
                        </span>
                    </p>
                    
                    <?php if (in_array($existing_submission->status, array('accepted', 'awaiting_upload'))): ?>
                        <div class="accepted-submission-info">
                            <div class="hkota-notice hkota-notice-success">
                                <p><strong>Congratulations!</strong> Your abstract has been accepted for presentation.</p>
                                <?php if ($existing_submission->status === 'awaiting_upload'): ?>
                                    <p><strong>Important:</strong> To complete your submission, please upload a supporting document (PDF format only, max 10MB):</p>
                                <?php else: ?>
                                    <p>Your submission is complete. You may still update your supporting document if needed (PDF format only, max 10MB):</p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- File Upload Section -->
                            <div class="file-upload-section">
                                <h5>Supporting Document Upload</h5>
                                
                                <?php
                                $file_status = HKOTA_File_Handler::get_file_status($existing_submission->id);
                                $document_deadline_info = HKOTA_Admin::get_document_deadline_info();
                                ?>
                                
                                <!-- Document Deadline Information -->
                                <?php if ($document_deadline_info['has_deadline']): ?>
                                    <div class="document-deadline-info">
                                        <?php if ($document_deadline_info['is_passed']): ?>
                                            <div class="hkota-notice hkota-notice-error">
                                                <h4>Document Upload Deadline Passed</h4>
                                                <p><strong>Document deadline was:</strong> <?php echo esc_html($document_deadline_info['deadline_formatted']); ?> (Hong Kong Time)</p>
                                                <p><?php echo esc_html($document_deadline_info['message']); ?></p>
                                            </div>
                                        <?php else: ?>
                                            <div class="hkota-notice hkota-notice-info">
                                                <h4>Document Upload Deadline</h4>
                                                <p><strong>You have until:</strong> <?php echo esc_html($document_deadline_info['deadline_formatted']); ?> (Hong Kong Time)</p>
                                                <?php if ($document_deadline_info['time_remaining']): ?>
                                                    <p><strong>Time remaining:</strong> 
                                                        <?php 
                                                        $remaining = $document_deadline_info['time_remaining'];
                                                        if ($remaining['days'] > 0) {
                                                            echo $remaining['days'] . ' days, ' . $remaining['hours'] . ' hours';
                                                        } else {
                                                            echo $remaining['hours'] . ' hours, ' . $remaining['minutes'] . ' minutes';
                                                        }
                                                        ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($file_status['has_file']): ?>
                                    <div class="uploaded-file-info">
                                        <p><strong>Uploaded File:</strong> <?php echo esc_html(basename($file_status['filename'])); ?></p>
                                        <?php if ($file_status['can_upload']): ?>
                                            <p><em>You can upload a new file to replace the existing one.</em></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($file_status['can_upload']): ?>
                                <form id="supporting-document-form" enctype="multipart/form-data">
                                    <?php wp_nonce_field('hkota_file_upload_nonce', 'file_upload_nonce'); ?>
                                    
                                    <div class="file-input-group">
                                        <label for="supporting_document">Select PDF File:</label>
                                        <input type="file" 
                                               id="supporting_document" 
                                               name="supporting_document" 
                                               accept=".pdf,application/pdf" 
                                               required>
                                        <p class="description">Maximum file size: 10MB. PDF format only.</p>
                                    </div>
                                    
                                    <p class="submit">
                                        <button type="submit" class="button button-primary" id="upload-document-btn">
                                            <?php echo $file_status['has_file'] ? 'Replace Document' : 'Upload Document'; ?>
                                        </button>
                                    </p>
                                </form>
                                
                                <div id="upload-messages"></div>
                                <?php else: ?>
                                    <div class="upload-disabled-info">
                                        <div class="hkota-notice hkota-notice-warning">
                                            <p><strong>Document upload is currently not available.</strong></p>
                                            <?php if ($document_deadline_info['has_deadline'] && $document_deadline_info['is_passed']): ?>
                                                <p>The deadline for document uploads has passed.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php elseif ($existing_submission->status === 'rejected'): ?>
                        <div class="rejected-submission-info">
                            <div class="hkota-notice hkota-notice-error">
                                <p>Unfortunately, your abstract was not selected for presentation this time.</p>
                                <p>Thank you for your submission and interest in participating.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="pending-submission-info">
                            <div class="hkota-notice hkota-notice-info">
                                <p>Your submission is currently under review. You will be notified once a decision has been made.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="deadline-message">
                <?php echo wp_kses_post(wpautop($deadline_info['message'])); ?>
            </div>
        <?php endif; ?>
        
        <div class="contact-info">
            <p><strong>Need Help?</strong></p>
            <p>If you believe this is an error or have special circumstances, please contact the administrators for assistance.</p>
        </div>
    </div>
    
    <style>
    .hkota-deadline-passed-container {
        max-width: 700px;
        margin: 20px auto;
        padding: 20px;
    }
    
    .hkota-notice {
        padding: 20px;
        border-radius: 5px;
        border-left: 4px solid #ffb900;
        background: #fff8e1;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .hkota-notice-success {
        border-left-color: #46b450;
        background: #f0fff4;
        color: #155724;
    }
    
    .hkota-notice-error {
        border-left-color: #dc3545;
        background: #f8d7da;
        color: #721c24;
    }
    
    .hkota-notice-info {
        border-left-color: #0073aa;
        background: #e1f5fe;
        color: #0c5460;
    }
    
    .hkota-notice h3 {
        margin-top: 0;
        color: #b8860b;
        font-size: 1.3em;
    }
    
    .deadline-info {
        background: #f9f9f9;
        padding: 15px;
        border-radius: 3px;
        margin: 15px 0;
        border: 1px solid #e0e0e0;
    }
    
    .deadline-date {
        font-size: 1.1em;
        font-weight: bold;
        color: #d63384;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .deadline-date .dashicons {
        width: 20px;
        height: 20px;
        font-size: 20px;
    }
    
    .submission-status-section {
        margin-top: 20px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 5px;
        border: 1px solid #dee2e6;
    }
    
    .submission-status-section h4 {
        margin-top: 0;
        color: #495057;
        border-bottom: 2px solid #0073aa;
        padding-bottom: 10px;
    }
    
    .status-info p {
        margin: 10px 0;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 4px;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 0.85em;
    }
    
    .status-badge.status-accepted {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status-badge.status-awaiting_upload {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .status-badge.status-rejected {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .status-badge.status-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .file-upload-section {
        margin-top: 20px;
        padding: 20px;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
    }
    
    .file-upload-section h5 {
        margin-top: 0;
        color: #333;
    }
    
    .file-input-group {
        margin: 15px 0;
    }
    
    .file-input-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }
    
    .file-input-group input[type="file"] {
        width: 100%;
        padding: 8px;
        border: 2px dashed #ddd;
        border-radius: 4px;
        background: #fafafa;
    }
    
    .file-input-group input[type="file"]:focus {
        border-color: #0073aa;
        outline: none;
    }
    
    .uploaded-file-info {
        margin: 15px 0;
        padding: 10px;
        background: #e8f5e8;
        border: 1px solid #c3e6cb;
        border-radius: 4px;
    }
    
    .document-deadline-info {
        margin: 15px 0;
    }
    
    .document-deadline-info h4 {
        margin-top: 0;
        margin-bottom: 10px;
    }
    
    .upload-disabled-info {
        margin: 15px 0;
    }
    
    .deadline-message {
        margin: 15px 0;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 3px;
        border-left: 3px solid #dc3545;
    }
    
    .contact-info {
        margin-top: 20px;
        padding: 15px;
        background: #e7f3ff;
        border-radius: 3px;
        border-left: 3px solid #0073aa;
    }
    
    .contact-info p:last-child {
        margin-bottom: 0;
    }
    
    #upload-messages {
        margin-top: 15px;
    }
    
    #upload-messages .message {
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 10px;
    }
    
    #upload-messages .success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    #upload-messages .error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .hkota-deadline-passed-container {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
    }
    
    .hkota-notice {
        padding: 20px;
        border-radius: 5px;
        border-left: 4px solid #ffb900;
        background: #fff8e1;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .hkota-notice h3 {
        margin-top: 0;
        color: #b8860b;
        font-size: 1.3em;
    }
    
    .deadline-info {
        background: #f9f9f9;
        padding: 15px;
        border-radius: 3px;
        margin: 15px 0;
        border: 1px solid #e0e0e0;
    }
    
    .deadline-date {
        font-size: 1.1em;
        font-weight: bold;
        color: #d63384;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .deadline-date .dashicons {
        width: 20px;
        height: 20px;
        font-size: 20px;
    }
    
    .deadline-message {
        margin: 15px 0;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 3px;
        border-left: 3px solid #dc3545;
    }
    
    .contact-info {
        margin-top: 20px;
        padding: 15px;
        background: #e7f3ff;
        border-radius: 3px;
        border-left: 3px solid #0073aa;
    }
    
    .contact-info p:last-child {
        margin-bottom: 0;
    }
    </style>
</div>
