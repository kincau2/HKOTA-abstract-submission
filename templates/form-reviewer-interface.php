<?php
/**
 * Template for reviewer interface
 * Variables available: $submissions, $current_user
 */

// Get all submissions for review
if (!isset($submissions)) {
    $submissions = HKOTA_Database::get_all_submissions();
}

$current_user = wp_get_current_user();
?>

<div class="hkota-reviewer-interface">
    <div class="hkota-page-header">
        <h2>HKOTA Occupational Therapy Conference 2025</h2>
        <h3>Abstract Review Interface</h3>
        <p class="reviewer-info">Welcome, <strong><?php echo esc_html($current_user->display_name); ?></strong> | Reviewer Access</p>
    </div>

    <!-- Filters and Search -->
    <div class="hkota-filters-section">
        <div class="hkota-filters-row">
            <div class="filter-group">
                <label for="presentation-filter">Presentation Type:</label>
                <select id="presentation-filter">
                    <option value="">All Types</option>
                    <option value="Oral Presentation">Oral Presentation</option>
                    <option value="E-poster presentation">E-poster presentation</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="status-filter">Review Status:</label>
                <select id="status-filter">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending Review</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="accepted">Accepted</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="theme-filter">Theme:</label>
                <select id="theme-filter">
                    <option value="">All Themes</option>
                    <option value="Occupational Therapy in Mental Health Practice">Occupational Therapy in Mental Health Practice</option>
                    <option value="Occupational Therapy in Community and Private practice">Occupational Therapy in Community and Private practice</option>
                    <option value="Occupational Therapy in School-based Practice">Occupational Therapy in School-based Practice</option>
                    <option value="Occupational Therapy in Primary Care Practice">Occupational Therapy in Primary Care Practice</option>
                    <option value="Occupational Therapy in Hospital Practice">Occupational Therapy in Hospital Practice</option>
                    <option value="Occupational Therapy with Innovative Approaches or New Occupational Therapy Services">Occupational Therapy with Innovative Approaches or New Occupational Therapy Services</option>
                </select>
            </div>
            
            <div class="filter-group search-group">
                <label for="submission-search-input">Search:</label>
                <input type="text" id="submission-search-input" placeholder="Search by title, author, or organization...">
            </div>
            
            <div class="filter-actions">
                <button type="button" id="apply-filters" class="button button-primary">Apply Filters</button>
                <button type="button" id="clear-filters" class="button">Clear All</button>
            </div>
        </div>
    </div>

    <!-- Submissions Table -->
    <div class="hkota-submissions-container">
        <div class="tablenav top">
            <div class="alignleft actions">
                <span class="displaying-num"><?php echo count($submissions); ?> items</span>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped" id="submissions-table">
            <thead>
                <tr>
                    <th class="sortable" data-column="abstract_title">
                        Abstract Title
                        <span class="sorting-indicator"></span>
                    </th>
                    <th class="sortable" data-column="author_name">
                        Author
                        <span class="sorting-indicator"></span>
                    </th>
                    <th class="sortable" data-column="organization">
                        Organization
                        <span class="sorting-indicator"></span>
                    </th>
                    <th class="sortable" data-column="theme">
                        Theme
                        <span class="sorting-indicator"></span>
                    </th>
                    <th class="sortable" data-column="presentation_preference">
                        Presentation Type
                        <span class="sorting-indicator"></span>
                    </th>
                    <th class="sortable" data-column="submission_date">
                        Submitted
                        <span class="sorting-indicator"></span>
                    </th>
                    <th class="sortable" data-column="status">
                        Status
                        <span class="sorting-indicator"></span>
                    </th>
                    <th>Rating</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($submissions)): ?>
                    <tr>
                        <td colspan="9" class="no-submissions">No submissions found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($submissions as $submission): ?>
                        <tr data-submission-id="<?php echo esc_attr($submission->id); ?>">
                            <td class="abstract-title-cell">
                                <strong><?php echo esc_html($submission->abstract_title); ?></strong>
                                <?php if ($submission->submission_number): ?>
                                    <br><small>ID: #<?php echo esc_html($submission->submission_number); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($submission->surname . ', ' . $submission->given_name); ?></td>
                            <td><?php echo esc_html($submission->organization); ?></td>
                            <td><?php echo esc_html($submission->theme); ?></td>
                            <td><?php echo esc_html($submission->presentation_preference); ?></td>
                            <td><?php echo esc_html(date('M j, Y', strtotime($submission->submission_date))); ?></td>
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
                            <td class="rating-cell">
                                <?php 
                                $existing_rating = HKOTA_Database::get_reviewer_rating($current_user->ID, $submission->id);
                                ?>
                                <?php if ($existing_rating): ?>
                                    <span class="rating-display">
                                        Score: <?php echo esc_html(number_format($existing_rating->total_score, 1)); ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="rating-pending">Not Rated</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions-cell">
                                <button type="button" 
                                        class="button button-primary rate-submission" 
                                        data-id="<?php echo esc_attr($submission->id); ?>"
                                        data-title="<?php echo esc_attr($submission->abstract_title); ?>">
                                    <?php echo $existing_rating ? 'Edit Rating' : 'Rate'; ?>
                                </button>
                                
                                <button type="button" 
                                        class="button view-details" 
                                        data-id="<?php echo esc_attr($submission->id); ?>">
                                    View Details
                                </button>
                                
                                <button type="button" 
                                        class="button download-pdf" 
                                        data-id="<?php echo esc_attr($submission->id); ?>">
                                    Download PDF
                                </button>
                                
                                <?php if (!empty($submission->supporting_document)): ?>
                                    <button type="button" 
                                            class="button download-supporting-doc" 
                                            data-id="<?php echo esc_attr($submission->id); ?>">
                                        Download Support Doc
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Rating Modal -->
<div id="rating-modal" class="hkota-modal" style="display: none;">
    <div class="hkota-modal-content rating-modal-content">
        <div class="hkota-modal-header">
            <h2>HKOTA Occupational Therapy Conference 2025</h2>
            <h3>Abstract Assessment Form</h3>
            <span class="hkota-modal-close">&times;</span>
        </div>
        <div class="hkota-modal-body">
            <form id="rating-form">
                <input type="hidden" id="rating-submission-id" name="submission_id" value="">
                
                <div class="rating-header-info">
                    <div class="rating-info-item">
                        <label>Title of Project:</label>
                        <span id="rating-project-title"></span>
                    </div>
                    <div class="rating-info-item">
                        <label>First author:</label>
                        <span id="rating-first-author"></span>
                    </div>
                    <div class="rating-info-item">
                        <label>Reviewer:</label>
                        <span><?php echo esc_html($current_user->last_name).', '.esc_html($current_user->first_name); ?></span>
                    </div>
                </div>

                <div class="rating-table-container">
                    <table class="rating-assessment-table">
                        <thead>
                            <tr>
                                <th class="items-col">Items</th>
                                <th class="points-col">Points to note:</th>
                                <th class="weight-col">Relative Weighting</th>
                                <th class="rating-col">Item Ratings (1 to 5)<br><small>See below</small></th>
                                <th class="score-col">Adjusted Scores<br><small>(weight x rating)</small></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="criterion-name">Innovation</td>
                                <td class="criterion-description">New ideas, cross-over of different field of studies or practices</td>
                                <td class="weight-value">3</td>
                                <td class="rating-input">
                                    <select name="innovation_rating" class="rating-select" data-weight="3">
                                        <option value="">Select...</option>
                                        <option value="1">1 - Inadequate</option>
                                        <option value="2">2 - Wholly satisfactory</option>
                                        <option value="3">3 - Good</option>
                                        <option value="4">4 - Very Good</option>
                                        <option value="5">5 - Excellent</option>
                                    </select>
                                </td>
                                <td class="calculated-score" id="innovation-score">-</td>
                            </tr>
                            <tr>
                                <td class="criterion-name">Scientific Merit</td>
                                <td class="criterion-description">Testing of hypotheses, empirical findings, sources, control groups, control of confounding variables, quantitative methods</td>
                                <td class="weight-value">5</td>
                                <td class="rating-input">
                                    <select name="scientific_merit_rating" class="rating-select" data-weight="5">
                                        <option value="">Select...</option>
                                        <option value="1">1 - Inadequate</option>
                                        <option value="2">2 - Wholly satisfactory</option>
                                        <option value="3">3 - Good</option>
                                        <option value="4">4 - Very Good</option>
                                        <option value="5">5 - Excellent</option>
                                    </select>
                                </td>
                                <td class="calculated-score" id="scientific-merit-score">-</td>
                            </tr>
                            <tr>
                                <td class="criterion-name">Contribution to knowledge development in Occupational Therapy</td>
                                <td class="criterion-description">Discovery of new knowledge, support of new direction of practices</td>
                                <td class="weight-value">6</td>
                                <td class="rating-input">
                                    <select name="knowledge_contribution_rating" class="rating-select" data-weight="6">
                                        <option value="">Select...</option>
                                        <option value="1">1 - Inadequate</option>
                                        <option value="2">2 - Wholly satisfactory</option>
                                        <option value="3">3 - Good</option>
                                        <option value="4">4 - Very Good</option>
                                        <option value="5">5 - Excellent</option>
                                    </select>
                                </td>
                                <td class="calculated-score" id="knowledge-contribution-score">-</td>
                            </tr>
                            <tr>
                                <td class="criterion-name">Application to solve clinical problems faced by Occupational Therapist</td>
                                <td class="criterion-description">Direct relevance to clinical practices: development of measurement, assessment tool for practitioners, or/and continuous improvements</td>
                                <td class="weight-value">6</td>
                                <td class="rating-input">
                                    <select name="clinical_application_rating" class="rating-select" data-weight="6">
                                        <option value="">Select...</option>
                                        <option value="1">1 - Inadequate</option>
                                        <option value="2">2 - Wholly satisfactory</option>
                                        <option value="3">3 - Good</option>
                                        <option value="4">4 - Very Good</option>
                                        <option value="5">5 - Excellent</option>
                                    </select>
                                </td>
                                <td class="calculated-score" id="clinical-application-score">-</td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="4"><strong>Total Scores:</strong></td>
                                <td class="total-score" id="total-score"><strong>0</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rating-guidelines">
                    <h4>Rating Scale:</h4>
                    <ul>
                        <li><strong>"1" – Inadequate</strong></li>
                        <li><strong>"2" – Wholly satisfactory</strong></li>
                        <li><strong>"3" – Good</strong></li>
                        <li><strong>"4" – Very Good</strong></li>
                        <li><strong>"5" – Excellent</strong></li>
                    </ul>
                </div>

                <div class="rating-recommendations">
                    <div class="recommendation-item">
                        <label for="reviewer1-comments"><strong>Reviewer's comments/remarks:</strong></label>
                        <textarea id="reviewer1-comments" name="reviewer_comments" rows="4" placeholder="Enter your detailed comments and feedback..."></textarea>
                    </div> 
                </div>

                <div class="rating-actions">
                    <button type="submit" class="button button-primary">Submit Rating</button>
                    <button type="button" class="button cancel-rating">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Submission Details Modal -->
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

