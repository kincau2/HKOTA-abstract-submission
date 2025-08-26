<?php
/**
 * Template for login required message
 * Variables available: $login_url
 */
?>

<div class="hkota-abstract-login-required">
    <h3>Login Required</h3>
    <p>You must be logged in to submit an abstract.</p>
    <p>After logging in (or registering a new account), you will be automatically redirected back to this form.</p>
    
    <div class="hkota-login-actions">
        <a href="<?php echo esc_url($login_url); ?>" class="button button-primary">Login</a>
    </div>
</div>