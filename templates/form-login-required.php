<?php
/**
 * Template for login required message
 * Variables available: $login_url
 */
?>

<div class="hkota-abstract-login-required">
    <p>You must be logged in to submit an abstract.</p>
    <a href="<?php echo esc_url($login_url); ?>" class="button">Login</a>
</div>