<!-- Loading Overlay -->
<div id="loading-overlay" class="loading-overlay" style="display: none;">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <div class="loading-text">Loading...</div>
    </div>
</div>

<style>
.hkota-reviewer-interface {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
}

.hkota-page-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #0073aa;
}

.hkota-page-header h2 {
    margin: 0 0 10px 0;
    color: #0073aa;
    font-size: 24px;
}

.hkota-page-header h3 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 20px;
}

.reviewer-info {
    margin: 0;
    color: #666;
    font-style: italic;
}

.hkota-filters-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
}

.hkota-filters-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.filter-group select,
.filter-group input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.search-group input {
    min-width: 250px;
}

.filter-actions {
    display: flex;
    gap: 10px;
    flex-direction: column;
}

.filter-actions .button {
    padding: 8px 16px;
    font-size: 14px;
    border-radius: 3px;
    border: 1px solid #0073aa;
    cursor: pointer;
    transition: all 0.2s ease;
}

/* WordPress default button styling */
.filter-actions .button-primary,
.button-primary {
    background: #0073aa;
    border-color: #0073aa;
    color: #fff;
    text-decoration: none;
    text-shadow: 0 -1px 1px #006799, 1px 0 1px #006799, 0 1px 1px #006799, -1px 0 1px #006799;
    box-shadow: 0 1px 0 #006799;
}

