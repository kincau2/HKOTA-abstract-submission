jQuery(document).ready(function($) {
    
    // Handle keyword fields
    function updateKeywordsField() {
        var keywords = [];
        for (var i = 1; i <= 5; i++) {
            var keyword = $('#keyword_' + i).val().trim();
            if (keyword) {
                keywords.push(keyword);
            }
        }
        $('#keywords').val(keywords.join(', '));
    }
    
    // Initialize keywords on page load
    $(document).ready(function() {
        updateKeywordsField();
    });
    
    // Update hidden field when any keyword input changes
    $('[name^="keyword_"]').on('input blur', function() {
        updateKeywordsField();
        validateKeywords();
        
        // Auto-focus next field when current field is filled
        var currentNum = parseInt($(this).attr('name').split('_')[1]);
        var currentValue = $(this).val().trim();
        
        if (currentValue && currentNum < 5) {
            var nextField = $('#keyword_' + (currentNum + 1));
            if (!nextField.val().trim()) {
                setTimeout(function() {
                    nextField.focus();
                }, 100);
            }
        }
    });
    
    // Handle Enter key to move to next field
    $('[name^="keyword_"]').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            var currentNum = parseInt($(this).attr('name').split('_')[1]);
            if (currentNum < 5) {
                $('#keyword_' + (currentNum + 1)).focus();
            } else {
                // On last field, submit form if valid
                if ($('.hkota-submit-btn').prop('disabled') === false) {
                    $('#hkota-abstract-form').submit();
                }
            }
        }
    });
    
    // Validate keywords in real-time
    function validateKeywords() {
        var filledKeywords = 0;
        var allValid = true;
        
        for (var i = 1; i <= 5; i++) {
            var $input = $('#keyword_' + i);
            var value = $input.val().trim();
            
            if (value) {
                filledKeywords++;
                
                // Check for duplicates
                var isDuplicate = false;
                for (var j = 1; j <= 5; j++) {
                    if (i !== j && $('#keyword_' + j).val().trim().toLowerCase() === value.toLowerCase()) {
                        isDuplicate = true;
                        break;
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
        
        if (filledKeywords === 5 && allValid) {
            $submitBtn.removeClass('keywords-disabled');
        } else {
            $submitBtn.addClass('keywords-disabled');
            
            // Show validation message
            var message = '';
            if (filledKeywords < 5) {
                message = 'Please enter all 5 keywords (' + filledKeywords + '/5 completed).';
            } else if (!allValid) {
                message = 'Please ensure all keywords are unique.';
            }
            
            $('.keywords-container').after('<div class="keywords-validation-message" style="color: #dc3232; font-size: 12px; margin-top: 5px;">' + message + '</div>');
        }
    }
    
    // Handle form submission
    $('#hkota-abstract-form').on('submit', function(e) {
        e.preventDefault();
        
        // Update keywords field before submission
        updateKeywordsField();
        
        var form = $(this);
        var submitBtn = form.find('.hkota-submit-btn');
        var messagesDiv = $('#hkota-form-messages');
        
        // Check if keywords validation is passing
        if (submitBtn.hasClass('keywords-disabled')) {
            messagesDiv.html('<div class="hkota-message error">Please complete all 5 keywords before submitting.</div>');
            return false;
        }
        
        // Validate keywords count
        var keywordCount = $('#keywords').val().split(',').filter(function(k) { return k.trim(); }).length;
        if (keywordCount !== 5) {
            messagesDiv.html('<div class="hkota-message error">Please provide exactly 5 keywords.</div>');
            return false;
        }
        
        // Disable submit button and show loading state
        submitBtn.prop('disabled', true).text('Submitting...');
        form.addClass('hkota-loading');
        messagesDiv.empty();
        
        // Prepare form data
        var formData = {
            action: 'submit_abstract',
            hkota_nonce: form.find('input[name="hkota_nonce"]').val(),
            title: form.find('select[name="title"]').val(),
            surname: form.find('input[name="surname"]').val(),
            given_name: form.find('input[name="given_name"]').val(),
            contact_number: form.find('input[name="contact_number"]').val(),
            contact_email: form.find('input[name="contact_email"]').val(),
            organization: form.find('input[name="organization"]').val(),
            theme: form.find('select[name="theme"]').val(),
            presentation_preference: form.find('select[name="presentation_preference"]').val(),
            abstract_title: form.find('input[name="abstract_title"]').val(),
            authors: form.find('textarea[name="authors"]').val(),
            affiliations: form.find('textarea[name="affiliations"]').val(),
            background: form.find('textarea[name="background"]').val(),
            methods: form.find('textarea[name="methods"]').val(),
            results: form.find('textarea[name="results"]').val(),
            conclusion: form.find('textarea[name="conclusion"]').val(),
            keywords: form.find('input[name="keywords"]').val()
        };
        
        // Submit via AJAX
        $.post(hkota_ajax.ajax_url, formData)
            .done(function(response) {
                if (response.success) {
                    messagesDiv.html('<div class="hkota-message success">' + response.data + '</div>');
                    submitBtn.text('Update Submission');
                } else {
                    messagesDiv.html('<div class="hkota-message error">' + response.data + '</div>');
                }
            })
            .fail(function() {
                messagesDiv.html('<div class="hkota-message error">There was an error submitting your form. Please try again.</div>');
            })
            .always(function() {
                // Re-enable submit button and remove loading state
                submitBtn.prop('disabled', false);
                form.removeClass('hkota-loading');
                
                // Scroll to messages
                if (messagesDiv.children().length > 0) {
                    $('html, body').animate({
                        scrollTop: messagesDiv.offset().top - 20
                    }, 500);
                }
            });
    });
    
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
        var keywordsArray = keywords.split(',').map(function(k) { return k.trim(); });
        
        if (keywords && keywordsArray.length !== 5) {
            $(this).addClass('error');
            $(this).next('.error-message').remove();
            $(this).after('<small class="error-message" style="color: #d63384;">Please enter exactly 5 keywords separated by commas.</small>');
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
        var isValid = true;
        
        // Validate title (20 words)
        if (!updateWordCount('abstract_title', 20)) {
            isValid = false;
        }
        
        // Validate authors (8 authors)
        if (!updateWordCount('authors', 8, true)) {
            isValid = false;
        }
        
        // Validate background (500 words)
        if (!updateWordCount('background', 500)) {
            isValid = false;
        }
        
        // Validate methods (500 words)
        if (!updateWordCount('methods', 500)) {
            isValid = false;
        }
        
        // Validate results (500 words)
        if (!updateWordCount('results', 500)) {
            isValid = false;
        }
        
        // Validate conclusion (500 words)
        if (!updateWordCount('conclusion', 500)) {
            isValid = false;
        }
        
        return isValid;
    }
    
    // Initialize word counting for all fields
    function initializeWordCounting() {
        // Fields with word limits
        var wordLimitFields = ['abstract_title', 'background', 'methods', 'results', 'conclusion'];
        
        wordLimitFields.forEach(function(fieldId) {
            var limit = parseInt($('#' + fieldId).data('word-limit'));
            updateWordCount(fieldId, limit);
            
            // Add real-time validation
            $('#' + fieldId).on('input keyup paste', function() {
                updateWordCount(fieldId, limit);
            });
        });
        
        // Authors field with author limit
        var authorLimit = parseInt($('#authors').data('author-limit'));
        updateWordCount('authors', authorLimit, true);
        
        $('#authors').on('input keyup paste', function() {
            updateWordCount('authors', authorLimit, true);
        });
    }
    
    // Initialize on page load
    initializeWordCounting();
    
    // Add validation to form submission
    $('#hkota-abstract-form').on('submit', function(e) {
        if (!validateWordLimits()) {
            e.preventDefault();
            showUploadMessage('error', 'Please check the word limits for all fields before submitting.');
            return false;
        }
    });
});
