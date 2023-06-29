// Display the "Login as User" button in the wp-admin user page column.
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