.filter-actions .button-primary:hover,
.button-primary:hover {
    background: #005177;
    border-color: #005177;
    color: #fff;
}

.filter-actions .button:not(.button-primary),
.button:not(.button-primary) {
    background: #f6f7f7;
    border-color: #ddd;
    color: #555;
    text-shadow: 0 1px 0 #fff;
    box-shadow: 0 1px 0 #ccc;
}

.filter-actions .button:not(.button-primary):hover,
.button:not(.button-primary):hover {
    background: #fafafa;
    border-color: #999;
    color: #23282d;
}

.hkota-submissions-container {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

#submissions-table {
    margin: 0;
}

#submissions-table th {
    background: #f8f9fa;
    font-weight: 600;
    padding: 12px 8px;
    border-bottom: 2px solid #dee2e6;
}

#submissions-table td {
    padding: 12px 8px;
    vertical-align: top;
    border-bottom: 1px solid #eee;
}

.abstract-title-cell strong {
    color: #0073aa;
    font-size: 14px;
}

.actions-cell {
    white-space: nowrap;
}

.actions-cell .button {
    margin: 2px;
    padding: 4px 8px;
    font-size: 12px;
    border-radius: 3px;
    border: 1px solid #0073aa;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-block;
    vertical-align: top;
}

.actions-cell .button-primary {
    background: #0073aa;
    border-color: #0073aa;
    color: #fff;
    text-shadow: 0 -1px 1px #006799, 1px 0 1px #006799, 0 1px 1px #006799, -1px 0 1px #006799;
    box-shadow: 0 1px 0 #006799;
}

