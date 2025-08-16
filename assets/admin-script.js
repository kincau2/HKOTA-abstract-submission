jQuery(document).ready(function($) {
    
    // === REVIEWER MANAGEMENT ===
    
    var searchTimeout;
    var currentSearchResults = [];
    
    // Handle reviewer search input
    $('#reviewer-search-input').on('input', function() {
        var searchTerm = $(this).val().trim();
        var resultsContainer = $('#reviewer-search-results');
        
        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        // Hide results if search term is too short
        if (searchTerm.length < 2) {
            resultsContainer.hide().empty();
            return;
        }
        
        // Debounce search requests
        searchTimeout = setTimeout(function() {
            searchUsers(searchTerm);
        }, 300);
    });
    
    // Search users via AJAX
    function searchUsers(searchTerm) {
        var resultsContainer = $('#reviewer-search-results');
        
        // Show loading state
        resultsContainer.html('<div class="hkota-search-result-item">Searching...</div>').show();
        
        $.post(hkota_admin_ajax.ajax_url, {
            action: 'search_users_for_reviewer',
            search_term: searchTerm,
            nonce: hkota_admin_ajax.nonce
        })
        .done(function(response) {
            if (response.success && response.data.length > 0) {
                currentSearchResults = response.data;
                displaySearchResults(response.data);
            } else {
                resultsContainer.html('<div class="hkota-search-result-item" style="color: #666; font-style: italic;">No users found</div>');
            }
        })
        .fail(function() {
            resultsContainer.html('<div class="hkota-search-result-item" style="color: #dc3232;">Search failed. Please try again.</div>');
        });
    }
    
    // Display search results
    function displaySearchResults(users) {
        var resultsContainer = $('#reviewer-search-results');
        var html = '';
        
        $.each(users, function(index, user) {
            html += '<div class="hkota-search-result-item" data-user-id="' + user.id + '">' +
                       '<div class="hkota-search-result-name">' + user.name + '</div>' +
                       '<div class="hkota-search-result-email">' + user.email + '</div>' +
                    '</div>';
        });
        
        resultsContainer.html(html).show();
    }
    
    // Handle clicking on search result
    $(document).on('click', '.hkota-search-result-item[data-user-id]', function() {
        var userId = $(this).data('user-id');
        var selectedUser = currentSearchResults.find(function(user) {
            return user.id == userId;
        });
        
        if (selectedUser) {
            addReviewer(selectedUser);
        }
    });
    
    // Add reviewer
    function addReviewer(user) {
        showLoading('Adding reviewer...');
        
        $.post(hkota_admin_ajax.ajax_url, {
            action: 'add_reviewer',
            user_id: user.id,
            nonce: hkota_admin_ajax.nonce
        })
        .done(function(response) {
            if (response.success) {
                // Clear search input and results
                $('#reviewer-search-input').val('');
                $('#reviewer-search-results').hide().empty();
                
                // Add reviewer to the list
                addReviewerToList(response.data.user);
                
                showAdminMessage('Reviewer added successfully!', 'success');
            } else {
                showAdminMessage(response.data || 'Failed to add reviewer.', 'error');
            }
        })
        .fail(function() {
            showAdminMessage('There was an error adding the reviewer. Please try again.', 'error');
        })
        .always(function() {
            hideLoading();
        });
    }
    
    // Add reviewer to the visual list
    function addReviewerToList(reviewer) {
        var reviewersList = $('#reviewers-list');
        var noReviewersMsg = reviewersList.find('.no-reviewers');
        
        // Remove "no reviewers" message if it exists
        if (noReviewersMsg.length > 0) {
            noReviewersMsg.remove();
            
            // Create the table structure
            reviewersList.html(`
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            `);
        }
        
        // Add new reviewer row
        var newRow = `
            <tr data-reviewer-id="${reviewer.id}">
                <td><strong>${reviewer.name}</strong></td>
                <td>${reviewer.email}</td>
                <td>
                    <button type="button" 
                            class="button button-small remove-reviewer-btn" 
                            data-reviewer-id="${reviewer.id}"
                            data-reviewer-name="${reviewer.name}">
                        Remove
                    </button>
                </td>
            </tr>
        `;
        
        reviewersList.find('tbody').append(newRow);
    }
    
    // Handle remove reviewer button
    $(document).on('click', '.remove-reviewer-btn', function() {
        var button = $(this);
        var reviewerId = button.data('reviewer-id');
        var reviewerName = button.data('reviewer-name');
        var row = button.closest('tr');
        
        if (!confirm('Are you sure you want to remove "' + reviewerName + '" as a reviewer?\n\nThey will lose access to the review interface.')) {
            return;
        }
        
        showLoading('Removing reviewer...');
        
        $.post(hkota_admin_ajax.ajax_url, {
            action: 'remove_reviewer',
            user_id: reviewerId,
            nonce: hkota_admin_ajax.nonce
        })
        .done(function(response) {
            if (response.success) {
                // Remove the row
                row.fadeOut(function() {
                    $(this).remove();
                    
                    // Check if this was the last reviewer
                    var remainingRows = $('#reviewers-list tbody tr').length;
                    if (remainingRows === 0) {
                        $('#reviewers-list').html(`
                            <div class="no-reviewers" style="padding: 20px; text-align: center; color: #666; font-style: italic; border: 2px dashed #ddd; border-radius: 5px;">
                                No reviewers assigned yet. Add users above to grant them review access.
                            </div>
                        `);
                    }
                });
                
                showAdminMessage('Reviewer removed successfully.', 'success');
            } else {
                showAdminMessage(response.data || 'Failed to remove reviewer.', 'error');
            }
        })
        .fail(function() {
            showAdminMessage('There was an error removing the reviewer. Please try again.', 'error');
        })
        .always(function() {
            hideLoading();
        });
    });
    
    // Hide search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#reviewer-search-input, #reviewer-search-results').length) {
            $('#reviewer-search-results').hide();
        }
    });
    
    // Loading functions
    function showLoading(message) {
        var loadingDiv = $('#hkota-loading');
        if (message) {
            loadingDiv.find('span:last').text(message);
        }
        loadingDiv.show();
    }
    
    function hideLoading() {
        $('#hkota-loading').hide();
    }
    
    // === EXISTING SUBMISSION MANAGEMENT CODE ===
    
    // Handle status update buttons
    $('.update-status').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var submissionId = button.data('id');
        var status = button.data('status');
        var row = button.closest('tr');
        
        if (!confirm('Are you sure you want to ' + status + ' this submission?')) {
            return;
        }
        
        // Add loading state
        button.addClass('loading').prop('disabled', true);
        
        // Prepare data
        var data = {
            action: 'update_submission_status',
            submission_id: submissionId,
            status: status,
            nonce: hkota_admin_ajax.nonce
        };
        
        // Send AJAX request
        $.post(hkota_admin_ajax.ajax_url, data)
            .done(function(response) {
                if (response.success) {
                    // Update status badge
                    var statusBadge = row.find('.status-badge');
                    statusBadge.removeClass('status-pending status-accepted status-rejected')
                              .addClass('status-' + status)
                              .text(status.charAt(0).toUpperCase() + status.slice(1));
                    
                    // Remove action buttons for this row
                    row.find('.update-status').remove();
                    
                    // Show success message
                    showAdminMessage('Status updated successfully and email sent to applicant.', 'success');
                } else {
                    showAdminMessage(response.data || 'Failed to update status.', 'error');
                }
            })
            .fail(function() {
                showAdminMessage('There was an error updating the status. Please try again.', 'error');
            })
            .always(function() {
                button.removeClass('loading').prop('disabled', false);
            });
    });
    
    // Handle PDF download
    $('.download-pdf').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var submissionId = button.data('id');
        
        // Add loading state
        button.addClass('loading').prop('disabled', true);
        
        // Create download URL
        var downloadUrl = hkota_admin_ajax.ajax_url + '?action=download_submission_pdf&submission_id=' + submissionId + '&nonce=' + hkota_admin_ajax.nonce;
        
        // Open in new window for PDF generation
        window.open(downloadUrl, '_blank');
        
        // Remove loading state after a short delay
        setTimeout(function() {
            button.removeClass('loading').prop('disabled', false);
        }, 1000);
    });
    
    // Handle supporting document download
    $('.download-supporting-doc').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var submissionId = button.data('id');
        
        // Add loading state
        button.addClass('loading').prop('disabled', true);
        
        // Create download URL
        var downloadUrl = hkota_admin_ajax.ajax_url + '?action=download_supporting_document&submission_id=' + submissionId + '&nonce=' + hkota_admin_ajax.nonce;
        
        // Open in new window for download
        window.open(downloadUrl, '_blank');
        
        // Remove loading state after a short delay
        setTimeout(function() {
            button.removeClass('loading').prop('disabled', false);
        }, 1000);
    });
    
    // Handle view details
    $('.view-details').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var submissionId = button.data('id');
        var row = button.closest('tr');
        
        // Get submission data from the row
        var submissionData = {
            id: submissionId,
            title: row.find('td:nth-child(1)').text(),
            name: row.find('td:nth-child(2)').text(),
            email: row.find('td:nth-child(3)').text(),
            organization: row.find('td:nth-child(4)').text(),
            theme: row.find('td:nth-child(5)').text(),
            presentation: row.find('td:nth-child(6)').text(),
            abstractTitle: row.find('td:nth-child(7)').text(),
            date: row.find('td:nth-child(8)').text(),
            status: row.find('.status-badge').text()
        };
        
        showSubmissionModal(submissionData);
    });
    
    // Modal functionality
    function showSubmissionModal(data) {
        var modal = $('#submission-modal');
        var modalBody = modal.find('#submission-details-content');
        
        // Build modal content
        var content = `
            <div class="submission-detail-grid">
                <div class="submission-detail-label">Submission ID:</div>
                <div>#${data.id}</div>
                
                <div class="submission-detail-label">Title:</div>
                <div>${data.title}</div>
                
                <div class="submission-detail-label">Name:</div>
                <div>${data.name}</div>
                
                <div class="submission-detail-label">Email:</div>
                <div>${data.email}</div>
                
                <div class="submission-detail-label">Organization:</div>
                <div>${data.organization}</div>
                
                <div class="submission-detail-label">Theme:</div>
                <div>${data.theme}</div>
                
                <div class="submission-detail-label">Presentation Preference:</div>
                <div>${data.presentation}</div>
                
                <div class="submission-detail-label">Abstract Title:</div>
                <div>${data.abstractTitle}</div>
                
                <div class="submission-detail-label">Submission Date:</div>
                <div>${data.date}</div>
                
                <div class="submission-detail-label">Status:</div>
                <div><span class="status-badge status-${data.status.toLowerCase()}">${data.status}</span></div>
            </div>
            
            <div class="submission-detail-label">Abstract Content:</div>
            <div class="submission-abstract-content">
                <em>Full abstract content would be loaded here via AJAX in a complete implementation...</em>
            </div>
        `;
        
        modalBody.html(content);
        modal.show();
    }
    
    // Close modal
    $(document).on('click', '.hkota-modal-close, .hkota-modal', function(e) {
        if (e.target === this) {
            $('.hkota-modal').hide();
        }
    });
    
    // Escape key to close modal
    $(document).on('keydown', function(e) {
        if (e.keyCode === 27) { // Escape key
            $('.hkota-modal').hide();
        }
    });
    
    // Show admin messages
    function showAdminMessage(message, type) {
        var messageDiv = $('<div class="hkota-admin-message ' + type + '">' + message + '</div>');
        
        // Remove existing messages
        $('.hkota-admin-message').remove();
        
        // Add new message at the top of the submissions container
        $('.hkota-submissions-container').prepend(messageDiv);
        
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
    
    // Confirmation for bulk actions (if implemented later)
    $('.bulk-action-btn').on('click', function(e) {
        var selectedItems = $('input[name="submission_ids[]"]:checked').length;
        
        if (selectedItems === 0) {
            e.preventDefault();
            alert('Please select at least one submission.');
            return false;
        }
        
        if (!confirm('Are you sure you want to perform this action on ' + selectedItems + ' submission(s)?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Select all checkbox functionality
    $('#select-all-submissions').on('change', function() {
        $('input[name="submission_ids[]"]').prop('checked', $(this).is(':checked'));
    });
    
    // Update select all when individual checkboxes change
    $('input[name="submission_ids[]"]').on('change', function() {
        var totalCheckboxes = $('input[name="submission_ids[]"]').length;
        var checkedCheckboxes = $('input[name="submission_ids[]"]:checked').length;
        
        $('#select-all-submissions').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
    
    // Set minimum datetime to current time for deadline input
    $(document).ready(function() {
        // Get current Hong Kong time
        var now = new Date();
        var utcTime = now.getTime() + (now.getTimezoneOffset() * 60000);
        var hkTime = new Date(utcTime + (8 * 3600000)); // UTC+8
        
        var year = hkTime.getFullYear();
        var month = String(hkTime.getMonth() + 1).padStart(2, '0');
        var day = String(hkTime.getDate()).padStart(2, '0');
        var hours = String(hkTime.getHours()).padStart(2, '0');
        var minutes = String(hkTime.getMinutes()).padStart(2, '0');
        
        var minDateTime = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
        $('#submission_deadline').attr('min', minDateTime);
    });
});
