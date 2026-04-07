<div class="wpac-wrap">

	<!-- SIDEBAR USERS -->
	<div class="wpac-sidebar">

		<div class="wpac-header">
			<h2>Access Control</h2>
		</div>

		<div class="wpac-user-list">

			<?php foreach ($users as $user) : ?>

				<a href="#" class="wpac-user-card" data-user="<?php echo esc_attr($user->ID); ?>">

					<div class="wpac-avatar">
						<?php echo get_avatar($user->ID, 32); ?>
					</div>

					<div class="wpac-user-info">
						<strong><?php echo esc_html($user->display_name); ?></strong>
						<small><?php echo esc_html($user->user_email); ?></small>
					</div>

				</a>

			<?php endforeach; ?>

		</div>

	</div>

	<!-- MAIN PANEL -->
	<div class="wpac-content">

		<div id="wpac-empty" class="wpac-empty">
			Select a user to manage access
		</div>

		<div id="wpac-editor" style="display:none;">

			<input type="hidden" id="wpac-user-id">

			<h2>User Access Control</h2>

			<!-- ROLE -->
			<div class="wpac-card">

				<label for="wpac-role">Role</label>

				<select id="wpac-role">

					<option value="">-- Select Role --</option>

					<?php foreach ($roles as $role) : ?>

						<option value="<?php echo esc_attr($role->slug); ?>">
							<?php echo esc_html($role->name); ?>
						</option>

					<?php endforeach; ?>

				</select>

			</div>

			<!-- SCOPE -->
			<div class="wpac-card">

				<label for="wpac-scope">Scope</label>

				<select id="wpac-scope">

					<option value="global">Global</option>

					<?php foreach ($scopes as $scope) : ?>

						<option value="<?php echo esc_attr($scope->slug); ?>">
							<?php echo esc_html($scope->name); ?>
						</option>

					<?php endforeach; ?>

				</select>

			</div>

			<!-- PERMISSIONS -->
			<div class="wpac-card">

				<h3>Permissions Override</h3>

				<?php foreach ($modules as $module_key => $module) : ?>
					<div class="wpac-module" data-module="<?php echo esc_attr($module_key); ?>">
						<h4><?php echo esc_html($module['label']); ?></h4>
						<div class="wpac-module-body">
							<label>
								<input type="checkbox"
									class="wpac-module-checkbox"
									data-module="<?php echo esc_attr($module_key); ?>">
								Select All
							</label>

							<?php foreach ($module['capabilities'] as $capability_key => $capability_label) : ?>
								<label>
									<input type="checkbox"
										class="wpac-capability-checkbox"
										data-module="<?php echo esc_attr($module_key); ?>"
										value="<?php echo esc_attr($capability_key); ?>">
									<?php echo esc_html($capability_label); ?>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>

			</div>

			<!-- ACTIONS -->
			<div class="wpac-actions">

				<button type="button" id="wpac-save" class="button button-primary">
					Save Access
				</button>

				<button type="button" id="wpac-revoke" class="button">
					Revoke
				</button>

			</div>

		</div>

	</div>

</div>
