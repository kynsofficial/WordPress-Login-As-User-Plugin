<?php
/*
Plugin Name: aptLearn Login as User
Description: Adds a "Login as User" button to the wp-admin user page column and displays a sticky banner for admins to return back to the wp-admin user page and be logged in back as an admin originally developed and Licensed to aptLearn, feel free to use.
Version: 1.0
Author: Akinola A
Author URI: https://aptlearn.io/
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// Start session.
if ( session_status() === PHP_SESSION_NONE ) {
    session_start();
}

// Register activation and deactivation hooks.
// Include the download plugins function.
register_activation_hook( __FILE__, 'aptlearn_login_as_user_activate' );
register_deactivation_hook( __FILE__, 'aptlearn_login_as_user_deactivate' );


// Activation hook function.
function aptlearn_login_as_user_activate() {
    // Add any necessary activation code here.
}

// Deactivation hook function.
function aptlearn_login_as_user_deactivate() {
    // Add any necessary deactivation code here.
}

/**
 * Adds the "Login as User" button to the wp-admin user page column.
 *
 * @param array $columns Columns displayed in the wp-admin user page.
 * @return array Modified columns array.
 */
function aptlearn_login_as_user_column( $columns ) {
    $columns['aptlearn_login_as_user'] = __( 'Login as User', 'aptlearn-login-as-user' );
    return $columns;
}
add_filter( 'manage_users_columns', 'aptlearn_login_as_user_column' );

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

    $current_user = wp_get_current_user();
    if ( ! $current_user || ! current_user_can( 'administrator' ) ) {
        return;
    }

    $login_url = add_query_arg( 'aptlearn_login_as_user', $user_id, home_url() );
    $button = sprintf( '<a href="%s" class="button aptlearn-login-as-user">%s</a>', esc_url( $login_url ), __( 'Login as User', 'aptlearn-login-as-user' ) );

    return $button;
}
add_action( 'manage_users_custom_column', 'aptlearn_login_as_user_column_content', 10, 3 );

/**
 * Automatically logs in the user when the "Login as User" button is clicked.
 */
function aptlearn_login_as_user_auto_login() {
    if ( isset( $_GET['aptlearn_login_as_user'] ) ) {
        $user_id = intval( $_GET['aptlearn_login_as_user'] );
        $user = get_user_by( 'id', $user_id );
        if ( $user ) {
                       // Save the current user ID and the previous URL in the session variables.
            $_SESSION['aptlearn_login_as_user_current_user_id'] = get_current_user_id();
            $_SESSION['aptlearn_login_as_user_previous_url'] = ( isset( $_SERVER['HTTP_REFERER'] ) && !empty( $_SERVER['HTTP_REFERER'] ) ) ? $_SERVER['HTTP_REFERER'] : home_url();

            // Log in as the new user.
            wp_set_current_user( $user_id, $user->user_login );
            wp_set_auth_cookie( $user_id );
            do_action( 'wp_login', $user->user_login, $user );
            wp_redirect( home_url() );
            exit;
        }
    }
}
add_action( 'template_redirect', 'aptlearn_login_as_user_auto_login' );

/**
 * Automatically logs the admin back in when they click the "Login back as" button.
 */
function aptlearn_login_as_user_auto_login_back() {
    if ( isset( $_GET['aptlearn_login_back_as_admin'] ) ) {
        $user_id = intval( $_GET['aptlearn_login_back_as_admin'] );
        $user = get_user_by( 'id', $user_id );

        if ( $user && isset( $_SESSION['aptlearn_login_as_user_current_user_id'] ) && $_SESSION['aptlearn_login_as_user_current_user_id'] == $user_id ) {
            // Log in as the original admin user.
            wp_set_current_user( $user_id, $user->user_login );
            wp_set_auth_cookie( $user_id );
            do_action( 'wp_login', $user->user_login, $user );

            // Redirect to the previous page the admin was on.
            $previous_url = isset( $_SESSION['aptlearn_login_as_user_previous_url'] ) ? $_SESSION['aptlearn_login_as_user_previous_url'] : home_url();

            // Clear the session variables.
            unset( $_SESSION['aptlearn_login_as_user_current_user_id'] );
            unset( $_SESSION['aptlearn_login_as_user_previous_url'] );

            wp_redirect( $previous_url );
            exit;
        }
    }
}
add_action( 'template_redirect', 'aptlearn_login_as_user_auto_login_back' );

/**
 * Displays the login as user banner for all roles.
 */
function aptlearn_login_as_user_show_banner() {
    // Enqueue the plugin stylesheet.
    wp_enqueue_style( 'aptlearn-login-as-user-style', plugins_url( '/css/aptlearn-login-as-user-style.css', __FILE__ ), array(), '1.0', 'all' );

    // Check if the current user is logged in and has the necessary capabilities.
    $current_user = wp_get_current_user();
    if ( ! $current_user || ! current_user_can( 'read' ) ) {
        return;
    }

    // Check if a session has already been started before calling session_start().
    if ( session_status() == PHP_SESSION_NONE ) {
        session_start();
    }

    $previous_user_id = isset( $_SESSION['aptlearn_login_as_user_current_user_id'] ) ? $_SESSION['aptlearn_login_as_user_current_user_id'] : null;

    // If there is a previous user ID, display the banner.
    if ( $previous_user_id ) {
        $previous_user = get_user_by( 'id', $previous_user_id );
        $button_text = sprintf( __( 'Login back as %s', 'aptlearn-login-as-user' ), $previous_user->user_login );

                // Get the URL to log back in as the admin user.
        $login_back_url = add_query_arg( 'aptlearn_login_back_as_admin', $previous_user_id, home_url() );

        ?>
        <div id="aptlearn-login-as-user-banner">
            <p><?php printf( __( 'You are currently logged in as %s.', 'aptlearn-login-as-user' ), $current_user->user_login ); ?></p>
            <a href="<?php echo esc_url( $login_back_url ); ?>" class="button aptlearn-login-as-user-return"><?php echo esc_html( $button_text ); ?></a>
        </div>
        <?php
    }
}
add_action( 'wp_footer', 'aptlearn_login_as_user_show_banner' );


