<?php
/**
 * Admin page template for submission settings
 * Variables available: $reviewers (passed from controller)
 */

// Get current reviewers
$reviewers = HKOTA_Admin::get_reviewers();
?>
<div class="wrap">
    <h1>Submission Settings</h1>
    
    <div class="hkota-settings-container">
        
        <!-- Reviewer Management Section -->
        <div class="postbox" style="margin-top: 20px;">
            <h2 class="hndle"><span>Reviewer Management</span></h2>
            <div class="inside">
                <p>Manage users who can review abstract submissions. Only users with the "hkota_reviewer" capability can access the review interface.</p>
                
                <!-- Add Reviewer Section -->
                <div class="hkota-add-reviewer-section" style="margin-bottom: 30px; padding: 20px; background: #f9f9f9; border-radius: 5px;">
                    <h3>Add New Reviewer</h3>
                    <div class="hkota-reviewer-search">
                        <label for="reviewer-search-input">Search Users by Email:</label>
                        <div style="position: relative; max-width: 400px;">
                            <input type="text" 
                                   id="reviewer-search-input" 
                                   class="regular-text" 
                                   placeholder="Type email address to search..."
                                   autocomplete="off">
                            <div id="reviewer-search-results" class="hkota-search-results" style="display: none;"></div>
                        </div>
                        <p class="description">Start typing an email address to see user suggestions. Click on a user to add them as a reviewer.</p>
                    </div>
                </div>
                
                <!-- Current Reviewers List -->
                <div class="hkota-reviewers-list-section">
                    <h3>Current Reviewers</h3>
                    <div id="reviewers-list">
                        <?php if (empty($reviewers)): ?>
                            <div class="no-reviewers" style="padding: 20px; text-align: center; color: #666; font-style: italic;">
                                No reviewers assigned yet. Add users above to grant them review access.
                            </div>
                        <?php else: ?>
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th style="width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reviewers as $reviewer): ?>
                                        <tr data-reviewer-id="<?php echo esc_attr($reviewer['id']); ?>">
                                            <td><strong><?php echo esc_html($reviewer['name']); ?></strong></td>
                                            <td><?php echo esc_html($reviewer['email']); ?></td>
                                            <td>
                                                <button type="button" 
                                                        class="button button-small remove-reviewer-btn" 
                                                        data-reviewer-id="<?php echo esc_attr($reviewer['id']); ?>"
                                                        data-reviewer-name="<?php echo esc_attr($reviewer['name']); ?>">
                                                    Remove
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Deadline Management Section -->
        <div class="postbox" style="margin-top: 20px;">
            <h2 class="hndle"><span>Submission Deadline</span></h2>
            <div class="inside">
                <?php settings_errors('hkota_deadline'); ?>
                
                <form method="post" action="">
                    <?php wp_nonce_field('hkota_deadline_settings', 'deadline_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="submission_deadline">Submission Deadline</label>
                            </th>
                            <td>
                                <?php
                                $current_deadline = get_option('hkota_submission_deadline');
                                $deadline_input_value = '';
                                if ($current_deadline) {
                                    // Convert from UTC to Hong Kong time for display
                                    $deadline_obj = new DateTime($current_deadline, new DateTimeZone('UTC'));
                                    $deadline_obj->setTimezone(new DateTimeZone('Asia/Hong_Kong'));
                                    $deadline_input_value = $deadline_obj->format('Y-m-d\TH:i');
                                }
                                ?>
                                <input type="datetime-local" 
                                       id="submission_deadline" 
                                       name="submission_deadline" 
                                       value="<?php echo esc_attr($deadline_input_value); ?>"
                                       class="regular-text">
                                <p class="description">
                                    Set the deadline for abstract submissions. Time zone: UTC+8 (Hong Kong Time)<br>
                                    After this date and time, users will not be able to submit or edit their abstracts.
                                    <?php
                                    if ($current_deadline) {
                                        $deadline_obj = new DateTime($current_deadline, new DateTimeZone('UTC'));
                                        $deadline_hk = clone $deadline_obj;
                                        $deadline_hk->setTimezone(new DateTimeZone('Asia/Hong_Kong'));
                                        $now = new DateTime('now', new DateTimeZone('UTC'));
                                        $is_past = $deadline_obj < $now;
                                        echo '<br><strong>Current Status:</strong> ';
                                        if ($is_past) {
                                            echo '<span style="color: #dc3232;">Deadline has passed - Submissions are closed</span>';
                                        } else {
                                            $time_left = $now->diff($deadline_obj);
                                            if ($time_left->days > 0) {
                                                echo '<span style="color: #46b450;">Open - ' . $time_left->days . ' days, ' . $time_left->h . ' hours remaining</span>';
                                            } else {
                                                echo '<span style="color: #ffb900;">Open - ' . $time_left->h . ' hours, ' . $time_left->i . ' minutes remaining</span>';
                                            }
                                        }
                                    }
                                    ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="deadline_message">Deadline Message</label>
                            </th>
                            <td>
                                <textarea id="deadline_message" 
                                          name="deadline_message" 
                                          rows="3" 
                                          class="large-text"
                                          placeholder="Custom message to display when deadline has passed..."><?php echo esc_textarea(get_option('hkota_deadline_message', 'The submission deadline has passed. Abstract submissions are no longer accepted.')); ?></textarea>
                                <p class="description">
                                    This message will be displayed to users when the deadline has passed.
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <?php submit_button('Save Deadline Settings', 'primary', 'submit_deadline_settings', false); ?>
                        <?php submit_button('Clear Deadline (Allow Unlimited Submissions)', 'secondary', 'clear_deadline', false, array('onclick' => 'return confirm("Are you sure you want to clear the deadline? This will allow unlimited submissions until a new deadline is set.");')); ?>
                    </p>
                </form>
            </div>
        </div>
        
        <!-- Supporting Document Deadline Section -->
        <div class="postbox" style="margin-top: 20px;">
            <h2 class="hndle"><span>Supporting Document Upload Deadline</span></h2>
            <div class="inside">
                <?php settings_errors('hkota_document_deadline'); ?>
                
                <form method="post" action="">
                    <?php wp_nonce_field('hkota_document_deadline_settings', 'document_deadline_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="document_deadline">Document Upload Deadline</label>
                            </th>
                            <td>
                                <?php
                                $current_doc_deadline = get_option('hkota_document_deadline');
                                $doc_deadline_input_value = '';
                                if ($current_doc_deadline) {
                                    // Convert from UTC to Hong Kong time for display
                                    $doc_deadline_obj = new DateTime($current_doc_deadline, new DateTimeZone('UTC'));
                                    $doc_deadline_obj->setTimezone(new DateTimeZone('Asia/Hong_Kong'));
                                    $doc_deadline_input_value = $doc_deadline_obj->format('Y-m-d\TH:i');
                                }
                                ?>
                                <input type="datetime-local" 
                                       id="document_deadline" 
                                       name="document_deadline" 
                                       value="<?php echo esc_attr($doc_deadline_input_value); ?>"
                                       class="regular-text">
                                <p class="description">
                                    Set the deadline for supporting document uploads (for accepted submissions only). Time zone: UTC+8 (Hong Kong Time)<br>
                                    After this date and time, users with accepted submissions will not be able to upload supporting documents.
                                    <?php
                                    if ($current_doc_deadline) {
                                        $doc_deadline_obj = new DateTime($current_doc_deadline, new DateTimeZone('UTC'));
                                        $doc_deadline_hk = clone $doc_deadline_obj;
                                        $doc_deadline_hk->setTimezone(new DateTimeZone('Asia/Hong_Kong'));
                                        $now = new DateTime('now', new DateTimeZone('UTC'));
                                        $is_past = $doc_deadline_obj < $now;
                                        echo '<br><strong>Current Status:</strong> ';
                                        if ($is_past) {
                                            echo '<span style="color: #dc3232;">Document deadline has passed - Uploads are closed</span>';
                                        } else {
                                            $time_left = $now->diff($doc_deadline_obj);
                                            if ($time_left->days > 0) {
                                                echo '<span style="color: #46b450;">Open - ' . $time_left->days . ' days, ' . $time_left->h . ' hours remaining</span>';
                                            } else {
                                                echo '<span style="color: #ffb900;">Open - ' . $time_left->h . ' hours, ' . $time_left->i . ' minutes remaining</span>';
                                            }
                                        }
                                    }
                                    ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="document_deadline_message">Document Deadline Message</label>
                            </th>
                            <td>
                                <textarea id="document_deadline_message" 
                                          name="document_deadline_message" 
                                          rows="3" 
                                          class="large-text"
                                          placeholder="Custom message to display when document deadline has passed..."><?php echo esc_textarea(get_option('hkota_document_deadline_message', 'The deadline for supporting document uploads has passed. You can no longer upload or update your supporting documents.')); ?></textarea>
                                <p class="description">
                                    This message will be displayed to users with accepted submissions when the document upload deadline has passed.
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <?php submit_button('Save Document Deadline Settings', 'primary', 'submit_document_deadline_settings', false); ?>
                        <?php submit_button('Clear Document Deadline (Allow Unlimited Uploads)', 'secondary', 'clear_document_deadline', false, array('onclick' => 'return confirm("Are you sure you want to clear the document deadline? This will allow unlimited document uploads for accepted submissions.");')); ?>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.hkota-search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.hkota-search-result-item {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
}

.hkota-search-result-item:hover {
    background-color: #f0f0f0;
}

.hkota-search-result-item:last-child {
    border-bottom: none;
}

.hkota-search-result-name {
    font-weight: bold;
    color: #333;
}

.hkota-search-result-email {
    color: #666;
    font-size: 0.9em;
}

.remove-reviewer-btn {
    background-color: #dc3232;
    color: white;
    border-color: #dc3232;
}

.remove-reviewer-btn:hover {
    background-color: #c02c2c;
    border-color: #c02c2c;
}

.no-reviewers {
    border: 2px dashed #ddd;
    border-radius: 5px;
}
</style>
