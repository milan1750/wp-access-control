<?php
/**
 * Entities Admin Template.
 *
 * Variables available:
 *   - $entities
 *
 * @package WPAC
 */
?>

<div class="wpac-wrap">
	<div class="wpac-content">
		<div class="wpac-card">

			<div class="wpac-card-header">
				<h2>Entities</h2>
				<p class="wpac-subtitle">Manage system entities</p>
			</div>

			<?php wp_nonce_field( 'wpac_entity_action', 'wpac_entity_nonce' ); ?>

			<div class="wpac-message" id="wpac-entity-message"></div>

			<div class="wpac-form-grid">
				<input type="hidden" id="wpac-entity-id">

				<div class="wpac-field">
					<label for="wpac-entity-name">Entity Name</label>
					<input type="text" id="wpac-entity-name" placeholder="Enter entity name" autocomplete="off">
				</div>

				<div class="wpac-field">
					<label for="wpac-entity-slug">Entity Slug</label>
					<input type="text" id="wpac-entity-slug" placeholder="Auto generated slug" autocomplete="off">
				</div>

				<div class="wpac-field">
					<label for="wpac-entity-status">Status</label>
					<select id="wpac-entity-status">
						<option value="1">Active</option>
						<option value="0">Inactive</option>
					</select>
				</div>

				<div class="wpac-actions">
					<button type="button" id="wpac-save-entity" class="button wpac-btn wpac-btn-primary">Add Entity</button>
					<button type="button" id="wpac-cancel-entity" class="button wpac-btn wpac-btn-secondary" style="display:none;">Cancel</button>
				</div>
			</div>

			<hr class="wpac-divider">

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
								<button type="button" class="button wpac-edit-entity">Edit</button>
								<button type="button" class="button wpac-delete-entity">Delete</button>
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