.actions-cell .button-primary:hover {
    background: #005177;
    border-color: #005177;
    color: #fff;
}

.actions-cell .button:not(.button-primary) {
    background: #f6f7f7;
    border-color: #ddd;
    color: #555;
    text-shadow: 0 1px 0 #fff;
    box-shadow: 0 1px 0 #ccc;
}

.actions-cell .button:not(.button-primary):hover {
    background: #fafafa;
    border-color: #999;
    color: #23282d;
}

.rating-cell {
    text-align: center;
}

.rating-display {
    background: #d1edff;
    color: #0c5460;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.rating-pending {
    color: #856404;
    font-style: italic;
    font-size: 12px;
}

/* Rating Modal Styles */
.hkota-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    box-sizing: border-box;
}

.hkota-modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    position: relative;
    animation: modalSlideIn 0.3s ease-out;
    max-width: 90vw;
    width: 800px;
    max-height: 80vh;
    overflow-y: auto;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.rating-modal-content {
    max-width: 95vw;
    width: 1000px;
    max-height: 90vh;
    overflow-y: auto;
}

.hkota-modal-header {
    padding: 20px 30px;
    border-bottom: 2px solid #0073aa;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px 12px 0 0;
    position: relative;
}

.hkota-modal-header h2 {
    margin: 0 0 5px 0;
    color: #0073aa;
    font-size: 22px;
    font-weight: 600;
}

.hkota-modal-header h3 {
    margin: 0;
    color: #333;
    font-size: 18px;
    font-weight: 500;
}

.hkota-modal-close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 28px;
    font-weight: bold;
    color: #666;
    cursor: pointer;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.hkota-modal-close:hover {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    transform: scale(1.1);
}

.hkota-modal-body {
    padding: 30px;
    overflow-y: auto;
    max-height: calc(80vh - 120px); /* Account for header and padding */
}

.rating-header-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
    border-radius: 10px;
    border: 2px solid #e3f2fd;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.rating-info-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.rating-info-item label {
    font-weight: bold;
    color: #0073aa;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.rating-info-item span {
    color: #333;
    font-size: 15px;
    font-weight: 500;
    padding: 8px 12px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
}

.rating-table-container {
    overflow-x: auto;
    margin-bottom: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.rating-assessment-table {
    width: 100%;
    border-collapse: collapse;
    border: none;
    font-size: 14px;
    background: white;
    border-radius: 10px;
    overflow: hidden;
}

.rating-assessment-table th,
.rating-assessment-table td {
    border: 1px solid #dee2e6;
    padding: 12px;
    text-align: left;
    vertical-align: top;
}

.rating-assessment-table th {
    background: linear-gradient(135deg, #0073aa 0%, #005177 100%);
    color: white;
    font-weight: 600;
    text-align: center;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.rating-assessment-table tbody tr {
    transition: background-color 0.2s ease;
}

.rating-assessment-table tbody tr:hover {
    background-color: #f8f9fa;
}

.rating-assessment-table tbody tr:nth-child(even) {
    background-color: #fafafa;
}

.rating-assessment-table tbody tr:nth-child(even):hover {
    background-color: #f0f0f0;
}

.items-col {
    width: 20%;
}

.points-col {
    width: 35%;
}

.weight-col {
    width: 10%;
    text-align: center;
}

.rating-col {
    width: 20%;
    text-align: center;
}

.score-col {
    width: 15%;
    text-align: center;
}

.criterion-name {
    font-weight: bold;
    color: #333;
}

.criterion-description {
    font-size: 13px;
    line-height: 1.4;
}

.weight-value {
    text-align: center;
    font-weight: bold;
    font-size: 18px;
    color: #0073aa;
}

.rating-select {
    width: 100%;
    padding: 8px 12px;
    font-size: 13px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
}

.rating-select:focus {
    outline: none;
    border-color: #0073aa;
    box-shadow: 0 0 0 3px rgba(0, 115, 170, 0.1);
}

.rating-select:hover {
    border-color: #0073aa;
}

.calculated-score {
    text-align: center;
    font-weight: bold;
    font-size: 16px;
    color: #28a745;
    background: #f8fff9;
    border-radius: 4px;
    padding: 4px 8px;
}

.total-row {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
    font-weight: bold;
}

.total-row:hover {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
}

.total-row td {
    border-top: 3px solid #0073aa !important;
    font-size: 15px;
}

.total-score {
    font-size: 20px;
    color: #0073aa;
    font-weight: bold;
    text-align: center;
}

.rating-guidelines {
    margin: 20px 0;
    padding: 15px;
    background: #e7f3ff;
    border-radius: 8px;
    border: 1px solid #0073aa;
}

.rating-guidelines h4 {
    margin: 0 0 10px 0;
    color: #0073aa;
}

.rating-guidelines ul {
    margin: 0;
    padding-left: 20px;
}

.rating-guidelines li {
    margin: 5px 0;
    font-size: 14px;
}

.rating-recommendations {
    margin: 20px 0;
}

.recommendation-item {
    margin: 15px 0;
}

.recommendation-item label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
}

.recommendation-item textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: inherit;
    resize: vertical;
}

.recommendation-item select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.rating-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
    padding-top: 25px;
    border-top: 2px solid #e9ecef;
}

