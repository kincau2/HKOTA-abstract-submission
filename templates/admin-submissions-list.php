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
                    <!-- Presentation Filter -->
                    <select id="presentation-filter" class="postform">
                        <option value="">All Presentations</option>
                        <option value="Oral Presentation">Oral Presentation</option>
                        <option value="E-poster presentation">E-poster presentation</option>
                    </select>
                    
                    <!-- Status Filter -->
                    <select id="status-filter" class="postform">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="awaiting_upload">Awaiting Upload</option>
                        <option value="completed">Completed</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    
                    <button type="button" id="apply-filters" class="button">Filter</button>
                    <button type="button" id="clear-filters" class="button">Clear</button>
                    
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
            
            <table class="wp-list-table widefat fixed striped sortable-table" id="submissions-table">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column sortable" data-sort="submission-number">
                            Submission # <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="manage-column sortable" data-sort="rating">
                            Rating <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="manage-column sortable" data-sort="name">
                            Name <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="manage-column sortable" data-sort="email">
                            Email <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="manage-column sortable" data-sort="organization">
                            Organization <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="manage-column sortable" data-sort="theme">
                            Theme <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="manage-column sortable" data-sort="presentation">
                            Presentation <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="manage-column sortable" data-sort="title">
                            Abstract Title <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="manage-column sortable" data-sort="status">
                            Status <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="manage-column sortable" data-sort="submission-date">
                            Submitted <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="manage-column sortable" data-sort="last-modified">
                            Last Edit <span class="sorting-indicator"></span>
                        </th>
                        <th scope="col" class="manage-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr data-submission-number="<?php echo esc_attr($submission->submission_number ?? ''); ?>"
                            data-name="<?php echo esc_attr($submission->surname . ', ' . $submission->given_name); ?>" 
                            data-email="<?php echo esc_attr($submission->contact_email); ?>" 
                            data-organization="<?php echo esc_attr($submission->organization); ?>" 
                            data-theme="<?php echo esc_attr($submission->theme); ?>" 
                            data-presentation="<?php echo esc_attr($submission->presentation_preference); ?>" 
                            data-title="<?php echo esc_attr($submission->abstract_title); ?>" 
                            data-status="<?php echo esc_attr($submission->status); ?>"
                            data-submission-date="<?php echo esc_attr($submission->submission_date); ?>"
                            data-last-modified="<?php echo esc_attr($submission->last_modified ?? $submission->submission_date); ?>"
                            data-rating="<?php 
                                $avg_rating = HKOTA_Database::get_submission_average_rating($submission->id);
                                echo esc_attr($avg_rating && $avg_rating->total_ratings > 0 ? number_format($avg_rating->average_score, 1) : 'Nil');
                            ?>">
                            <td>
                                <strong><?php echo esc_html($submission->submission_number ?? 'N/A'); ?></strong>
                            </td>
                            <td class="rating-cell">
                                <?php 
                                $avg_rating = HKOTA_Database::get_submission_average_rating($submission->id);
                                if ($avg_rating && $avg_rating->total_ratings > 0): 
                                ?>
                                    <div class="rating-display">
                                        <span class="rating-score"><?php echo number_format($avg_rating->average_score, 1); ?>%</span>
                                        <small>(<?php echo $avg_rating->total_ratings; ?> review<?php echo $avg_rating->total_ratings > 1 ? 's' : ''; ?>)</small>
                                        <br>
                                        <button class="button button-small view-rating-details" 
                                                data-id="<?php echo esc_attr($submission->id); ?>"
                                                title="View rating details">
                                            View Details
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="rating-nil">Nil</span>
                                <?php endif; ?>
                            </td>
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
                                    } elseif ($status_text === 'completed') {
                                        $status_text = 'Completed';
                                    } else {
                                        $status_text = ucfirst($status_text);
                                    }
                                    echo esc_html($status_text); 
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php echo esc_html(date('M j, Y', strtotime($submission->submission_date))); ?>
                            </td>
                            <td>
                                <?php 
                                $last_modified = $submission->last_modified ?? $submission->submission_date;
                                echo esc_html(date('M j, Y g:i A', strtotime($last_modified))); 
                                ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($submission->status === 'pending'): ?>
                                        <button class="button button-primary accept-submission" 
                                                data-id="<?php echo esc_attr($submission->id); ?>" 
                                                data-presentation="Oral Presentation"
                                                title="Accept as Oral Presentation">
                                            Accept Oral
                                        </button>
                                        <button class="button button-primary accept-submission" 
                                                data-id="<?php echo esc_attr($submission->id); ?>" 
                                                data-presentation="E-poster presentation"
                                                title="Accept as E-poster Presentation">
                                            Accept E-Poster
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

<!-- Modal for viewing rating details -->
<div id="rating-modal" class="hkota-modal" style="display: none;">
    <div class="hkota-modal-content">
        <div class="hkota-modal-header">
            <h2>Rating Details</h2>
            <span class="hkota-modal-close">&times;</span>
        </div>
        <div class="hkota-modal-body">
            <div id="rating-details-content"></div>
        </div>
    </div>
</div>
