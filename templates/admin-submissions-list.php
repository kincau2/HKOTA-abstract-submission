<?php
/**
 * Admin page template for submissions list
 * Available variables: $submissions array
 */
?>
<div class="wrap">
    <h1>Abstract Submissions</h1>
    
    <div class="hkota-submissions-container">
        <?php if (empty($submissions)): ?>
            <div class="notice notice-info">
                <p>No submissions found. Submissions will appear here once users start submitting abstracts.</p>
            </div>
        <?php else: ?>
            <div class="tablenav top">
                <div class="alignleft actions">
                    <p class="search-box">
                        <label class="screen-reader-text" for="submission-search-input">Search submissions:</label>
                        <input type="search" id="submission-search-input" name="s" value="" placeholder="Search submissions...">
                        <input type="submit" id="search-submit" class="button" value="Search">
                    </p>
                </div>
                <div class="alignright">
                    <span class="displaying-num"><?php echo count($submissions); ?> items</span>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column">Name</th>
                        <th scope="col" class="manage-column">Email</th>
                        <th scope="col" class="manage-column">Organization</th>
                        <th scope="col" class="manage-column">Theme</th>
                        <th scope="col" class="manage-column">Presentation</th>
                        <th scope="col" class="manage-column">Abstract Title</th>
                        <th scope="col" class="manage-column">Status</th>
                        <th scope="col" class="manage-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($submission->title . " " . $submission->surname . ', ' . $submission->given_name); ?></strong>
                            </td>
                            <td>
                                <a href="mailto:<?php echo esc_attr($submission->contact_email); ?>">
                                    <?php echo esc_html($submission->contact_email); ?>
                                </a>
                            </td>
                            <td>
                                <?php 
                                $org = $submission->organization;
                                echo esc_html(strlen($org) > 30 ? substr($org, 0, 30) . '...' : $org); 
                                ?>
                            </td>
                            <td>
                                <?php echo $submission->theme; ?>
                            </td>
                            <td><?php echo esc_html($submission->presentation_preference); ?></td>
                            <td>
                                <?php echo $submission->abstract_title; ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr($submission->status); ?>">
                                    <?php 
                                    $status_text = $submission->status;
                                    if ($status_text === 'awaiting_upload') {
                                        $status_text = 'Awaiting Upload';
                                    } else {
                                        $status_text = ucfirst($status_text);
                                    }
                                    echo esc_html($status_text); 
                                    ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($submission->status === 'pending'): ?>
                                        <button class="button button-primary update-status" 
                                                data-id="<?php echo esc_attr($submission->id); ?>" 
                                                data-status="accepted"
                                                title="Accept this submission">
                                            Accept
                                        </button>
                                        <button class="button button-secondary update-status" 
                                                data-id="<?php echo esc_attr($submission->id); ?>" 
                                                data-status="rejected"
                                                title="Reject this submission">
                                            Reject
                                        </button>
                                    <?php endif; ?>
                                    <button class="button download-pdf" 
                                            data-id="<?php echo esc_attr($submission->id); ?>"
                                            title="Download PDF report">
                                        Download PDF
                                    </button>
                                    <?php if (!empty($submission->supporting_document)): ?>
                                        <button class="button download-supporting-doc" 
                                                data-id="<?php echo esc_attr($submission->id); ?>"
                                                title="Download supporting document">
                                            Download Support Doc
                                        </button>
                                    <?php endif; ?>
                                    <button class="button view-details" 
                                            data-id="<?php echo esc_attr($submission->id); ?>"
                                            title="View full details">
                                        View Details
                                    </button>
                                    <button class="button button-link-delete delete-submission" 
                                            data-id="<?php echo esc_attr($submission->id); ?>"
                                            data-name="<?php echo esc_attr($submission->surname . ', ' . $submission->given_name); ?>"
                                            title="Delete this submission">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

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
