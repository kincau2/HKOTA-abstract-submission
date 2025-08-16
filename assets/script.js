jQuery(document).ready(function($) {
    
    // Handle form submission
    $('#hkota-abstract-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('.hkota-submit-btn');
        var messagesDiv = $('#hkota-form-messages');
        
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
    
    // Character counters for text areas
    $('textarea[name="background"], textarea[name="methods"], textarea[name="results"], textarea[name="conclusion"]').each(function() {
        var textarea = $(this);
        var fieldName = textarea.attr('name');
        var counter = $('<div class="char-counter">' + fieldName.charAt(0).toUpperCase() + fieldName.slice(1) + ' characters: <span class="count">0</span></div>');
        textarea.after(counter);
        
        textarea.on('input', function() {
            var charCount = $(this).val().length;
            counter.find('.count').text(charCount);
        });
        
        // Trigger initial count
        textarea.trigger('input');
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
});
