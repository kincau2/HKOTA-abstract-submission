# HKOTA Abstract Submission Plugin - AI Coding Instructions

## Plugin Architecture

This is a WordPress plugin for managing academic abstract submissions with a role-based system supporting regular users, reviewers (`hkota_reviewer`), and administrators.

### Core Components

- **Main Plugin File**: `hkota-abstract-submission.php` - Bootstraps the plugin and loads all includes
- **Database Layer**: `class-database.php` - Handles all database operations with custom tables
- **Frontend Interface**: `class-shortcode.php` - Manages the `[hkota_abstract_form]` shortcode and AJAX handlers
- **Admin Interface**: `class-admin.php` - WordPress admin panel functionality
- **Template System**: `class-template-helper.php` - Centralized template loading with variable extraction
- **Email System**: `class-email.php` - HTML email notifications using template system
- **File Handling**: `class-file-handler.php` - Secure file uploads with `.htaccess` protection
- **PDF Generation**: `class-pdf-generator.php` - Uses DomPDF library for submission reports

## Database Schema

The plugin creates two custom tables:
- `wp_hkota_abstract_submissions` - Main submission data with unique submission numbers (format: YY-T-NNN)
- `wp_hkota_reviewer_ratings` - Reviewer scoring system with weighted calculations

## Critical Development Patterns

### AJAX Security Pattern
All AJAX handlers follow this pattern:
```php
// Verify nonce - supports dual nonces for admin/frontend
if (!wp_verify_nonce($_POST['nonce'], 'hkota_abstract_nonce')) {
    wp_send_json_error('Security check failed');
}

// Check user capabilities
if (!is_user_logged_in() || !current_user_can('required_capability')) {
    wp_send_json_error('Insufficient permissions');
}
```

### Template Loading Convention
Templates are organized by purpose with consistent variable passing:
```php
HKOTA_Template_Helper::load_template('template-name', array('var' => $value));
// Email templates: email-{type}.php
// Admin templates: admin-{page}.php  
// Form templates: form-{context}.php
```

### Submission Number Generation
Unique submission numbers use format `YY-T-NNN` where:
- YY = 2-digit year
- T = Type (O=Oral, P=E-poster)
- NNN = Sequential number with database transactions for thread safety

### Role-Based Interface Rendering
The shortcode `[hkota_abstract_form]` dynamically renders different interfaces:
- **Not logged in**: Login prompt with redirect cookie
- **Reviewer role**: Submission review interface with rating modals
- **Regular users**: Submission form or submissions list based on deadline

### Word Limit Validation
Real-time JavaScript validation with server-side verification:
- Abstract title: 20 words max
- Authors: 8 authors max  
- Background/Methods/Results/Conclusion: 500 words each
- Keywords: Exactly 5, unique, 50 chars each

## Key Integration Points

### Email Templates
HTML emails with embedded CSS located in `templates/email-*.php`. Status-specific templates:
- `email-accept-oral.php` - Oral presentation acceptance
- `email-accept-poster.php` - Poster presentation acceptance
- `email-reject.php` - Rejection notification

### Asset Enqueuing
Frontend and admin assets are conditionally loaded:
- Frontend: Only on pages with shortcode
- Admin: Only on plugin admin pages (hook contains 'hkota')

### File Upload Security
Files uploaded to `/wp-content/uploads/hkota-submissions/` with:
- `.htaccess` deny rules
- Filename sanitization with timestamp prefixes
- User ownership validation

## Development Workflows

### Testing AJAX Endpoints
Use browser dev tools with these common endpoints:
- `submit_abstract` - Form submission
- `get_submission_details` - Modal data loading
- `update_submission_status` - Admin status changes
- `submit_reviewer_rating` - Reviewer scoring

### Database Operations
All database operations use prepared statements via `HKOTA_Database` class. For new features requiring data:
1. Add fields to `create_tables()` method
2. Update `insert_submission()` with new field handling
3. Modify templates to display new fields

### Adding New Email Templates
1. Create template in `templates/email-{type}.php`
2. Add case in `HKOTA_Email::send_status_notification()`
3. Use `HKOTA_Template_Helper::get_email_template()` for loading

### Reviewer System Integration
The reviewer system supports weighted scoring across 4 criteria. When adding reviewer features:
- Check `current_user_can('hkota_reviewer')` for access control
- Use `HKOTA_Database::save_reviewer_rating()` for score persistence
- Modal templates follow pattern: `rating-details-modal.php`

## Plugin-Specific Conventions

- All classes prefixed with `HKOTA_`
- Database table names include `hkota_` prefix
- AJAX actions use `hkota_` prefix
- CSS classes use `hkota-` prefix
- WordPress nonces follow pattern `hkota_{context}_nonce`
- File uploads restricted to PDF only with 5MB limit
- Submission editing locked after deadline passes
- Admin capabilities require `manage_options` or `hkota_reviewer`
