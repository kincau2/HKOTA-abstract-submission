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
        
        <!-- Other Settings (for future development) -->
        <div class="postbox" style="margin-top: 20px;">
            <h2 class="hndle"><span>General Settings</span></h2>
            <div class="inside">
                <div class="notice notice-info">
                    <p><strong>Additional Settings</strong></p>
                    <p>Future features will include:</p>
                    <ul>
                        <li>Submission deadline management</li>
                        <li>Email template customization</li>
                        <li>Form field configuration</li>
                        <li>Theme and presentation options management</li>
                        <li>Notification settings</li>
                        <li>Export/import settings</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading indicator -->
    <div id="hkota-loading" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.8); color: white; padding: 20px; border-radius: 5px; z-index: 9999;">
        <span class="spinner is-active" style="float: left; margin-right: 10px;"></span>
        Processing...
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
