<?php
/**
 * Login Page Template.
 *
 * @package WPAC
 */

defined( 'ABSPATH' ) || exit;

//phpcs:ignore WordPress.Security.NonceVerification.Missing -- This is a simple login form, nonce verification can be added in future iterations.
$redirect_to = '';

if ( ! empty( $_GET['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$redirect_to = esc_url_raw( $_GET['redirect_to'] );  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
} else {
	$redirect_to = home_url( '/wpac-platform/dashboard' );
}

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
	<?php wp_head(); ?>
</head>

<body>
	<div class="wpac-login-page">
		<div class="wpac-login-box">

			<h2>Kims Group Platform</h2>

			<?php if ( ! empty( $error_message ) ) : ?>
				<div class="wpac-login-error">
					<?php echo esc_html( $error_message ); ?>
				</div>
			<?php endif; ?>

			<form method="post">
				<?php wp_nonce_field( 'wpac_login_action', 'wpac_login_nonce' ); ?>
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">

				<input type="text" name="wpac_username" placeholder="Username" required>
				<input type="password" name="wpac_password" placeholder="Password" required>

				<button type="submit" name="wpac_login">
					Login
				</button>
			</form>

			<!-- Microsoft Login Button -->
			<!-- Microsoft Login Button -->
			<a class="wpac-login-ms" href="<?php echo esc_url( home_url( '/wpac-microsoft-login' ) ); ?>">
				<img src="<?php echo esc_url( WPAC_PLUGIN_URL ); ?>/assets/images/ms-logo.png" alt="Microsoft Logo" width="auto" height="20" class="wpac-ms-logo">
				Login with Microsoft
			</a>

		</div>
	</div>

	<?php wp_footer(); ?>
</body>

</html>
