<?php
/**
 * Template for user submissions list
 * Variables available: $user, $submissions, $deadline_info
 */
?>

<div class="hkota-abstract-form-container">
    <h3>Your Abstract Submissions</h3>
    
    <?php if (isset($_GET['deadline_error']) && $_GET['deadline_error'] === '1'): ?>
        <div class="hkota-notice hkota-notice-error">
            <p><strong>Action Not Allowed:</strong> New submissions are not allowed after the deadline has passed. You can only upload documents for accepted submissions.</p>
        </div>
    <?php endif; ?>
    
    <?php if ($deadline_info['has_deadline']): ?>
        <?php if (!$deadline_info['is_passed']): ?>
            <div class="hkota-notice hkota-notice-warning">
                <p><strong>Submission Deadline:</strong> <?php echo esc_html($deadline_info['deadline_formatted']); ?> (Hong Kong Time)</p>
                <?php if (isset($deadline_info['time_remaining'])): ?>
                    <p><strong>Time Remaining:</strong> 
                        <?php 
                        $remaining = $deadline_info['time_remaining'];
                        if ($remaining['days'] > 0) {
                            echo esc_html($remaining['days'] . ' days, ' . $remaining['hours'] . ' hours');
                        } elseif ($remaining['hours'] > 0) {
                            echo esc_html($remaining['hours'] . ' hours, ' . $remaining['minutes'] . ' minutes');
                        } else {
                            echo esc_html($remaining['minutes'] . ' minutes');
                        }
                        ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="hkota-notice hkota-notice-error">
                <p><strong>Submission Deadline Passed:</strong> The deadline for new abstract submissions was <?php echo esc_html($deadline_info['deadline_formatted']); ?> (Hong Kong Time).</p>
                <p><strong>Document Upload:</strong> If you have accepted submissions, you can still upload supporting documents for them in below.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="submissions-header">
        <p>You have <?php echo count($submissions); ?> submission<?php echo count($submissions) !== 1 ? 's' : ''; ?>:</p>
        
        <?php if (!$deadline_info['has_deadline'] || !$deadline_info['is_passed']): ?>
            <button type="button" class="hkota-btn hkota-btn-primary" id="add-new-submission">
                <span class="dashicons dashicons-plus-alt"></span> Add New Submission
            </button>
        <?php endif; ?>
    </div>
    
    <div class="submissions-list">
        <?php foreach ($submissions as $index => $submission): ?>
            <div class="submission-item" data-submission-id="<?php echo esc_attr($submission->id); ?>">
                <div class="submission-header">
                    <h4 class="submission-title">
                        <?php echo esc_html($submission->abstract_title); ?>
                        <?php if ($submission->submission_number): ?>
                            <span class="submission-number">#<?php echo esc_html($submission->submission_number); ?></span>
                        <?php endif; ?>
                    </h4>
                    <span class="status-badge status-<?php echo esc_attr($submission->status); ?>">
                        <?php 
                        $status_text = $submission->status;
                        if ($status_text === 'awaiting_upload') {
                            $status_text = 'Awaiting Upload';
                        } elseif ($status_text === 'completed') {
                            $status_text = 'Completed';
                        } else {
                            $status_text = ucfirst($status_text);
                        }
                        echo esc_html($status_text); 
                        ?>
                    </span>
                </div>
                
                <div class="submission-details">
                    <div class="submission-info">
                        <p><strong>Theme:</strong> <?php echo esc_html($submission->theme); ?></p>
                        <p><strong>Presentation:</strong> <?php echo esc_html($submission->presentation_preference); ?></p>
                        <p><strong>Submitted:</strong> <?php echo esc_html(date('M j, Y g:i A', strtotime($submission->submission_date))); ?></p>
                        <?php if ($submission->last_modified && $submission->last_modified !== $submission->submission_date): ?>
                            <p><strong>Last Modified:</strong> <?php echo esc_html(date('M j, Y g:i A', strtotime($submission->last_modified))); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="submission-abstract">
                        <p><strong>Background:</strong> <?php echo esc_html(wp_trim_words($submission->background, 20, '...')); ?></p>
                        <p><strong>Keywords:</strong> <?php echo esc_html($submission->keywords); ?></p>
                    </div>
                </div>
                
                <div class="submission-actions">
                    <?php if (!$deadline_info['has_deadline'] || !$deadline_info['is_passed']): ?>
                        <button type="button" class="hkota-btn hkota-btn-secondary edit-submission" 
                                data-submission-id="<?php echo esc_attr($submission->id); ?>">
                            <span class="dashicons dashicons-edit"></span> Edit
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($submission->status === 'awaiting_upload' || $submission->status === 'completed'): ?>
                        <?php 
                        $document_deadline_info = HKOTA_Admin::get_document_deadline_info();
                        $can_upload = !($document_deadline_info['has_deadline'] && $document_deadline_info['is_passed']);
                        ?>
                        <?php if ($can_upload): ?>
                            <button type="button" class="hkota-btn hkota-btn-secondary upload-document" 
                                    data-submission-id="<?php echo esc_attr($submission->id); ?>">
                                <span class="dashicons dashicons-upload"></span> 
                                <?php echo !empty($submission->supporting_document) ? 'Update Document' : 'Upload Document'; ?>
                            </button>
                        <?php else: ?>
                            <button type="button" class="hkota-btn hkota-btn-secondary" disabled title="Document upload deadline has passed">
                                <span class="dashicons dashicons-upload"></span> 
                                Upload Deadline Passed
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <button type="button" class="hkota-btn hkota-btn-outline view-submission" 
                            data-submission-id="<?php echo esc_attr($submission->id); ?>">
                        <span class="dashicons dashicons-visibility"></span> View Details
                    </button>
                    
                    <?php if ($submission->status === 'pending' && (!$deadline_info['has_deadline'] || !$deadline_info['is_passed'])): ?>
                        <button type="button" class="hkota-btn hkota-btn-danger delete-submission" 
                                data-submission-id="<?php echo esc_attr($submission->id); ?>"
                                data-title="<?php echo esc_attr($submission->abstract_title); ?>">
                            <span class="dashicons dashicons-trash"></span> Delete
                        </button>
                    <?php endif; ?>
                </div>
                
                <?php if ($submission->status === 'awaiting_upload'): ?>
                    <div class="submission-notice notice-warning">
                        <p><strong>Action Required:</strong> Your abstract has been accepted. Please upload your supporting document to complete the submission.</p>
                        
                        <?php 
                        $document_deadline_info = HKOTA_Admin::get_document_deadline_info();
                        if ($document_deadline_info['has_deadline']): ?>
                            <?php if (!$document_deadline_info['is_passed']): ?>
                                <div class="document-deadline-notice">
                                    <p><strong>Document Upload Deadline:</strong> <?php echo esc_html($document_deadline_info['deadline_formatted']); ?> (Hong Kong Time)</p>
                                    <?php if (isset($document_deadline_info['time_remaining'])): ?>
                                        <p><strong>Time Remaining:</strong> 
                                            <?php 
                                            $remaining = $document_deadline_info['time_remaining'];
                                            if ($remaining['days'] > 0) {
                                                echo esc_html($remaining['days'] . ' days, ' . $remaining['hours'] . ' hours');
                                            } elseif ($remaining['hours'] > 0) {
                                                echo esc_html($remaining['hours'] . ' hours, ' . $remaining['minutes'] . ' minutes');
                                            } else {
                                                echo esc_html($remaining['minutes'] . ' minutes');
                                            }
                                            ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="document-deadline-notice deadline-passed">
                                    <p><strong>Document Upload Deadline Passed:</strong> The deadline was <?php echo esc_html($document_deadline_info['deadline_formatted']); ?> (Hong Kong Time)</p>
                                    <p><em>Document uploads are no longer accepted.</em></p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php elseif ($submission->status === 'completed'): ?>
                    <div class="submission-notice notice-success">
                        <p><strong>Complete:</strong> Your submission has been completed successfully.</p>
                    </div>
                <?php elseif ($submission->status === 'rejected'): ?>
                    <div class="submission-notice notice-error">
                        <p><strong>Status:</strong> This submission was not accepted.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div id="hkota-form-messages"></div>
