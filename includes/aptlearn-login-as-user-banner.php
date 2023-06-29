<?php
// Check if the current user is an administrator
if (current_user_can('administrator')) {
    // Check if there is a previous user ID saved in the session variable
    session_start();
    $previous_user_id = isset($_SESSION['aptlearn_login_as_user_current_user_id']) ? $_SESSION['aptlearn_login_as_user_current_user_id'] : null;

    // If there is a previous user ID, add the "Log Back In" banner
    if ($previous_user_id) {
        ?>
        <div id="aptlearn-login-as-user-banner">
            <p><?php _e('You are currently logged in as the previous user.', 'aptlearn-login-as-user'); ?></p>
            <a href="<?php echo wp_login_url(); ?>" class="button aptlearn-login-as-user-return"><?php _e('Log Back In', 'aptlearn-login-as-user'); ?></a>
        </div>
        <?php
    }
}
