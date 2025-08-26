<?php
/**
 * Template for rating details modal
 * Variables available: $submission, $ratings, $avg_rating
 */
?>

<div class="rating-details-modal">
    <div class="submission-info-section">
        <h4>Submission Information</h4>
        <div class="submission-info-grid">
            <div class="info-item">
                <label>Submission #:</label>
                <span><?php echo esc_html($submission->submission_number ?? 'N/A'); ?></span>
            </div>
            <div class="info-item">
                <label>Title:</label>
                <span><?php echo esc_html($submission->abstract_title); ?></span>
            </div>
            <div class="info-item">
                <label>Author:</label>
                <span><?php echo esc_html($submission->title . ' ' . $submission->surname . ', ' . $submission->given_name); ?></span>
            </div>
        </div>
    </div>

    <?php if ($avg_rating && $avg_rating->total_ratings > 0): ?>
        <div class="average-rating-section">
            <h4>Overall Rating Summary</h4>
            <div class="avg-rating-grid">
                <div class="avg-item">
                    <label>Total Reviews:</label>
                    <span class="avg-value"><?php echo $avg_rating->total_ratings; ?></span>
                </div>
                <div class="avg-item">
                    <label>Average Score:</label>
                    <span class="avg-value highlight"><?php echo number_format($avg_rating->average_score, 1); ?>%</span>
                </div>
            </div>
            
            <div class="criteria-averages">
                <h5>Average Scores by Criteria:</h5>
                <div class="criteria-grid">
                    <div class="criteria-item">
                        <label>Innovation:</label>
                        <span><?php echo number_format($avg_rating->avg_innovation, 1); ?>/5 (Weight: 3)</span>
                    </div>
                    <div class="criteria-item">
                        <label>Scientific Merit:</label>
                        <span><?php echo number_format($avg_rating->avg_scientific_merit, 1); ?>/5 (Weight: 5)</span>
                    </div>
                    <div class="criteria-item">
                        <label>Knowledge Contribution:</label>
                        <span><?php echo number_format($avg_rating->avg_knowledge_contribution, 1); ?>/5 (Weight: 6)</span>
                    </div>
                    <div class="criteria-item">
                        <label>Clinical Application:</label>
                        <span><?php echo number_format($avg_rating->avg_clinical_application, 1); ?>/5 (Weight: 6)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="individual-ratings-section">
            <h4>Individual Reviews</h4>
            <?php foreach ($ratings as $rating): ?>
                <div class="rating-card">
                    <div class="rating-header">
                        <div class="reviewer-info">
                            <div class="reviewer-details">
                                <?php 
                                // Build reviewer name display
                                $reviewer_display = '';
                                if (!empty($rating->reviewer_last_name) || !empty($rating->reviewer_first_name)) {
                                    $reviewer_display = trim($rating->reviewer_last_name . ', ' . $rating->reviewer_first_name);
                                    // Remove leading/trailing commas if one name is missing
                                    $reviewer_display = trim($reviewer_display, ', ');
                                } else {
                                    $reviewer_display = $rating->reviewer_name ?? 'Unknown Reviewer';
                                }
                                ?>
                                <strong><?php echo esc_html($reviewer_display); ?></strong>
                                <?php if (!empty($rating->reviewer_email)): ?>
                                    <div class="reviewer-email"><?php echo esc_html($rating->reviewer_email); ?></div>
                                <?php endif; ?>
                            </div>
                            <span class="rating-score-badge"><?php echo number_format($rating->total_score, 1); ?>%</span>
                        </div>
                        <div class="rating-dates">
                            <small>
                                Reviewed: <?php echo date('M j, Y g:i A', strtotime($rating->rating_date)); ?>
                                <?php if ($rating->last_modified && $rating->last_modified !== $rating->rating_date): ?>
                                    <br>Modified: <?php echo date('M j, Y g:i A', strtotime($rating->last_modified)); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="rating-criteria">
                        <h6>Individual Scores:</h6>
                        <div class="criteria-scores">
                            <div class="score-item">
                                <label>Innovation:</label>
                                <span><?php echo $rating->innovation_rating; ?>/5 (<?php echo $rating->innovation_rating * 3; ?> pts)</span>
                            </div>
                            <div class="score-item">
                                <label>Scientific Merit:</label>
                                <span><?php echo $rating->scientific_merit_rating; ?>/5 (<?php echo $rating->scientific_merit_rating * 5; ?> pts)</span>
                            </div>
                            <div class="score-item">
                                <label>Knowledge Contribution:</label>
                                <span><?php echo $rating->knowledge_contribution_rating; ?>/5 (<?php echo $rating->knowledge_contribution_rating * 6; ?> pts)</span>
                            </div>
                            <div class="score-item">
                                <label>Clinical Application:</label>
                                <span><?php echo $rating->clinical_application_rating; ?>/5 (<?php echo $rating->clinical_application_rating * 6; ?> pts)</span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($rating->reviewer_comments)): ?>
                        <div class="rating-comments">
                            <h6>Reviewer Comments:</h6>
                            <div class="comments-content">
                                <?php echo nl2br(esc_html($rating->reviewer_comments)); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <div class="no-ratings-section">
            <div class="no-ratings-message">
                <h4>No Reviews Yet</h4>
                <p>This submission has not been reviewed by any reviewers yet.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.rating-details-modal {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    line-height: 1.5;
}

.submission-info-section,
.average-rating-section,
.individual-ratings-section,
.no-ratings-section {
    margin-bottom: 25px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.submission-info-section h4,
.average-rating-section h4,
.individual-ratings-section h4,
.no-ratings-section h4 {
    margin: 0 0 15px 0;
    color: #0073aa;
    font-size: 18px;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 8px;
}

.submission-info-grid,
.avg-rating-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.info-item,
.avg-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-item label,
.avg-item label {
    font-weight: bold;
    color: #555;
    font-size: 14px;
}

.info-item span,
.avg-item span {
    color: #333;
    font-size: 15px;
}

.avg-value.highlight {
    font-size: 24px;
    font-weight: bold;
    color: #0073aa;
}

.criteria-averages {
    margin-top: 20px;
}

.criteria-averages h5 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 16px;
}

.criteria-grid,
.criteria-scores {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 10px;
}

.criteria-item,
.score-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: white;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.criteria-item label,
.score-item label {
    font-weight: 600;
    color: #333;
}

.criteria-item span,
.score-item span {
    font-weight: bold;
    color: #0073aa;
}

.rating-card {
    margin-bottom: 20px;
    padding: 20px;
    background: white;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.rating-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.reviewer-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.reviewer-details {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.reviewer-email {
    font-size: 12px;
    color: #666;
    font-weight: normal;
    font-style: italic;
}

.rating-score-badge {
    background: #0073aa;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: bold;
}

.rating-dates {
    text-align: right;
    color: #666;
}

.rating-criteria h6,
.rating-comments h6 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 16px;
    font-weight: 600;
}

.comments-content {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 4px;
    border: 1px solid #e9ecef;
    color: #333;
    line-height: 1.6;
}

.no-ratings-message {
    text-align: center;
    padding: 40px 20px;
}

.no-ratings-message h4 {
    border: none;
    margin-bottom: 10px;
    color: #666;
}

.no-ratings-message p {
    color: #888;
    font-size: 16px;
    margin: 0;
}

@media (max-width: 768px) {
    .submission-info-grid,
    .avg-rating-grid,
    .criteria-grid,
    .criteria-scores {
        grid-template-columns: 1fr;
    }
    
    .rating-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .rating-dates {
        text-align: left;
    }
}
</style>
