jQuery(document).ready(function($) {
    
    // Handle multiple submissions interface
    handleMultipleSubmissions();
    
    // Handle reviewer interface if present
    if ($('.hkota-reviewer-interface').length > 0) {
        handleReviewerInterface();
    }
    
    function handleReviewerInterface() {
        // Initialize table functionality
        initTableSorting();
        initTableFiltering();
        initRatingModal();
        
        // Handle rate submission button
        $(document).on('click', '.rate-submission', function(e) {
            e.preventDefault();
            var $button = $(this);
            var submissionId = $button.data('id');
            var submissionTitle = $button.data('title');
            var originalText = $button.text();
            
            // Extract author name from the table row
            var $row = $button.closest('tr');
            var authorName = $row.find('td:nth-child(2)').text().trim();
            
            // Add loading state to button
            $button.addClass('loading').prop('disabled', true).text('Loading...');
            
            // Show loading overlay
            showLoadingOverlay('Loading submission data...');
            
            // Load existing rating data if available and open modal
            openRatingModal(submissionId, submissionTitle, authorName, $button, originalText);
        });        // Handle view details button (reuse existing functionality)
        $(document).on('click', '.view-details', function(e) {
            e.preventDefault();
            var submissionId = $(this).data('id');
            var $button = $(this);
            var originalText = $button.text();
            
            $button.addClass('loading').prop('disabled', true).text('Loading...');
            
            $.post(hkota_ajax.ajax_url, {
                action: 'get_submission_details',
                submission_id: submissionId,
                nonce: hkota_ajax.nonce
            })
            .done(function(response) {
                if (response.success) {
                    showSubmissionDetailsModal(response.data.html);
                } else {
                    alert('Error: ' + (response.data || 'Unknown error'));
                }
            })
            .fail(function(xhr, status, error) {
                console.log('AJAX failed:', xhr, status, error);
                alert('Failed to load submission details. Please try again.');
            })
            .always(function() {
                $button.removeClass('loading').prop('disabled', false).text(originalText);
            });
        });
        
        // Handle PDF download (reuse existing functionality)
        $(document).on('click', '.download-pdf', function(e) {
            e.preventDefault();
            var submissionId = $(this).data('id');
            var $button = $(this);
            var originalText = $button.text();
            
            $button.addClass('loading').prop('disabled', true).text('Downloading...');
            var downloadUrl = hkota_ajax.ajax_url + '?action=download_submission_pdf&submission_id=' + submissionId + '&nonce=' + hkota_ajax.nonce;
            window.open(downloadUrl, '_blank');
            
            setTimeout(function() {
                $button.removeClass('loading').prop('disabled', false).text(originalText);
            }, 1000);
        });
        
        // Handle supporting document download
        $(document).on('click', '.download-supporting-doc', function(e) {
            e.preventDefault();
            var submissionId = $(this).data('id');
            var $button = $(this);
            var originalText = $button.text();
            
            $button.addClass('loading').prop('disabled', true).text('Downloading...');
            var downloadUrl = hkota_ajax.ajax_url + '?action=download_supporting_document&submission_id=' + submissionId + '&nonce=' + hkota_ajax.nonce;
            window.open(downloadUrl, '_blank');
            
            setTimeout(function() {
                $button.removeClass('loading').prop('disabled', false).text(originalText);
            }, 1000);
        });
    }
    
    function openRatingModal(submissionId, submissionTitle, authorName, $button, originalText) {
        // Populate rating form
        $('#rating-submission-id').val(submissionId);
        $('#rating-project-title').text(submissionTitle);
        $('#rating-first-author').text(authorName);
        
        // Load existing rating if available
        loadExistingRating(submissionId);
        
        // Hide loading overlay and show modal
        hideLoadingOverlay();
        $('#rating-modal').show();
        
        // Remove loading state from button
        if ($button) {
            $button.removeClass('loading').prop('disabled', false);
            if (originalText) {
                $button.text(originalText);
            }
        }
    }
    
    function loadExistingRating(submissionId) {
        // Load existing rating data if available
        $.post(hkota_ajax.ajax_url, {
            action: 'get_reviewer_rating',
            submission_id: submissionId,
            nonce: hkota_ajax.nonce
        })
        .done(function(response) {
            if (response.success && response.data) {
                var rating = response.data;
                
                // Populate rating form with existing data
                $('[name="innovation_rating"]').val(rating.innovation_rating);
                $('[name="scientific_merit_rating"]').val(rating.scientific_merit_rating);
                $('[name="knowledge_contribution_rating"]').val(rating.knowledge_contribution_rating);
                $('[name="clinical_application_rating"]').val(rating.clinical_application_rating);
                $('[name="reviewer_comments"]').val(rating.reviewer_comments);
                
                // Recalculate scores
                calculateRatings();
            }
        })
        .fail(function() {
            console.log('No existing rating found or failed to load.');
        });
    }
    
    function initRatingModal() {
        // Handle rating calculation
        $(document).on('change', '.rating-select', function() {
            calculateRatings();
        });
        
        // Handle rating form submission
        $(document).on('submit', '#rating-form', function(e) {
            e.preventDefault();
            submitRating();
        });
        
        // Handle modal close
        $(document).on('click', '.cancel-rating, .hkota-modal-close', function() {
            $('.hkota-modal').hide();
        });
        
        // Close modal when clicking outside
        $(document).on('click', '.hkota-modal', function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });
    }
    
    function calculateRatings() {
        var totalScore = 0;
        var totalWeight = 20; // 3 + 5 + 6 + 6
        var allRated = true;
        
        $('.rating-select').each(function() {
            var rating = parseFloat($(this).val());
            var weight = parseFloat($(this).data('weight'));
            
            if (rating && weight) {
                var score = rating * weight;
                var criterionId = $(this).attr('name').replace('_rating', '').replace('_', '-');
                $('#' + criterionId + '-score').text(score.toFixed(0));
                totalScore += score;
            } else {
                allRated = false;
                var criterionId = $(this).attr('name').replace('_rating', '').replace('_', '-');
                $('#' + criterionId + '-score').text('-');
            }
        });
        
        if (allRated) {
            var percentage = (totalScore / (totalWeight * 5)) * 100;
            $('#total-score').html('<strong>' + percentage.toFixed(0) + '/100</strong>');
            $('#total-rating').text(totalScore.toFixed(0));
        } else {
            $('#total-score').html('<strong>0</strong>');
            $('#total-rating').text('-');
        }
    }
    
    function submitRating() {
        var formData = {
            action: 'submit_reviewer_rating',
            submission_id: $('#rating-submission-id').val(),
            innovation_rating: $('[name="innovation_rating"]').val(),
            scientific_merit_rating: $('[name="scientific_merit_rating"]').val(),
            knowledge_contribution_rating: $('[name="knowledge_contribution_rating"]').val(),
            clinical_application_rating: $('[name="clinical_application_rating"]').val(),
            reviewer_comments: $('[name="reviewer_comments"]').val(),
            nonce: hkota_ajax.nonce
        };
        
        var allRated = true;
        $('.rating-select').each(function() {
            if (!$(this).val()) {
                allRated = false;
            }
        });
        
        if (!allRated) {
            alert('Please rate all criteria before submitting.');
            return;
        }
        
        // Show loading state
        var $submitBtn = $('#rating-form button[type="submit"]');
        var originalText = $submitBtn.text();
        $submitBtn.addClass('loading').prop('disabled', true).text('Submitting...');
        
        // Submit rating
        $.post(hkota_ajax.ajax_url, formData)
        .done(function(response) {
            console.log('Response received:', response);
            if (response.success) {
                alert('Rating submitted successfully!');
                $('#rating-modal').hide();
                location.reload(); // Refresh to show updated rating
            } else {
                console.log('Error response:', response);
                alert('Error: ' + (response.data || 'Unknown error'));
            }
        })
        .fail(function(xhr, status, error) {
            console.log('AJAX failed:', xhr, status, error);
            alert('Failed to submit rating. Please try again.');
        })
        .always(function() {
            $submitBtn.removeClass('loading').prop('disabled', false).text(originalText);
        });
    }
    
    function showSubmissionDetailsModal(htmlContent) {
        var modal = $('#submission-modal');
        var modalBody = modal.find('#submission-details-content');
        modalBody.html(htmlContent);
        modal.show();
    }
    
    // Table functionality
    function initTableSorting() {
        $('.sortable').on('click', function() {
            var $header = $(this);
            var $indicator = $header.find('.sorting-indicator');
            var column = $header.data('column');
            var $table = $('#submissions-table');
            var $tbody = $table.find('tbody');
            var rows = $tbody.find('tr').toArray();
            
            // Determine sort direction - check the indicator element for current sort state
            var direction = 'asc';
            if ($indicator.hasClass('sorted-asc')) {
                direction = 'desc';
            } else if ($indicator.hasClass('sorted-desc')) {
                direction = 'asc';
            }
            
            // Clear all sort indicators
            $('.sorting-indicator').removeClass('sorted-asc sorted-desc');
            
            // Set current sort indicator
            $indicator.addClass('sorted-' + direction);
            
            // Sort rows
            rows.sort(function(a, b) {
                var aVal = getCellValue(a, column);
                var bVal = getCellValue(b, column);
                
                // Handle dates
                if (column === 'submission_date') {
                    aVal = new Date(aVal).getTime();
                    bVal = new Date(bVal).getTime();
                }
                
                // Compare values
                if (aVal < bVal) return direction === 'asc' ? -1 : 1;
                if (aVal > bVal) return direction === 'asc' ? 1 : -1;
                return 0;
            });
            
            // Rebuild table
            $tbody.empty().append(rows);
        });
    }
    
    function getCellValue(row, column) {
        var $row = $(row);
        switch(column) {
            case 'abstract_title':
                return $row.find('td:nth-child(1) strong').text().toLowerCase();
            case 'author_name':
                return $row.find('td:nth-child(2)').text().toLowerCase();
            case 'organization':
                return $row.find('td:nth-child(3)').text().toLowerCase();
            case 'theme':
                return $row.find('td:nth-child(4)').text().toLowerCase();
            case 'presentation_preference':
                return $row.find('td:nth-child(5)').text().toLowerCase();
            case 'submission_date':
                return $row.find('td:nth-child(6)').text();
            case 'status':
                return $row.find('td:nth-child(7)').text().toLowerCase();
            default:
                return '';
        }
    }
    
    function initTableFiltering() {
        // Apply filters button
        $('#apply-filters').on('click', function() {
            applyFilters();
        });
        
        // Clear filters button
        $('#clear-filters').on('click', function() {
            clearFilters();
        });
        
        // Real-time search
        $('#submission-search-input').on('input', function() {
            var searchTerm = $(this).val().trim();
            if (searchTerm.length === 0 || searchTerm.length >= 2) {
                applyFilters();
            }
        });
        
        // Apply filters on Enter key
        $('#submission-search-input').on('keypress', function(e) {
            if (e.which === 13) {
                applyFilters();
            }
        });
    }
    
    function applyFilters() {
        var presentationFilter = $('#presentation-filter').val().toLowerCase();
        var statusFilter = $('#status-filter').val().toLowerCase();
        var themeFilter = $('#theme-filter').val().toLowerCase();
        var searchTerm = $('#submission-search-input').val().toLowerCase().trim();
        
        var visibleRows = 0;
        
        $('#submissions-table tbody tr').each(function() {
            var $row = $(this);
            var show = true;
            
            // Skip if this is the "no submissions" row
            if ($row.find('.no-submissions').length > 0) {
                return;
            }
            
            // Get row data
            var presentation = $row.find('td:nth-child(5)').text().toLowerCase();
            var status = $row.find('td:nth-child(7)').text().toLowerCase();
            var theme = $row.find('td:nth-child(4)').text().toLowerCase();
            
            // Presentation filter
            if (presentationFilter && presentation.indexOf(presentationFilter) === -1) {
                show = false;
            }
            
            // Status filter
            if (statusFilter) {
                if (statusFilter === 'pending' && status.indexOf('pending') === -1) {
                    show = false;
                } else if (statusFilter === 'reviewed' && status.indexOf('awaiting') === -1 && status.indexOf('completed') === -1) {
                    show = false;
                } else if (statusFilter === 'accepted' && status.indexOf('awaiting') === -1 && status.indexOf('completed') === -1) {
                    show = false;
                } else if (statusFilter === 'rejected' && status.indexOf('rejected') === -1) {
                    show = false;
                }
            }
            
            // Theme filter
            if (themeFilter && theme.indexOf(themeFilter) === -1) {
                show = false;
            }
            
            // Search filter
            if (searchTerm) {
                var searchableText = [
                    $row.find('td:nth-child(1)').text(), // title
                    $row.find('td:nth-child(2)').text(), // author
                    $row.find('td:nth-child(3)').text()  // organization
                ].join(' ').toLowerCase();
                
                if (searchableText.indexOf(searchTerm) === -1) {
                    show = false;
                }
            }
            
            if (show) {
                $row.show();
                visibleRows++;
            } else {
                $row.hide();
            }
        });
        
        // Update count
        updateRowCount(visibleRows);
    }
    
    function clearFilters() {
        $('#presentation-filter').val('');
        $('#status-filter').val('');
        $('#theme-filter').val('');
        $('#submission-search-input').val('');
        
        // Show all rows
        $('#submissions-table tbody tr').show();
        
        // Update count
        var totalRows = $('#submissions-table tbody tr').length;
        // Subtract 1 if there's a "no submissions" row
        if ($('#submissions-table tbody tr .no-submissions').length > 0) {
            totalRows = 0;
        }
        updateRowCount(totalRows);
    }
    
    function updateRowCount(count) {
        var totalRows = $('#submissions-table tbody tr').length;
        // Subtract 1 if there's a "no submissions" row
        if ($('#submissions-table tbody tr .no-submissions').length > 0) {
            totalRows = 0;
        }
        
        var countText = count + ' items';
        if (count !== totalRows && totalRows > 0) {
            countText += ' (filtered from ' + totalRows + ' total)';
        }
        $('.displaying-num').text(countText);
    }
    
    function handleMultipleSubmissions() {
        // Add new submission button
        $(document).on('click', '#add-new-submission', function(e) {
            e.preventDefault();
            var $button = $(this);
            
            // Add loading state to button
            $button.addClass('loading');
            showLoadingOverlay('Loading submission form...');
            
            loadSubmissionForm();
        });
        
        // Edit submission button
        $(document).on('click', '.edit-submission', function(e) {
            e.preventDefault();
            var submissionId = $(this).data('submission-id');
            var $button = $(this);
            
            // Add loading state to button
            $button.addClass('loading');
            showLoadingOverlay('Loading submission for editing...');
            
            loadSubmissionForm(submissionId);
        });
        
        // Delete submission button
        $(document).on('click', '.delete-submission', function(e) {
            e.preventDefault();
            var submissionId = $(this).data('submission-id');
            var title = $(this).data('title');
            var $button = $(this);
            
            if (confirm('Are you sure you want to delete the submission "' + title + '"? This action cannot be undone.')) {
                $button.addClass('loading');
                showLoadingOverlay('Deleting submission...');
                deleteSubmission(submissionId);
            }
        });
        
        // View submission details
        $(document).on('click', '.view-submission', function(e) {
            e.preventDefault();
            var submissionId = $(this).data('submission-id');
            var $button = $(this);
            
            // Add loading state to button
            $button.addClass('loading');
            showLoadingOverlay('Loading submission details...');
            
            viewSubmissionDetails(submissionId);
        });
        
        // Upload document button
        $(document).on('click', '.upload-document', function(e) {
            e.preventDefault();
            var submissionId = $(this).data('submission-id');
            showUploadModal(submissionId);
        });
        
        // Cancel form button
        $(document).on('click', '.cancel-form', function(e) {
            e.preventDefault();
            hideLoadingOverlay(); // Clear any loading states
            location.reload();
        });
        
        // Modal close buttons
        $(document).on('click', '.hkota-modal-close, .cancel-upload', function() {
            $('.hkota-modal').hide();
            hideLoadingOverlay(); // Clear any loading states
        });
        
        // Close modal when clicking outside
        $(document).on('click', '.hkota-modal', function(e) {
            if (e.target === this) {
                $(this).hide();
                hideLoadingOverlay(); // Clear any loading states
            }
        });
        
        // File upload form
        $(document).on('submit', '#file-upload-form', function(e) {
            e.preventDefault();
            handleFileUpload();
        });
    }
    
    // Loading overlay functions
    function showLoadingOverlay(message) {
        var loadingText = message || 'Loading...';
        $('#loading-overlay .loading-text').text(loadingText);
        $('#loading-overlay').show();
    }
    
    function hideLoadingOverlay() {
        $('#loading-overlay').hide();
        // Remove loading state from all buttons
        $('.hkota-btn').removeClass('loading');
    }
    
    function loadSubmissionForm(submissionId) {
        $.post(hkota_ajax.ajax_url, {
            action: 'get_submission_form',
            submission_id: submissionId || 0,
            nonce: hkota_ajax.nonce
        }, function(response) {
            hideLoadingOverlay();
            
            if (response.success) {
                var container = $('.hkota-abstract-form-container').first();
                container.html(response.data.html);
                initializeForm();
                $('html, body').animate({ scrollTop: 0 }, 500);
            } else {
                alert('Error: ' + response.data);
            }
        }).fail(function() {
            hideLoadingOverlay();
            alert('Failed to load submission form. Please try again.');
        });
    }
    
    function deleteSubmission(submissionId) {
        $.post(hkota_ajax.ajax_url, {
            action: 'delete_user_submission',
            submission_id: submissionId,
            nonce: hkota_ajax.nonce
        }, function(response) {
            hideLoadingOverlay();
            
            if (response.success) {
                // Show success message briefly before reload
                showLoadingOverlay('Submission deleted successfully. Refreshing...');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                alert('Error: ' + response.data);
            }
        }).fail(function() {
            hideLoadingOverlay();
            alert('Failed to delete submission. Please try again.');
        });
    }
    
    function viewSubmissionDetails(submissionId) {
        $.post(hkota_ajax.ajax_url, {
            action: 'get_submission_details',
            submission_id: submissionId,
            nonce: hkota_ajax.nonce
        }, function(response) {
            hideLoadingOverlay();
            
            if (response.success) {
                $('#submission-details-content').html(response.data.html);
                $('#submission-modal').show();
            } else {
                alert('Error: ' + response.data);
            }
        }).fail(function() {
            hideLoadingOverlay();
            alert('Failed to load submission details. Please try again.');
        });
    }
    
    function showUploadModal(submissionId) {
        $('#upload-submission-id').val(submissionId);
        $('#upload-modal').show();
    }
    
    function handleFileUpload() {
        var formData = new FormData();
        var fileInput = $('#supporting_document')[0];
        var submissionId = $('#upload-submission-id').val();
        
        if (!fileInput.files[0]) {
            alert('Please select a file to upload.');
            return;
        }
        
        formData.append('action', 'upload_supporting_document');
        formData.append('supporting_document', fileInput.files[0]);
        formData.append('submission_id', submissionId);
        formData.append('file_upload_nonce', $('[name="file_upload_nonce"]').val());
        
        // Show progress
        $('#upload-progress').show();
        $('.progress-fill').css('width', '0%');
        
        $.ajax({
            url: hkota_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total * 100;
                        $('.progress-fill').css('width', percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                $('#upload-progress').hide();
                if (response.success) {
                    alert(response.data);
                    $('#upload-modal').hide();
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                $('#upload-progress').hide();
                alert('Upload failed. Please try again.');
            }
        });
    }
    
    function initializeForm() {
        // Only initialize form functionality if we're on a page with the submission form
        if ($('#hkota-abstract-form').length > 0) {
            // Clear any existing submission state for fresh start
            $('#hkota-abstract-form').removeData('submitting');
            
            // Re-initialize form functionality for loaded form
            updateKeywordsField();
            initializeWordCounting();
            initializeKeywordValidation();
            initializeFormSubmission();
        }
    }
    
    // Initialize form functionality on page load
    initializeForm();
    
    // Handle keyword fields
    function updateKeywordsField() {
        var keywords = [];
        for (var i = 1; i <= 5; i++) {
            var $keywordField = $('#keyword_' + i);
            if ($keywordField.length > 0) {
                var keyword = $keywordField.val();
                if (keyword && keyword.trim && keyword.trim()) {
                    keywords.push(keyword.trim());
                }
            }
        }
        var $keywordsField = $('#keywords');
        if ($keywordsField.length > 0) {
            $keywordsField.val(keywords.join(', '));
        }
    }
    
    // Initialize keywords on page load
    function initializeKeywordValidation() {
        // Only initialize if keyword fields exist
        if ($('#keyword_1').length === 0) {
            return;
        }
        
        updateKeywordsField();
        
        // Update hidden field when any keyword input changes
        $(document).off('input blur', '[name^="keyword_"]').on('input blur', '[name^="keyword_"]', function() {
            updateKeywordsField();
            validateKeywords();
        });
        
        // Handle Enter key to move to next field
        $(document).off('keypress', '[name^="keyword_"]').on('keypress', '[name^="keyword_"]', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                var currentNum = parseInt($(this).attr('name').split('_')[1]);
                if (currentNum < 5) {
                    $('#keyword_' + (currentNum + 1)).focus();
                }
            }
        });
    }
    
    // Validate keywords in real-time
    function validateKeywords() {
        // Only validate if keyword fields exist
        if ($('#keyword_1').length === 0) {
            return true;
        }
        
        var filledKeywords = 0;
        var requiredKeywords = 0; // Count filled required keywords (1-3)
        var allValid = true;
        
        for (var i = 1; i <= 5; i++) {
            var $input = $('#keyword_' + i);
            if ($input.length === 0) continue;
            
            var value = $input.val();
            if (value && value.trim && value.trim()) {
                value = value.trim();
                filledKeywords++;
                
                // Count required keywords (1-3)
                if (i <= 3) {
                    requiredKeywords++;
                }
                
                // Check for duplicates
                var isDuplicate = false;
                for (var j = 1; j <= 5; j++) {
                    if (i !== j) {
                        var $otherInput = $('#keyword_' + j);
                        if ($otherInput.length > 0) {
                            var otherValue = $otherInput.val();
                            if (otherValue && otherValue.trim && otherValue.trim().toLowerCase() === value.toLowerCase()) {
                                isDuplicate = true;
                                break;
                            }
                        }
                    }
                }
                
                if (isDuplicate) {
                    $input.addClass('duplicate-keyword');
                    allValid = false;
                } else {
                    $input.removeClass('duplicate-keyword');
                }
            }
        }
        
        // Update submit button state
        var $submitBtn = $('.hkota-submit-btn');
        
        // Remove existing validation message
        $('.keywords-validation-message').remove();
        
        if (requiredKeywords === 3 && allValid) {
            $submitBtn.removeClass('keywords-disabled');
        } else {
            $submitBtn.addClass('keywords-disabled');
            
            // Show validation message
            var message = '';
            if (requiredKeywords < 3) {
                message = 'Please enter at least the first 3 keywords (' + requiredKeywords + '/3 completed).';
            } else if (!allValid) {
                message = 'Please ensure all keywords are unique.';
            }
            
            $('.keywords-container').after('<div class="keywords-validation-message" style="color: #dc3232; font-size: 12px; margin-top: 5px;">' + message + '</div>');
        }
    }
    
    // Form validation
    function validateForm() {
        var isValid = true;
        var requiredFields = $('#hkota-abstract-form input[required], #hkota-abstract-form textarea[required]');
        
        requiredFields.each(function() {
            var field = $(this);
            if (!field.val().trim()) {
                field.addClass('error');
                isValid = false;
            } else {
                field.removeClass('error');
            }
        });
        
        return isValid;
    }
    
    // Real-time validation
    $('#hkota-abstract-form input, #hkota-abstract-form textarea').on('blur', function() {
        var field = $(this);
        if (field.attr('required') && !field.val().trim()) {
            field.addClass('error');
        } else {
            field.removeClass('error');
        }
    });
    
    // Keywords validation
    $('#keywords').on('blur', function() {
        var keywords = $(this).val();
        var keywordsArray = keywords.split(',').map(function(k) { return k.trim(); }).filter(function(k) { return k; });
        
        if (keywords && keywordsArray.length < 3) {
            $(this).addClass('error');
            $(this).next('.error-message').remove();
            $(this).after('<small class="error-message" style="color: #d63384;">Please enter at least 3 keywords separated by commas.</small>');
        } else {
            $(this).removeClass('error');
            $(this).next('.error-message').remove();
        }
    });
    
    // Email validation
    $('#contact_email').on('blur', function() {
        var email = $(this).val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(this).addClass('error');
            $(this).next('.error-message').remove();
            $(this).after('<small class="error-message" style="color: #d63384;">Please enter a valid email address.</small>');
        } else {
            $(this).removeClass('error');
            $(this).next('.error-message').remove();
        }
    });
    
    // === FILE UPLOAD HANDLING ===
    
    // Handle supporting document upload
    $('#supporting-document-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = $('#upload-document-btn');
        var messagesDiv = $('#upload-messages');
        var fileInput = $('#supporting_document');
        
        // Validate file selection
        if (!fileInput[0].files.length) {
            showUploadMessage('Please select a PDF file to upload.', 'error');
            return;
        }
        
        var file = fileInput[0].files[0];
        
        // Validate file type
        if (file.type !== 'application/pdf') {
            showUploadMessage('Please select a PDF file only.', 'error');
            return;
        }
        
        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            showUploadMessage('File size must be less than 10MB.', 'error');
            return;
        }
        
        // Disable submit button and show loading state
        submitBtn.prop('disabled', true).text('Uploading...');
        messagesDiv.empty();
        
        // Prepare form data
        var formData = new FormData();
        formData.append('action', 'upload_supporting_document');
        formData.append('file_upload_nonce', form.find('input[name="file_upload_nonce"]').val());
        formData.append('supporting_document', file);
        
        // Submit via AJAX
        $.ajax({
            url: hkota_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showUploadMessage('File uploaded successfully!', 'success');
                    submitBtn.text('Replace Document');
                    // Add uploaded file info if not already present
                    if (!$('.uploaded-file-info').length) {
                        var fileName = file.name;
                        var fileInfo = '<div class="uploaded-file-info">' +
                                      '<p><strong>Uploaded File:</strong> ' + fileName + '</p>' +
                                      '<p><em>You can upload a new file to replace the existing one.</em></p>' +
                                      '</div>';
                        form.before(fileInfo);
                    }
                } else {
                    showUploadMessage(response.data || 'Failed to upload file. Please try again.', 'error');
                }
            },
            error: function() {
                showUploadMessage('There was an error uploading the file. Please try again.', 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                if (submitBtn.text() === 'Uploading...') {
                    submitBtn.text('Upload Document');
                }
            }
        });
    });
    
    // Show upload messages
    function showUploadMessage(message, type) {
        var messageDiv = $('<div class="message ' + type + '">' + message + '</div>');
        
        // Remove existing messages
        $('#upload-messages').empty();
        
        // Add new message
        $('#upload-messages').append(messageDiv);
        
        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(function() {
                messageDiv.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
        
        // Scroll to message
        $('html, body').animate({
            scrollTop: messageDiv.offset().top - 20
        }, 500);
    }
    
    // Word counting and validation functions
    function countWords(text) {
        if (!text || text.trim() === '') return 0;
        return text.trim().split(/\s+/).length;
    }
    
    function countAuthors(text) {
        if (!text || text.trim() === '') return 0;
        // Count patterns like "Name(1)" or "Name (1)" or just "Name,"
        var authorPattern = /[^,()]+\([^)]*\)|[^,]+/g;
        var matches = text.match(authorPattern);
        return matches ? matches.filter(author => author.trim().length > 0).length : 0;
    }
    
    function updateWordCount(fieldId, limit, isAuthor = false) {
        var $field = $('#' + fieldId);
        var $countDisplay = $('#' + fieldId + '_count');
        var $wordCountDiv = $field.siblings('.word-count-display');
        var text = $field.val();
        var count = isAuthor ? countAuthors(text) : countWords(text);
        
        // Update count display
        $countDisplay.text(count);
        
        // Remove existing classes
        $field.removeClass('over-limit near-limit');
        $wordCountDiv.removeClass('over-limit near-limit');
        
        // Add appropriate classes based on count
        if (count > limit) {
            $field.addClass('over-limit');
            $wordCountDiv.addClass('over-limit');
        } else if (count > limit * 0.9) { // 90% of limit
            $field.addClass('near-limit');
            $wordCountDiv.addClass('near-limit');
        }
        
        return count <= limit;
    }
    
    function validateWordLimits() {
        var errors = [];
        
        // Validate title (20 words)
        var titleWords = countWords($('#abstract_title').val());
        if (titleWords > 20) {
            errors.push("Abstract title exceeds 20 words limit (current: " + titleWords + " words)");
        }
        
        // Validate authors (8 authors)
        var authorCount = countAuthors($('#authors').val());
        if (authorCount > 8) {
            errors.push("Authors field exceeds 8 authors limit (current: " + authorCount + " authors)");
        }
        
        // Validate combined abstract sections (500 words total)
        var backgroundWords = countWords($('#background').val());
        var methodsWords = countWords($('#methods').val());
        var resultsWords = countWords($('#results').val());
        var conclusionWords = countWords($('#conclusion').val());
        
        var totalWords = backgroundWords + methodsWords + resultsWords + conclusionWords;
        if (totalWords > 500) {
            errors.push("Combined abstract sections (Background, Methods, Results, Conclusion) exceed 500 words limit (current: " + totalWords + " words - Background: " + backgroundWords + ", Methods: " + methodsWords + ", Results: " + resultsWords + ", Conclusion: " + conclusionWords + ")");
        }
        
        // Update the combined word count display
        var $combinedCountDiv = $('#combined_abstract_count');
        if ($combinedCountDiv.length) {
            $combinedCountDiv.text(totalWords);
            
            var $combinedContainer = $combinedCountDiv.closest('.combined-word-count');
            $combinedContainer.removeClass('over-limit near-limit');
            
            if (totalWords > 500) {
                $combinedContainer.addClass('over-limit');
            } else if (totalWords > 450) {
                $combinedContainer.addClass('near-limit');
            }
        }
        
        if (errors.length > 0) {
            return errors.join(' ');
        }
        
        return true;
    }
    
    // Helper function to update combined word count display (for real-time updates)
    function updateCombinedWordCountDisplay() {
        var backgroundWords = countWords($('#background').val());
        var methodsWords = countWords($('#methods').val());
        var resultsWords = countWords($('#results').val());
        var conclusionWords = countWords($('#conclusion').val());
        
        var totalWords = backgroundWords + methodsWords + resultsWords + conclusionWords;
        var limit = 500;
        
        // Update the combined word count display
        var $combinedCountDiv = $('#combined_abstract_count');
        if ($combinedCountDiv.length) {
            $combinedCountDiv.text(totalWords);
            
            var $combinedContainer = $combinedCountDiv.closest('.combined-word-count');
            $combinedContainer.removeClass('over-limit near-limit');
            
            if (totalWords > limit) {
                $combinedContainer.addClass('over-limit');
            } else if (totalWords > limit * 0.9) {
                $combinedContainer.addClass('near-limit');
            }
        }
    }
    
    // Initialize word counting for all fields
    function initializeWordCounting() {
        // Title field with word limit
        var titleField = $('#abstract_title');
        if (titleField.length) {
            var titleLimit = parseInt(titleField.data('word-limit'));
            updateWordCount('abstract_title', titleLimit);
            
            titleField.off('input keyup paste').on('input keyup paste', function() {
                updateWordCount('abstract_title', titleLimit);
            });
        }
        
        // Abstract sections with combined word limit validation
        var abstractFields = ['background', 'methods', 'results', 'conclusion'];
        abstractFields.forEach(function(fieldId) {
            var field = $('#' + fieldId);
            if (field.length) {
                // Add real-time validation for combined word count
                field.off('input keyup paste').on('input keyup paste', function() {
                    // Update combined word count display (just the display part)
                    updateCombinedWordCountDisplay();
                });
            }
        });
        
        // Initialize combined word count display
        updateCombinedWordCountDisplay();
        
        // Authors field with author limit
        var authorsField = $('#authors');
        if (authorsField.length) {
            var authorLimit = parseInt(authorsField.data('author-limit'));
            updateWordCount('authors', authorLimit, true);
            
            authorsField.off('input keyup paste').on('input keyup paste', function() {
                updateWordCount('authors', authorLimit, true);
            });
        }
    }
    
    function initializeFormSubmission() {
        // Remove ALL existing submit handlers with namespace for clean slate
        $(document).off('submit.hkota', '#hkota-abstract-form');
        
        // Add comprehensive form submission handler with namespace
        $(document).on('submit.hkota', '#hkota-abstract-form', function(e) {
            e.preventDefault();
            
            var form = $(this);
            
            // Prevent double submission
            if (form.data('submitting')) {
                return false;
            }
            
            // Update keywords field before submission
            updateKeywordsField();
            
            var submitBtn = form.find('.hkota-submit-btn');
            var messagesDiv = $('#hkota-form-messages');
            
            // Check if keywords validation is passing
            if (submitBtn.hasClass('keywords-disabled')) {
                showFormMessage('Please complete at least the first 3 keywords before submitting.', 'error');
                return false;
            }
            
            // Validate keywords count (at least 3)
            var keywordCount = $('#keywords').val().split(',').filter(function(k) { return k.trim(); }).length;
            if (keywordCount < 3) {
                showFormMessage('Please provide at least 3 keywords.', 'error');
                return false;
            }
            
            // Validate word limits
            var wordLimitValidation = validateWordLimits();
            if (wordLimitValidation !== true) {
                showFormMessage(wordLimitValidation, 'error');
                return false;
            }
            
            // Mark form as submitting and disable button immediately
            form.data('submitting', true);
            var originalText = submitBtn.text();
            console.log('Original button text:', originalText);
            submitBtn.prop('disabled', true).addClass('loading').text('Submitting...');
            
            // Clear any existing messages
            messagesDiv.empty();
            
            // Show loading overlay
            showLoadingOverlay('Submitting your abstract...');
            
            // Determine if this is an AJAX-loaded form (for post-success behavior)
            var isAjaxForm = form.closest('.hkota-abstract-form-container-ajax').length > 0;
            
            // Use serialize for reliable form data capture
            var formData = form.serialize() + '&action=submit_abstract';
            
            // Submit via AJAX
            $.post(hkota_ajax.ajax_url, formData)
                .done(function(response) {
                    hideLoadingOverlay();
                    
                    if (response.success) {
                        // Show success message with countdown
                        showSuccessWithCountdown(response.data);
                    } else {
                        showFormMessage(response.data, 'error');
                    }
                })
                .fail(function() {
                    hideLoadingOverlay();
                    showFormMessage('Failed to submit. Please try again.', 'error');
                })
                .always(function() {
                    // Reset submission state and re-enable button
                    form.removeData('submitting');
                    submitBtn.text('submitted');
                });
        });
    }
    
    // Show success message with countdown and redirect
    function showSuccessWithCountdown(message) {
        var messagesDiv = $('#hkota-form-messages');
        var countdown = 5;
        
        // Initial message with countdown
        var updateMessage = function() {
            var countdownHtml = '<div class="hkota-notice hkota-notice-success">' +
                '<p>' + message + '</p>' +
                '<p> Redirecting to submissions list in ' + countdown + ' seconds...</p>' +
                '</div>';
            messagesDiv.html(countdownHtml);
            $('html, body').animate({ scrollTop: messagesDiv.offset().top - 100 }, 500);
        };
        
        updateMessage();
        
        // Update countdown every second
        var timer = setInterval(function() {
            countdown--;
            if (countdown > 0) {
                updateMessage();
            } else {
                clearInterval(timer);
                // Redirect to the current page (which will show the submissions list)
                window.location.href = window.location.pathname;
            }
        }, 1000);
    }
    
    function showFormMessage(message, type) {
        var messagesDiv = $('#hkota-form-messages');
        messagesDiv.html('<div class="hkota-notice hkota-notice-' + type + '"><p>' + message + '</p></div>');
        $('html, body').animate({ scrollTop: messagesDiv.offset().top - 100 }, 500);
    }
});
