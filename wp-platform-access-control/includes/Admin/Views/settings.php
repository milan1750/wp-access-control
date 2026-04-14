<?php

// Save settings.
if ( isset( $_POST['wrm_auth_nonce'] ) && wp_verify_nonce( $_POST['wrm_auth_nonce'], 'wrm_auth_save' ) ) {

	$settings = array(
		'tenant_id'     => sanitize_text_field( $_POST['tenant_id'] ?? '' ),
		'client_id'     => sanitize_text_field( $_POST['client_id'] ?? '' ),
		'client_secret' => sanitize_text_field( $_POST['client_secret'] ?? '' ),
		'redirect_uri'  => esc_url_raw( $_POST['redirect_uri'] ?? '' ),

		// ✅ NEW: Cron toggle
		'enable_cron'   => isset( $_POST['enable_cron'] ) ? 1 : 0,
	);

	update_option( 'wrm_auth_settings', $settings );

	$notice = 'Settings saved successfully.';
}

// Load existing settings.
$settings = get_option( 'wrm_auth_settings', array() );

$tenant_id     = $settings['tenant_id'] ?? '';
$client_id     = $settings['client_id'] ?? '';
$client_secret = $settings['client_secret'] ?? '';
$redirect_uri  = $settings['redirect_uri'] ?? '';

// ✅ NEW
$enable_cron   = $settings['enable_cron'] ?? 0;

?>

<div class="wpac-wrap">
	<div class="wpac-content">
		<div class="wpac-card">

			<!-- Header -->
			<div class="wpac-card-header">
				<h2>Authentication</h2>
				<p class="wpac-subtitle">Configure Microsoft OAuth login</p>
			</div>

			<?php if ( ! empty( $notice ) ) : ?>
				<div class="wpac-message success show">
					<?php echo esc_html( $notice ); ?>
				</div>
			<?php endif; ?>

			<form method="post" class="wpac-form">

				<?php wp_nonce_field( 'wrm_auth_save', 'wrm_auth_nonce' ); ?>

				<div class="wpac-form">

					<!-- Tenant ID -->
					<div class="wpac-field">
						<label>Tenant ID</label>
						<input type="text" name="tenant_id" value="<?php echo esc_attr( $tenant_id ); ?>" placeholder="Azure Tenant ID">
					</div>

					<!-- Client ID -->
					<div class="wpac-field">
						<label>Client ID</label>
						<input type="text" name="client_id" value="<?php echo esc_attr( $client_id ); ?>" placeholder="Application Client ID">
					</div>

					<!-- Client Secret -->
					<div class="wpac-field">
						<label>Client Secret</label>
						<input type="password" name="client_secret" value="<?php echo esc_attr( $client_secret ); ?>" placeholder="Client Secret">
					</div>

					<!-- Redirect URI -->
					<div class="wpac-field">
						<label>Redirect URI</label>
						<input type="text" name="redirect_uri" value="<?php echo esc_attr( $redirect_uri ); ?>" placeholder="<?php echo esc_url( home_url( '/login' ) ); ?>">
					</div>

					<!-- Enable Cron -->
					<div class="wpac-field">
						<label>
							<input type="checkbox" name="enable_cron" value="1" <?php checked( $enable_cron, 1 ); ?>>
							Enable Automatic Cron Jobs
						</label>
						<p class="description">
							Turn ON only in production. Disable in development.
						</p>
					</div>

				</div>

				<div class="wpac-actions">
					<button type="submit" class="button wpac-btn wpac-btn-primary">
						Save Settings
					</button>
				</div>

			</form>

		</div>
	</div>
</div>