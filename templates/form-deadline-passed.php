<?php
/**
 * Template for deadline passed message (for users with no submissions)
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
        
        <div class="deadline-message">
            <p>New abstract submissions are no longer being accepted.</p>
            <?php if (!empty($deadline_info['message'])): ?>
                <?php echo wp_kses_post(wpautop($deadline_info['message'])); ?>
            <?php endif; ?>
        </div>
        
        <div class="contact-info">
            <p><strong>Need Help?</strong></p>
            <p>If you believe this is an error or have special circumstances, please contact the administrators for assistance.</p>
        </div>
    </div>
</div>

<style>
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

.hkota-notice-warning {
    border-left-color: #ffb900;
    background: #fff8e1;
    color: #856404;
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