</div>

    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="loading-overlay" style="display: none;">
    <div style="text-align: center;">
        <div class="loading-spinner"></div>
        <div class="loading-text">Loading...</div>
    </div>
</div>

<!-- Hidden form container for editing -->
<div id="edit-form-container" style="display: none;"></div>

<!-- Modal for viewing submission details -->
<div id="submission-modal" class="hkota-modal" style="display: none;">
    <div class="hkota-modal-content">
        <div class="hkota-modal-header">
            <h2>Submission Details</h2>
            <span class="hkota-modal-close">&times;</span>
        </div>
        <div class="hkota-modal-body">
            <div id="submission-details-content"></div>
        </div>
    </div>
</div>

<!-- File Upload Modal -->
<div id="upload-modal" class="hkota-modal" style="display: none;">
    <div class="hkota-modal-content">
        <div class="hkota-modal-header">
            <h2>Upload Supporting Document</h2>
            <span class="hkota-modal-close">&times;</span>
        </div>
        <div class="hkota-modal-body">
            <form id="file-upload-form" enctype="multipart/form-data">
                <input type="hidden" id="upload-submission-id" name="submission_id" value="">
                <?php wp_nonce_field('hkota_file_upload_nonce', 'file_upload_nonce'); ?>
                
                <div class="upload-area">
                    <p><strong>Select PDF file to upload:</strong></p>
                    <input type="file" id="supporting_document" name="supporting_document" accept=".pdf" required>
                    <p class="file-info">Maximum file size: 10MB. PDF format only.</p>
                </div>
                
                <div class="upload-actions">
                    <button type="submit" class="hkota-btn hkota-btn-primary">Upload Document</button>
                    <button type="button" class="hkota-btn hkota-btn-secondary cancel-upload">Cancel</button>
                </div>
                
                <div id="upload-progress" style="display: none;">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <p class="progress-text">Uploading...</p>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.submissions-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.submission-item {
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 20px;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: box-shadow 0.3s ease;
}

