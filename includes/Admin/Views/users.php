<?php defined( 'ABSPATH' ) || exit; ?>

<?php
global $wpdb;

// --- USERS ---
$users = get_users();

// --- ROLES ---
$roles_table    = $wpdb->prefix . 'wpac_roles';
$role_cap_table = $wpdb->prefix . 'wpac_role_capabilities';
$roles          = $wpdb->get_results( "SELECT * FROM {$roles_table} ORDER BY id DESC" );

// --- SCOPES ---
$scopes_table = $wpdb->prefix . 'wpac_scopes';
$scopes       = $wpdb->get_results( "SELECT * FROM {$scopes_table} ORDER BY id DESC" );

// --- CAPABILITIES ---
$allCaps = apply_filters( 'wpac_get_capabilities', array() ); // ['key' => ['label'=>'Label','module'=>'module_name']]

// Organize capabilities by module
$modules = array();
foreach ( $allCaps as $key => $cap ) {
	$module = $cap['module'] ?: 'general';
	if ( ! isset( $modules[ $module ] ) ) {
		$modules[ $module ] = array(
			'label'        => ucfirst( $module ),
			'capabilities' => array(),
		);
	}
	$modules[ $module ]['capabilities'][ $key ] = $cap['label'];
}

// --- Role Capabilities ---
$roles_caps = array();
foreach ( $roles as $role ) {
	$caps                      = $wpdb->get_col(
		$wpdb->prepare( "SELECT capability FROM {$role_cap_table} WHERE role_id = %d", $role->id )
	);
	$roles_caps[ $role->slug ] = $caps;
}

// --- User Overrides ---
$user_overrides = array();
foreach ( $users as $user ) {
	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}wpac_user_capabilities WHERE user_id = %d",
			$user->ID
		),
		ARRAY_A
	);

	if ( $row ) {
		$capabilities = $row['capabilities'] ? json_decode( $row['capabilities'], true ) : array();

		if ( ! is_array( $capabilities ) ) {
			$capabilities = array();
		}

		$user_overrides[ $user->ID ] = array(
			'role'         => $row['role'],
			'scope'        => $row['scope'] ?? 'global',
			'capabilities' => $capabilities, // flat array
		);
	}
}

?>

<!-- Inject JS data -->
<script>
window.WPAC = window.WPAC || {};
WPAC.rolesCaps = <?php echo wp_json_encode( $roles_caps ); ?>;
WPAC.userOverrides = <?php echo wp_json_encode( $user_overrides ); ?>;
</script>

<div class="wpac-wrap">

	<!-- SIDEBAR: USER LIST -->
	<div class="wpac-sidebar">
		<div class="wpac-header">
			<h2>Access Control</h2>
		</div>
		<div class="wpac-user-list">
			<?php foreach ( $users as $user ) : ?>
				<a href="#" class="wpac-user-card" data-user="<?php echo esc_attr( $user->ID ); ?>">
					<div class="wpac-avatar"><?php echo get_avatar( $user->ID, 32 ); ?></div>
					<div class="wpac-user-info">
						<strong><?php echo esc_html( $user->display_name ); ?></strong>
						<small><?php echo esc_html( $user->user_email ); ?></small>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- MAIN PANEL -->
	<div class="wpac-content">
		<div id="wpac-empty" class="wpac-empty">Select a user to manage access</div>

		<div id="wpac-editor" style="display:none;">
			<input type="hidden" id="wpac-user-id">

			<h2>User Access Control</h2>

			<!-- ROLE -->
			<div class="wpac-card">
				<label for="wpac-role">Role</label>
				<select id="wpac-role">
					<option value="">-- Select Role --</option>
					<?php foreach ( $roles as $role ) : ?>
						<option value="<?php echo esc_attr( $role->slug ); ?>"><?php echo esc_html( $role->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- SCOPE -->
			<div class="wpac-card">
				<label for="wpac-scope">Scope</label>
				<select id="wpac-scope">
					<option value="global">Global</option>
					<?php foreach ( $scopes as $scope ) : ?>
						<option value="<?php echo esc_attr( $scope->slug ); ?>"><?php echo esc_html( $scope->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- PERMISSIONS -->
			<div class="wpac-card">
				<h3>Permissions (Override)</h3>
				<?php foreach ( $modules as $module_key => $module ) : ?>
					<div class="wpac-module" data-module="<?php echo esc_attr( $module_key ); ?>">
						<h4><?php echo esc_html( $module['label'] ); ?></h4>
						<div class="wpac-module-body">
							<label>
								<input type="checkbox" class="wpac-module-checkbox" data-module="<?php echo esc_attr( $module_key ); ?>">
								Select All
							</label>
							<?php foreach ( $module['capabilities'] as $capKey => $capLabel ) : ?>
								<label>
									<input type="checkbox"
											class="wpac-capability-checkbox"
											data-module="<?php echo esc_attr( $module_key ); ?>"
											value="<?php echo esc_attr( $capKey ); ?>">
									<?php echo esc_html( $capLabel ); ?>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- ACTIONS -->
			<div class="wpac-actions">
				<button type="button" id="wpac-save" class="button button-primary">Save Access</button>
				<button type="button" id="wpac-revoke" class="button">Revoke</button>
			</div>
		</div>
	</div>
</div>
