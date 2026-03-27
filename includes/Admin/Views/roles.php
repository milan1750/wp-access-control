<?php
global $wpdb;

// Fetch roles
$roles = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpac_roles ORDER BY id DESC" );

// Fetch all capabilities via filter
$allCaps = apply_filters( 'wpac_get_capabilities', array() );

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
?>

<div class="wpac-wrap">
	<div class="wpac-content">
		<div class="wpac-card">

			<!-- Header -->
			<div class="wpac-card-header">
				<h2>Roles</h2>
				<p class="wpac-subtitle">Manage roles and capabilities</p>
			</div>

			<!-- Form -->
			<div class="wpac-form-grid">
				<input type="hidden" id="wpac-role-id">

				<div class="wpac-field">
					<label>Role Name</label>
					<input type="text" id="wpac-role-name" placeholder="Enter role name">
				</div>

				<div class="wpac-field">
					<label>Role Slug</label>
					<input type="text" id="wpac-role-slug" placeholder="Auto generated slug">
				</div>

				<div class="wpac-field" style="grid-column:1/-1;">
					<label>Capabilities</label>
					<div class="wpac-capabilities">
						<?php foreach ( $modules as $modKey => $mod ) : ?>
							<div class="wpac-module">
								<div class="wpac-module-title">
									<label>
										<input type="checkbox" class="wpac-checkbox wpac-module-checkbox" data-module="<?php echo esc_attr( $modKey ); ?>">
										<strong><?php echo esc_html( $mod['label'] ); ?></strong>
									</label>
								</div>
								<div class="wpac-module-caps" style="padding-left:20px;">
									<?php foreach ( $mod['capabilities'] as $capKey => $capLabel ) : ?>
										<label>
											<input type="checkbox" class="wpac-checkbox wpac-capability-checkbox" value="<?php echo esc_attr( $capKey ); ?>" data-module="<?php echo esc_attr( $modKey ); ?>">
											<?php echo esc_html( $capLabel ); ?>
										</label>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<div class="wpac-actions" style="margin-top:1rem;">
				<button id="wpac-save-role" class="button wpac-btn wpac-btn-primary">Add Role</button>
				<button id="wpac-cancel-role" class="button wpac-btn wpac-btn-secondary" style="display:none;">Cancel</button>
			</div>

			<hr class="wpac-divider">

			<ul id="wpac-role-list" class="wpac-list">
				<?php
				foreach ( $roles as $role ) :
					$assignedCaps = $wpdb->get_col(
						$wpdb->prepare(
							"SELECT capability FROM {$wpdb->prefix}wpac_role_capabilities WHERE role_id = %d",
							$role->id
						)
					);
					?>
					<li class="wpac-item"
						data-id="<?php echo esc_attr( $role->id ); ?>"
						data-name="<?php echo esc_attr( $role->name ); ?>"
						data-slug="<?php echo esc_attr( $role->slug ); ?>"
						data-caps='<?php echo wp_json_encode( $assignedCaps ); ?>'>
						<div class="wpac-item-info">
							<strong><?php echo esc_html( $role->name ); ?></strong>
							<small><?php echo esc_html( $role->slug ); ?></small>
						</div>
						<div class="wpac-item-actions">
							<button class="wpac-edit-role button">Edit</button>
							<button class="wpac-delete-role button">Delete</button>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>

		</div>
	</div>
</div>