.rating-actions .button {
    padding: 8px 20px;
    font-size: 13px;
    font-weight: 400;
    border-radius: 3px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid #0073aa;
    text-decoration: none;
    display: inline-block;
    vertical-align: top;
    line-height: 1.4;
}

.rating-actions .button-primary {
    background: #0073aa;
    border-color: #0073aa;
    color: #fff;
    text-shadow: 0 -1px 1px #006799, 1px 0 1px #006799, 0 1px 1px #006799, -1px 0 1px #006799;
    box-shadow: 0 1px 0 #006799;
}

.rating-actions .button-primary:hover {
    background: #005177;
    border-color: #005177;
    color: #fff;
}

.rating-actions .cancel-rating {
    background: #f6f7f7;
    border-color: #ddd;
    color: #555;
    text-shadow: 0 1px 0 #fff;
    box-shadow: 0 1px 0 #ccc;
}

.rating-actions .cancel-rating:hover {
    background: #fafafa;
    border-color: #999;
    color: #23282d;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hkota-filters-row {
        grid-template-columns: 1fr;
    }
    
    .rating-header-info {
        grid-template-columns: 1fr;
    }
    
    .rating-modal-content {
        max-width: 95vw;
        margin: 10px;
    }
    
    .hkota-modal-content {
        max-width: 95vw;
        width: 95vw;
        max-height: 90vh;
        margin: 5vh 2.5vw;
    }
    
    .hkota-modal-body {
        padding: 20px;
        max-height: calc(90vh - 100px);
    }
    
    .filter-actions {
        flex-direction: row;
    }
}

/* Sortable table headers */
.sortable {
    cursor: pointer;
    user-select: none;
    position: relative;
}

.sortable:hover {
    background-color: #f0f0f0;
}

.sorting-indicator {
    margin-left: 5px;
    font-size: 12px;
    color: #666;
}

.sorting-indicator.sorted-asc::after {
    content: '↑';
    color: #0073aa;
}

.sorting-indicator.sorted-desc::after {
    content: '↓';
    color: #0073aa;
}

/* Loading states */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    z-index: 15000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.loading-content {
    text-align: center;
    padding: 30px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    border: 2px solid #0073aa;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #0073aa;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

.loading-text {
    font-size: 16px;
    color: #333;
    font-weight: 500;
    margin: 0;
}

.button.loading {
    opacity: 0.7;
    pointer-events: none;
    position: relative;
}

.button.loading::before {
    content: "";
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 14px;
    height: 14px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Custom scrollbar styling for modal content */
.hkota-modal-content::-webkit-scrollbar,
.hkota-modal-body::-webkit-scrollbar,
.text-content::-webkit-scrollbar {
    width: 8px;
}

.hkota-modal-content::-webkit-scrollbar-track,
.hkota-modal-body::-webkit-scrollbar-track,
.text-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.hkota-modal-content::-webkit-scrollbar-thumb,
.hkota-modal-body::-webkit-scrollbar-thumb,
.text-content::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.hkota-modal-content::-webkit-scrollbar-thumb:hover,
.hkota-modal-body::-webkit-scrollbar-thumb:hover,
.text-content::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>