# HKOTA Abstract Submission Plugin

A WordPress plugin for managing abstract submissions with user roles and admin panel management.

## Features

### Frontend Features
- **Single shortcode**: `[hkota_abstract_form]` to display the submission form
- **User authentication**: Requires users to be logged in to view/submit
- **Role-based content**: Different interfaces for regular users vs. reviewers
- **Edit functionality**: Users can edit their submissions before the deadline
- **Form validation**: Real-time validation with word counting
- **Email notifications**: Automatic confirmation emails sent to users

### Admin Features
- **Submissions management**: View all submissions in a table format
- **Status management**: Accept/reject submissions with one click
- **PDF export**: Download submission details as PDF
- **Email notifications**: Automatic status update emails to applicants
- **Admin notifications**: New submission alerts for administrators

### User Roles
- **Regular users**: Can submit and edit their abstracts
- **hkota_reviewer**: Special reviewer interface (to be developed)
- **Administrator**: Full access to all admin features

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically create the necessary database table
4. Add the shortcode `[hkota_abstract_form]` to any page or post

## Usage

### Frontend Usage
Add the shortcode `[hkota_abstract_form]` to any page or post where you want the submission form to appear.

### Admin Usage
1. Navigate to "Abstract Submission" in the WordPress admin menu
2. View all submissions in the "Abstract Submissions" page
3. Use action buttons to accept/reject submissions
4. Download PDF reports for individual submissions
5. Configure settings in the "Submission Settings" page (to be developed)

## Database Structure

The plugin creates a table `wp_hkota_abstract_submissions` with the following fields:
- `id`: Primary key
- `user_id`: WordPress user ID
- `title`: Applicant's title (Professor, Dr., Mr., Ms.)
- `surname`: Applicant's surname
- `given_name`: Applicant's given name
- `contact_number`: Applicant's contact number
- `contact_email`: Applicant's email address
- `organization`: Applicant's organization
- `theme`: Theme of presenting paper
- `presentation_preference`: Oral presentation or E-poster
- `abstract_title`: Title of the abstract
- `authors`: List of authors with order indication
- `affiliations`: Author affiliations
- `background`: Abstract background section
- `methods`: Abstract methods section
- `results`: Abstract results and findings section
- `conclusion`: Abstract conclusion section
- `keywords`: 5 keywords separated by commas
- `submission_date`: When the submission was made
- `status`: Current status (pending, accepted, rejected)
- `admin_notes`: Internal notes from administrators

## File Structure

```
hkota-abstract-submission/
├── hkota-abstract-submission.php (Main plugin file)
├── includes/
│   ├── class-database.php (Database operations)
│   ├── class-shortcode.php (Frontend form handling)
│   ├── class-admin.php (Admin panel functionality)
│   ├── class-email.php (Email notifications)
│   └── class-pdf-generator.php (PDF generation)
├── assets/
│   ├── style.css (Frontend styles)
│   ├── script.js (Frontend JavaScript)
│   ├── admin-style.css (Admin panel styles)
│   └── admin-script.js (Admin panel JavaScript)
└── README.md (This file)
```

## Customization

### Form Fields
The submission form includes the following comprehensive fields:

**Personal Information:**
1. Title (Professor, Dr., Mr., Ms.)
2. Surname
3. Given Name
4. Contact Number
5. Contact E-mail Address
6. Organization

**Presentation Details:**
7. Theme of Presenting Paper (6 predefined options)
8. Preference of Presentation (Oral/E-poster)

**Abstract Information:**
9. Abstract Title
10. Authors (with order indication)
11. Affiliations of the author(s)
12. Background
13. Methods
14. Results and Findings
15. Conclusion
16. Keywords (exactly 5 keywords required)
To add new fields to the submission form, modify:
1. `class-shortcode.php` - Add form fields and validation
2. `class-database.php` - Update database schema and operations
3. `assets/style.css` - Style the new fields

### Email Templates
Email templates can be customized in `class-email.php`. The plugin supports HTML emails with embedded CSS.

### PDF Generation
The current PDF generator creates an HTML page that can be printed to PDF. For more advanced PDF features, consider integrating libraries like TCPDF or FPDF.

## Hooks and Filters

The plugin provides several hooks for customization:

### Actions
- `hkota_before_submission_save` - Before saving a submission
- `hkota_after_submission_save` - After saving a submission
- `hkota_status_updated` - When submission status changes

### Filters
- `hkota_form_fields` - Modify form fields
- `hkota_email_content` - Customize email content
- `hkota_pdf_content` - Modify PDF content

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Security Features

- Nonce verification for all form submissions
- Data sanitization and validation
- Capability checks for admin functions
- SQL injection prevention using prepared statements

## Future Development

The following features are planned for future releases:
1. Reviewer interface for hkota_reviewer role users
2. Advanced submission settings page
3. Deadline management
4. File upload support for abstracts
5. Advanced PDF generation with formatting options
6. Email template customization interface
7. Submission statistics and reporting

## Support

For support and bug reports, please contact the HKOTA development team.

## Changelog

### Version 1.0.0
- Initial release
- Basic submission form with user authentication
- Admin panel for managing submissions
- Email notifications
- PDF export functionality
- Role-based access control
