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
});
