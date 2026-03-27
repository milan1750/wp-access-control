<?php
global $wpdb;

// Fetch entities
$table    = $wpdb->prefix . 'wpac_entities';
$entities = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC" );
?>

<div class="wpac-wrap">
	<div class="wpac-content">
		<div class="wpac-card">

			<div class="wpac-card-header">
				<h2>Entities</h2>
				<p class="wpac-subtitle">Manage system entities</p>
			</div>

			<div class="wpac-message" id="wpac-entity-message"></div>

			<div class="wpac-form-grid">

				<!-- HIDDEN FIELD FOR EDIT -->
				<input type="hidden" id="wpac-entity-id">

				<!-- ENTITY NAME -->
				<div class="wpac-field">
					<label>Entity Name</label>
					<input type="text" id="wpac-entity-name" placeholder="Enter entity name">
				</div>

				<!-- ENTITY SLUG -->
				<div class="wpac-field">
					<label>Entity Slug</label>
					<input type="text" id="wpac-entity-slug" placeholder="Auto generated slug">
				</div>

				<!-- ENTITY STATUS -->
				<div class="wpac-field">
					<label>Status</label>
					<select id="wpac-entity-status">
						<option value="1">Active</option>
						<option value="0">Inactive</option>
					</select>
				</div>

				<!-- ACTION BUTTONS -->
				<div class="wpac-actions">
					<button id="wpac-save-entity" class="button wpac-btn wpac-btn-primary">
						Add Entity
					</button>
					<button id="wpac-cancel-entity" class="button wpac-btn wpac-btn-secondary" style="display:none;">
						Cancel
					</button>
				</div>

			</div>

			<hr class="wpac-divider">

			<!-- ENTITY LIST -->
			<ul id="wpac-entity-list" class="wpac-list">

				<?php if ( ! empty( $entities ) ) : ?>
					<?php foreach ( $entities as $e ) : ?>
						<li class="wpac-item"
							data-id="<?php echo esc_attr( $e->id ); ?>"
							data-name="<?php echo esc_attr( $e->name ); ?>"
							data-slug="<?php echo esc_attr( $e->slug ); ?>"
							data-status="<?php echo esc_attr( $e->status ); ?>">

							<div class="wpac-item-info">
								<strong><?php echo esc_html( $e->name ); ?></strong>
								<small><?php echo esc_html( $e->slug ); ?> • <?php echo $e->status ? 'Active' : 'Inactive'; ?></small>
							</div>

							<div class="wpac-item-actions">
								<button class="wpac-edit-entity button">Edit</button>
								<button class="wpac-delete-entity button">Delete</button>
							</div>

						</li>
					<?php endforeach; ?>
				<?php else : ?>
					<li class="wpac-empty">No entities found</li>
				<?php endif; ?>

			</ul>

		</div>
	</div>
</div>
