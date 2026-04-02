

<div class="wpac-wrap">
	<div class="wpac-content">

		<div class="wpac-card">
			<div class="wpac-card-header">
				<h2>Scopes</h2>
				<p class="wpac-subtitle">Manage permission scopes</p>
			</div>

			<!-- Scope Form -->
			<div class="wpac-form-grid">
				<input type="hidden" id="wpac-scope-id-hidden">

				<div class="wpac-field">
					<label>Scope Name</label>
					<input id="wpac-scope-name" placeholder="e.g. Admin Access">
				</div>

				<div class="wpac-field">
					<label>Scope Slug</label>
					<input id="wpac-scope-slug" placeholder="e.g. admin_access">
				</div>
			</div>

			<div class="wpac-message" id="wpac-scope-message"></div>

			<!-- Assign Scope Section -->
			<div style="margin-top: 20px; font-weight: 600;">Assign Scope</div>
			<div class="wpac-tree">
				<label><input type="checkbox" id="wpac-global-checkbox"> Global</label>

				<?php foreach ( $entities as $entity ) : ?>
					<div class="wpac-tree-item">
						<label>
							<input type="checkbox" class="wpac-entity-checkbox" data-entity="<?php echo esc_attr( $entity->id ); ?>">
							<?php echo esc_html( $entity->name ); ?>
						</label>
						<?php if ( ! empty( $entity_sites[ $entity->id ] ) ) : ?>
							<div class="wpac-tree-sites" style="padding-left: 20px;">
								<?php foreach ( $entity_sites[ $entity->id ] as $site ) : ?>
									<label>
										<input type="checkbox" class="wpac-site-checkbox"
												data-entity="<?php echo esc_attr( $entity->id ); ?>"
												data-site="<?php echo esc_attr( $site->id ); ?>">
										<?php echo esc_html( $site->name ); ?>
									</label>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="wpac-actions" style="margin-top: 20px;">
				<button id="wpac-save-scope" class="button button-primary wpac-btn">Save Scope</button>
				<button id="wpac-cancel-scope" class="button wpac-btn" style="display:none;">Cancel</button>
			</div>
		</div>

		<!-- Display Saved Scopes -->
		<div class="wpac-card">
			<h3>Saved Scopes</h3>
			<ul id="wpac-scope-list" class="wpac-list">
				<?php if ( ! empty( $scopes ) ) : ?>
					<?php foreach ( $scopes as $s ) : ?>
						<li class="wpac-item"
							data-id="<?php echo esc_attr( $s->id ); ?>"
							data-name="<?php echo esc_attr( $s->name ); ?>"
							data-slug="<?php echo esc_attr( $s->slug ); ?>"
							data-config="<?php echo esc_attr( $s->config ); ?>">
							<div class="wpac-item-info">
								<strong><?php echo esc_html( $s->name ); ?></strong>
								<small><?php echo esc_html( $s->slug ); ?></small>
							</div>
							<div class="wpac-item-actions">
								<button class="wpac-edit-scope">Edit</button>
								<button class="wpac-delete-scope">Delete</button>
							</div>
						</li>
					<?php endforeach; ?>
				<?php else : ?>
					<li class="wpac-empty">No scopes found</li>
				<?php endif; ?>
			</ul>
		</div>

	</div>
</div>
