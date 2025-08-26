<?php
/**
 * Email template for oral presentation acceptance
 * Available variables: $submission object with all submission data
 */
?>
<html>
<head>
    <title>Hong Kong Occupational Therapy Conference 2025 – Acceptance of Oral Presentation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .content { padding: 20px; }
        .details { background-color: #f8f9fa; padding: 15px; margin: 15px 0; }
        .section { margin: 20px 0; }
        h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        h4 { color: #555; margin-top: 15px; }
        ul { padding-left: 20px; }
        li { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="content">
        <p><strong>Subject: Hong Kong Occupational Therapy Conference 2025 – Acceptance of Oral Presentation</strong></p>
        
        <p>Dear <?php echo esc_html($submission->title . ' ' . $submission->surname . ', ' . $submission->given_name); ?>,</p>
        
        <p>We are pleased to inform you that the following abstract has been accepted for ORAL presentation in the free paper session of the Hong Kong Occupational Therapy Conference 2025, to be held at The Salisbury – YMCA of Hong Kong on 6th December 2025.</p>
        
        <div class="details">
            <p><strong>Title:</strong> <?php echo esc_html($submission->abstract_title); ?></p>
            <p><strong>Paper Number:</strong> <?php echo esc_html($submission->submission_number ?? 'TBD'); ?></p>
            <p><strong>Presenting Author:</strong> <?php echo esc_html($submission->title . ' ' . $submission->surname . ', ' . $submission->given_name); ?></p>
        </div>
        
        <p>To be confirmed for the conference program, please accept this invitation by 31st October 2025 by email to conference@hkota.org.hk, with "PRESENTER ACCEPTS" and the "PAPER NUMBER" in the email subject. Failure to respond by this date may result in the withdrawal of this offer and removal from the program.</p>
        
        <p>The information noted above will be printed in the e-program book. If the presenter(s) is/are occupational therapist(s), you are REQUIRED to become a HKOTA full member and register for the conference before 9th November 2025 (Link). If the presenter(s) is/are not occupational therapist(s), conference registration is not required as the conference is for members only, and you will be unable to attend the conference except for the presentation session. If the presenter(s) listed above have changed, please notify us by email at the time of your acceptance.</p>
        
        <p>As the submitting or primary author of this abstract, all communication from us regarding this presentation will come to you directly. We ask that you pass on any relevant information to your co-presenters, as they will not receive any direct communication from us regarding this presentation.</p>
        
        <div class="section">
            <h3>Oral Presentation Format:</h3>
            <ul>
                <li>You will have 10 minutes for the oral presentation, followed by 2 minutes of discussion.</li>
                <li>It is required that you upload your presentation file(s) with the presenting author and paper number as the file name (e.g. CHAN Tai Man_2025-O-001) in PowerPoint or PDF format (&lt;10 mb file size) to the abstract submission system (<?php echo home_url('/abstract-submission/'); ?>) before the 6th December 2025</li>
            </ul>
        </div>
        
        <p>I would like to thank you again for your support and look forward to seeing you at the conference. Should you require further information regarding your presentation, you can reach Ms. Lydia YIP by email (conference@hkota.org.hk).</p>
        
        <p>Best regards,<br>
        Scientific Committee<br>
        Hong Kong Occupational Therapy Conference 2025</p>
    </div>
</body>
</html>