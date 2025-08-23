<?php
/**
 * Template for user abstract submission form
 * Variables available: $user, $existing_submission, $deadline_info
 */

// Helper function for selected attribute
$get_selected = function($current, $value) {
    return $current === $value ? 'selected="selected"' : '';
};

?>

<div class="hkota-abstract-form-container">
    <h3>Abstract Submission Form</h3>
    
    <?php if ($deadline_info['has_deadline'] && !$deadline_info['is_passed']): ?>
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
    <?php endif; ?>
    
    <?php if ($existing_submission): ?>
        <div class="hkota-notice hkota-notice-info">
            <p>You have already submitted an abstract. You can edit your submission below<?php echo $deadline_info['has_deadline'] ? ' until the deadline' : ''; ?>.</p>
            <?php if (isset($existing_submission->submission_number) && $existing_submission->submission_number): ?>
                <p><strong>Submission Number:</strong> <?php echo esc_html($existing_submission->submission_number); ?></p>
            <?php endif; ?>
            <p><strong>Current Status:</strong> <?php echo esc_html(ucfirst($existing_submission->status)); ?></p>
            <?php if (isset($existing_submission->last_modified) && $existing_submission->last_modified): ?>
                <p><strong>Last Modified:</strong> <?php echo esc_html(date('M j, Y g:i A', strtotime($existing_submission->last_modified))); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <form id="hkota-abstract-form" method="post">
        <?php wp_nonce_field('hkota_abstract_nonce', 'hkota_nonce'); ?>
        
        <h4>Personal Information</h4>
        
        <div class="hkota-form-group">
            <label for="title">Title *</label>
            <select id="title" name="title" required>
                <option value="">Select Title</option>
                <option value="Professor" <?php echo $get_selected($existing_submission ? $existing_submission->title : '', 'Professor'); ?>>Professor</option>
                <option value="Dr." <?php echo $get_selected($existing_submission ? $existing_submission->title : '', 'Dr.'); ?>>Dr.</option>
                <option value="Mr." <?php echo $get_selected($existing_submission ? $existing_submission->title : '', 'Mr.'); ?>>Mr.</option>
                <option value="Ms." <?php echo $get_selected($existing_submission ? $existing_submission->title : '', 'Ms.'); ?>>Ms.</option>
            </select>
        </div>
        
        <div class="hkota-form-group">
            <label for="surname">Surname *</label>
            <input type="text" id="surname" name="surname" 
                   value="<?php echo esc_attr($existing_submission ? $existing_submission->surname : $user->last_name ); ?>" 
                   required>
        </div>
        
        <div class="hkota-form-group">
            <label for="given_name">Given Name *</label>
            <input type="text" id="given_name" name="given_name" 
                   value="<?php echo esc_attr($existing_submission ? $existing_submission->given_name : $user->first_name); ?>" 
                   required>
        </div>
        
        <div class="hkota-form-group">
            <label for="contact_number">Contact Number *</label>
            <input type="tel" id="contact_number" name="contact_number" 
                   value="<?php echo esc_attr($existing_submission ? $existing_submission->contact_number : get_user_meta($user->ID, 'billing_phone', true)); ?>" 
                   required>
        </div>
        
        <div class="hkota-form-group">
            <label for="contact_email">Contact E-mail Address *</label>
            <input type="email" id="contact_email" name="contact_email" 
                   value="<?php echo esc_attr($existing_submission ? $existing_submission->contact_email : $user->user_email); ?>" 
                   required>
        </div>
        
        <div class="hkota-form-group">
            <label for="organization">Organization *</label>
            <input type="text" id="organization" name="organization" 
                   value="<?php echo esc_attr($existing_submission ? $existing_submission->organization : ''); ?>" 
                   required>
        </div>
        
        <h4>Presentation Details</h4>
        
        <div class="hkota-form-group">
            <label for="theme">Theme of Presenting Paper *</label>
            <select id="theme" name="theme" required>
                <option value="">Select Theme</option>
                <option value="Occupational Therapy in Mental Health Practice" <?php echo $get_selected($existing_submission ? $existing_submission->theme : '', 'Occupational Therapy in Mental Health Practice'); ?>>Occupational Therapy in Mental Health Practice</option>
                <option value="Occupational Therapy in Community and Private practice" <?php echo $get_selected($existing_submission ? $existing_submission->theme : '', 'Occupational Therapy in Community and Private practice'); ?>>Occupational Therapy in Community and Private practice</option>
                <option value="Occupational Therapy in School-based Practice" <?php echo $get_selected($existing_submission ? $existing_submission->theme : '', 'Occupational Therapy in School-based Practice'); ?>>Occupational Therapy in School-based Practice</option>
                <option value="Occupational Therapy in Primary Care Practice" <?php echo $get_selected($existing_submission ? $existing_submission->theme : '', 'Occupational Therapy in Primary Care Practice'); ?>>Occupational Therapy in Primary Care Practice</option>
                <option value="Occupational Therapy in Hospital Practice" <?php echo $get_selected($existing_submission ? $existing_submission->theme : '', 'Occupational Therapy in Hospital Practice'); ?>>Occupational Therapy in Hospital Practice</option>
                <option value="Occupational Therapy with Innovative Approaches or New Occupational Therapy Services" <?php echo $get_selected($existing_submission ? $existing_submission->theme : '', 'Occupational Therapy with Innovative Approaches or New Occupational Therapy Services'); ?>>Occupational Therapy with Innovative Approaches or New Occupational Therapy Services</option>
            </select>
        </div>
        
        <div class="hkota-form-group">
            <label for="presentation_preference">Preference of Presentation *</label>
            <select id="presentation_preference" name="presentation_preference" required>
                <option value="">Select Preference</option>
                <option value="Oral Presentation" <?php echo $get_selected($existing_submission ? $existing_submission->presentation_preference : '', 'Oral Presentation'); ?>>Oral Presentation</option>
                <option value="E-poster presentation" <?php echo $get_selected($existing_submission ? $existing_submission->presentation_preference : '', 'E-poster presentation'); ?>>E-poster presentation</option>
            </select>
        </div>
        
        <h4>Abstract Information</h4>
        
        <div class="hkota-form-group">
            <label for="abstract_title">Abstract Title *</label>
            <input type="text" id="abstract_title" name="abstract_title" 
                   value="<?php echo esc_attr($existing_submission ? $existing_submission->abstract_title : ''); ?>" 
                   data-word-limit="20"
                   required>
            <div class="word-count-display">
                <span class="word-count" id="abstract_title_count">0</span>/<span class="word-limit">20</span> words
            </div>
        </div>
        
        <div class="hkota-form-group">
            <label for="authors">Authors *</label>
            <p class="field-instruction">Including presenting author and please indicate the order of author, e.g. Chan TM(1), Wong HM (1), Leung KL(2)... (Maximum 8 authors, separated by commas.)</p>
            <textarea id="authors" name="authors" rows="3" 
                      data-author-limit="8" 
                      required><?php echo esc_textarea($existing_submission ? $existing_submission->authors : ''); ?></textarea>
            <div class="word-count-display">
                <span class="author-count" id="authors_count">0</span>/<span class="author-limit">8</span> authors
            </div>
        </div>
        
        <div class="hkota-form-group">
            <label for="affiliations">Affiliations of the author(s) *</label>
            <p class="field-instruction">e.g. (1) Occupational Therapy Department, Kowloon Hospital</p>
            <textarea id="affiliations" name="affiliations" rows="3" required><?php echo esc_textarea($existing_submission ? $existing_submission->affiliations : ''); ?></textarea>
        </div>
        
        <div class="hkota-form-group">
            <label for="background">Background *</label>
            <textarea id="background" name="background" rows="5" 
                      data-word-limit="500" 
                      required><?php echo esc_textarea($existing_submission ? $existing_submission->background : ''); ?></textarea>
            <div class="word-count-display">
                <span class="word-count" id="background_count">0</span>/<span class="word-limit">500</span> words
            </div>
        </div>
        
        <div class="hkota-form-group">
            <label for="methods">Methods *</label>
            <textarea id="methods" name="methods" rows="5" 
                      data-word-limit="500" 
                      required><?php echo esc_textarea($existing_submission ? $existing_submission->methods : ''); ?></textarea>
            <div class="word-count-display">
                <span class="word-count" id="methods_count">0</span>/<span class="word-limit">500</span> words
            </div>
        </div>
        
        <div class="hkota-form-group">
            <label for="results">Results and Findings *</label>
            <textarea id="results" name="results" rows="5" 
                      data-word-limit="500" 
                      required><?php echo esc_textarea($existing_submission ? $existing_submission->results : ''); ?></textarea>
            <div class="word-count-display">
                <span class="word-count" id="results_count">0</span>/<span class="word-limit">500</span> words
            </div>
        </div>
        
        <div class="hkota-form-group">
            <label for="conclusion">Conclusion *</label>
            <textarea id="conclusion" name="conclusion" rows="5" 
                      data-word-limit="500" 
                      required><?php echo esc_textarea($existing_submission ? $existing_submission->conclusion : ''); ?></textarea>
            <div class="word-count-display">
                <span class="word-count" id="conclusion_count">0</span>/<span class="word-limit">500</span> words
            </div>
        </div>
        
        <div class="hkota-form-group">
            <label>Keywords *</label>
            <small class="keywords-help">Please enter exactly 5 keywords:</small>
            <?php 
            // Parse existing keywords if available
            $existing_keywords = array('', '', '', '', '');
            if ($existing_submission && $existing_submission->keywords) {
                $keywords_array = array_map('trim', explode(',', $existing_submission->keywords));
                for ($i = 0; $i < 5; $i++) {
                    $existing_keywords[$i] = isset($keywords_array[$i]) ? $keywords_array[$i] : '';
                }
            }
            ?>
            <div class="keywords-container">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <div class="keyword-field">
                        <label for="keyword_<?php echo $i; ?>" class="keyword-label">Keyword <?php echo $i; ?>:</label>
                        <input type="text" 
                               id="keyword_<?php echo $i; ?>" 
                               name="keyword_<?php echo $i; ?>" 
                               value="<?php echo esc_attr($existing_keywords[$i-1]); ?>" 
                               required 
                               maxlength="50"
                               placeholder="Enter keyword <?php echo $i; ?>">
                    </div>
                <?php endfor; ?>
            </div>
            <input type="hidden" id="keywords" name="keywords" value="">
        </div>
        
        <div class="hkota-form-group">
            <button type="submit" class="hkota-submit-btn">
                <?php echo $existing_submission ? 'Update Submission' : 'Submit Abstract'; ?>
            </button>
        </div>
    </form>
    
    <div id="hkota-form-messages"></div>
</div>
