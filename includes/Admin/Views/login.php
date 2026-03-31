<?php
defined( 'ABSPATH' ) || exit;

$error_message = '';

// Determine the redirect URL
$redirect_to = ! empty( $_GET['redirect_to'] ) ? esc_url_raw( $_GET['redirect_to'] ) : home_url( '/wpac-platform/dashboard' );

// Handle login form submission
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['wpac_login'] ) ) {

	// Security check
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wpac_login_nonce' ) ) {
		$error_message = 'Security verification failed.';
	} else {

		$username    = sanitize_text_field( $_POST['wpac_username'] ?? '' );
		$password    = $_POST['wpac_password'] ?? '';
		$redirect_to = ! empty( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : $redirect_to;

		if ( empty( $username ) || empty( $password ) ) {
			$error_message = 'Username and password are required.';
		} else {

			$creds = array(
				'user_login'    => $username,
				'user_password' => $password,
				'remember'      => true,
			);

			$user = wp_signon( $creds, false );

			if ( is_wp_error( $user ) ) {
				$error_message = $user->get_error_message();
			} else {

				// Login successful
				wp_set_current_user( $user->ID );
				wp_set_auth_cookie( $user->ID );

				// Redirect to original page
				wp_safe_redirect( $redirect_to );
				exit;
			}
		}
	}
}

// If already logged in, redirect to dashboard or original page
if ( is_user_logged_in() ) {
	wp_safe_redirect( $redirect_to );
	exit;
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Platform Login</title>
	<style>
		body{
			font-family: Arial;
			background:#f3f4f6;
			display:flex;
			justify-content:center;
			align-items:center;
			height:100vh;
		}
		.login-box{
			width:350px;
			background:#fff;
			padding:30px;
			border-radius:8px;
			box-shadow:0 5px 15px rgba(0,0,0,.1);
		}
		h2{
			text-align:center;
			margin-bottom:20px;
		}
		input{
			width:100%;
			padding:10px;
			margin-bottom:15px;
			border:1px solid #ccc;
			border-radius:4px;
		}
		button{
			width:100%;
			padding:10px;
			background:#0073aa;
			color:#fff;
			border:none;
			border-radius:4px;
			cursor:pointer;
		}
		button:hover{
			background:#005f8d;
		}
		.error{
			background:#ffe5e5;
			color:#a00;
			padding:10px;
			margin-bottom:15px;
			border-radius:4px;
		}
	</style>
</head>
<body>

<div class="login-box">

	<h2>Platform Login</h2>

	<?php if ( ! empty( $error_message ) ) : ?>
		<div class="error">
			<?php echo esc_html( $error_message ); ?>
		</div>
	<?php endif; ?>

	<form method="post">
		<?php wp_nonce_field( 'wpac_login_nonce' ); ?>
		<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">

		<input
			type="text"
			name="wpac_username"
			placeholder="Username"
			required
		>

		<input
			type="password"
			name="wpac_password"
			placeholder="Password"
			required
		>

		<button type="submit" name="wpac_login">
			Login
		</button>
	</form>

</div>

</body>
</html>
