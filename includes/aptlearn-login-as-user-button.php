<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Displays the "Login as User" button for each user in the wp-admin user page column.
 *
 * @param string $value The output that would normally be displayed in the wp-admin user page column.
 * @param string $column_name The name of the column being displayed.
 * @param int    $user_id The ID of the current user.
 * @return string Modified output to include the "Login as User" button.
 */
function aptlearn_login_as_user_column_content( $value, $column_name, $user_id ) {

    if ( 'aptlearn_login_as_user' !== $column_name ) {
        return $value;
    }

    if ( ! current_user_can( 'administrator' ) ) {
        return;
    }

    $login_url = wp_login_url() . '?aptlearn_login_as_user=' . $user_id;
    $button    = sprintf( '<a href="%s" class="button aptlearn-login-as-user">%s</a>', esc_url( $login_url ), __( 'Login as User', 'aptlearn-login-as-user' ) );

    return $button;
}

add_action( 'manage_users_custom_column', 'aptlearn_login_as_user_column_content', 10, 3 );
