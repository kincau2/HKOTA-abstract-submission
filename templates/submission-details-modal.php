<?php
/**
 * Template for submission details modal
 * Variables available: $submission
 */
?>

<div class="submission-details-modal">
    <div class="detail-section">
        <h4>Personal Information</h4>
        <div class="detail-grid">
            <div class="detail-item">
                <label>Name:</label>
                <span><?php echo esc_html($submission->title . ' ' . $submission->surname . ', ' . $submission->given_name); ?></span>
            </div>
            <div class="detail-item">
                <label>Contact Number:</label>
                <span><?php echo esc_html($submission->contact_number); ?></span>
            </div>
            <div class="detail-item">
                <label>Email:</label>
                <span><?php echo esc_html($submission->contact_email); ?></span>
            </div>
            <div class="detail-item">
                <label>Organization:</label>
                <span><?php echo esc_html($submission->organization); ?></span>
            </div>
        </div>
    </div>
    
    <div class="detail-section">
        <h4>Presentation Details</h4>
        <div class="detail-grid">
            <div class="detail-item">
                <label>Theme:</label>
                <span><?php echo esc_html($submission->theme); ?></span>
            </div>
            <div class="detail-item">
                <label>Presentation Type:</label>
                <span><?php echo esc_html($submission->presentation_preference); ?></span>
            </div>
            <?php if ($submission->submission_number): ?>
            <div class="detail-item">
                <label>Submission Number:</label>
                <span><?php echo esc_html($submission->submission_number); ?></span>
            </div>
            <?php endif; ?>
            <div class="detail-item">
                <label>Status:</label>
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
        </div>
    </div>
    
    <div class="detail-section">
        <h4>Abstract Information</h4>
        <div class="detail-item full-width">
            <label>Abstract Title:</label>
            <span><?php echo esc_html($submission->abstract_title); ?></span>
        </div>
        
        <div class="detail-item full-width">
            <label>Authors:</label>
            <span><?php echo nl2br(esc_html($submission->authors)); ?></span>
        </div>
        
        <div class="detail-item full-width">
            <label>Affiliations:</label>
            <span><?php echo nl2br(esc_html($submission->affiliations)); ?></span>
        </div>
        
        <div class="detail-item full-width">
            <label>Background:</label>
            <div class="text-content"><?php echo nl2br(esc_html($submission->background)); ?></div>
        </div>
        
        <div class="detail-item full-width">
            <label>Methods:</label>
            <div class="text-content"><?php echo nl2br(esc_html($submission->methods)); ?></div>
        </div>
        
        <div class="detail-item full-width">
            <label>Results and Findings:</label>
            <div class="text-content"><?php echo nl2br(esc_html($submission->results)); ?></div>
        </div>
        
        <div class="detail-item full-width">
            <label>Conclusion:</label>
            <div class="text-content"><?php echo nl2br(esc_html($submission->conclusion)); ?></div>
        </div>
        
        <div class="detail-item full-width">
            <label>Keywords:</label>
            <span><?php echo esc_html($submission->keywords); ?></span>
        </div>
    </div>
    
    <div class="detail-section">
        <h4>Submission Information</h4>
        <div class="detail-grid">
            <div class="detail-item">
                <label>Submitted:</label>
                <span><?php echo esc_html(date('F j, Y \a\t g:i A', strtotime($submission->submission_date))); ?></span>
            </div>
            <?php if ($submission->last_modified && $submission->last_modified !== $submission->submission_date): ?>
            <div class="detail-item">
                <label>Last Modified:</label>
                <span><?php echo esc_html(date('F j, Y \a\t g:i A', strtotime($submission->last_modified))); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($submission->supporting_document)): ?>
            <div class="detail-item">
                <label>Supporting Document:</label>
                <span>Uploaded</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.submission-details-modal {
    max-width: 700px;
}

.detail-section {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.detail-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.detail-section h4 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 16px;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 5px;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-item label {
    font-weight: bold;
    color: #555;
    font-size: 14px;
}

.detail-item span {
    color: #333;
    line-height: 1.5;
}

.text-content {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 4px;
    border: 1px solid #e9ecef;
    line-height: 1.6;
    color: #333;
    max-height: 150px;
    overflow-y: auto;
    word-wrap: break-word;
}

@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .submission-details-modal {
        max-width: 90vw;
    }
}
</style>