.submission-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.submission-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #ddd;
    border-radius: 8px 8px 0 0;
}

.submission-title {
    margin: 0;
    color: #333;
    font-size: 16px;
}

.submission-number {
    color: #666;
    font-size: 14px;
    font-weight: normal;
    margin-left: 10px;
}

.submission-details {
    padding: 20px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.submission-info p,
.submission-abstract p {
    margin: 8px 0;
    line-height: 1.5;
}

.submission-actions {
    display: flex;
    gap: 10px;
    padding: 15px 20px;
    background: #f8f9fa;
    border-top: 1px solid #ddd;
    border-radius: 0 0 8px 8px;
    flex-wrap: wrap;
}

.submission-notice {
    margin: 0;
    padding: 12px 20px;
    border-top: 1px solid #ddd;
}

.submission-notice.notice-warning {
    background: #fff3cd;
    color: #856404;
}

.submission-notice.notice-success {
    background: #d1edff;
    color: #0c5460;
}

.submission-notice.notice-error {
    background: #f8d7da;
    color: #721c24;
}

.hkota-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 16px;
    border: 1px solid;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.hkota-btn-primary {
    background: #0073aa;
    color: white;
    border-color: #0073aa;
}

.hkota-btn-primary:hover {
    background: #005177;
    border-color: #005177;
    color: white;
}

.hkota-btn-secondary {
    background: #f8f9fa;
    color: #333;
    border-color: #ddd;
}

.hkota-btn-secondary:hover {
    background: #e9ecef;
    border-color: #adb5bd;
    color: #333;
}

.hkota-btn-outline {
    background: transparent;
    color: #0073aa;
    border-color: #0073aa;
}

.hkota-btn-outline:hover {
    background: #0073aa;
    color: white;
}

.hkota-btn-danger {
    background: #dc3545;
    color: white;
    border-color: #dc3545;
}

.hkota-btn-danger:hover {
    background: #c82333;
    border-color: #bd2130;
    color: white;
}

.hkota-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
}

.document-deadline-notice {
    margin-top: 15px;
    padding: 12px;
    background: #e7f3ff;
    border: 1px solid #0073aa;
    border-radius: 4px;
    font-size: 14px;
}

.document-deadline-notice.deadline-passed {
    background: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

.document-deadline-notice p {
    margin: 5px 0;
}

.document-deadline-notice p:last-child {
    margin-bottom: 0;
}

.hkota-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hkota-modal-content {
    background: white;
    border-radius: 8px;
    max-width: 90%;
    max-height: 90%;
    overflow: auto;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.hkota-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #ddd;
}

.hkota-modal-header h2 {
    margin: 0;
}

.hkota-modal-close {
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.hkota-modal-close:hover {
    color: #333;
}

.hkota-modal-body {
    padding: 20px;
    min-width: 500px;
}

.upload-area {
    text-align: center;
    padding: 20px;
    border: 2px dashed #ddd;
    border-radius: 8px;
    margin-bottom: 20px;
}

.upload-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.progress-bar {
    width: 100%;
    height: 20px;
    background: #f0f0f0;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
}

.progress-fill {
    height: 100%;
    background: #0073aa;
    width: 0%;
    transition: width 0.3s ease;
}

.progress-text {
    text-align: center;
    margin: 0;
}

/* Loading Spinner */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #0073aa;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    margin-top: 15px;
    font-size: 16px;
    color: #333;
    text-align: center;
}

/* Button loading state */
.hkota-btn.loading {
    opacity: 0.6;
    pointer-events: none;
}

.hkota-btn.loading::after {
    content: "";
    display: inline-block;
    width: 12px;
    height: 12px;
    margin-left: 8px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@media (max-width: 768px) {
    .submission-details {
        grid-template-columns: 1fr;
    }
    
    .submission-actions {
        flex-direction: column;
    }
    
    .submissions-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
}
</style>