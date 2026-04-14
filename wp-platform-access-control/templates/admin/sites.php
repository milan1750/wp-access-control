<?php
/**
 * Sites Admin Template.
 *
 * Variables available:
 *   - $sites (array of site objects)
 *   - $entities (array of entity objects)
 *
 * @package WPAC
 */

?>

<div class="wpac-wrap">
	<div class="wpac-content">
		<div class="wpac-card">

			<div class="wpac-card-header">
				<h2>Sites</h2>
				<p class="wpac-subtitle">Manage system sites</p>
			</div>

			<?php wp_nonce_field( 'wpac_site_action', 'wpac_site_nonce' ); ?>

			<div class="wpac-message" id="wpac-site-message"></div>

			<div class="wpac-form-grid">

				<!-- HIDDEN FIELD FOR EDIT -->
				<input type="hidden" id="wpac-site-id-hidden">

				<!-- ENTITY -->
				<div class="wpac-field">
					<label for="wpac-site-entity">Entity</label>
					<select id="wpac-site-entity">
						<option value="">Select entity</option>
						<?php foreach ( $entities as $e ) : ?>
							<option value="<?php echo esc_attr( $e->id ); ?>">
								<?php echo esc_html( $e->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<!-- SITE ID -->
				<div class="wpac-field">
					<label for="wpac-site-id">Site ID (Manual / External)</label>
					<input
						type="text"
						id="wpac-site-id"
						placeholder="Enter site ID"
						autocomplete="off">
				</div>

				<!-- SITE NAME -->
				<div class="wpac-field">
					<label for="wpac-site-name">Site Name</label>
					<input
						type="text"
						id="wpac-site-name"
						placeholder="Enter site name"
						autocomplete="off">
				</div>

				<!-- SITE SLUG -->
				<div class="wpac-field">
					<label for="wpac-site-slug">Site Slug</label>
					<input
						type="text"
						id="wpac-site-slug"
						placeholder="Auto generated slug"
						autocomplete="off">
				</div>

				<!-- LOCATION -->
				<div class="wpac-field">
					<label for="wpac-site-location">Location</label>
					<input
						type="text"
						id="wpac-site-location"
						placeholder="Location"
						autocomplete="off">
				</div>

				<!-- ACTION BUTTONS -->
				<div class="wpac-actions">
					<button
						type="button"
						id="wpac-save-site"
						class="button wpac-btn wpac-btn-primary">
						Add Site
					</button>

					<button
						type="button"
						id="wpac-cancel-site"
						class="button wpac-btn wpac-btn-secondary"
						style="display:none;">
						Cancel
					</button>
				</div>

			</div>

			<hr class="wpac-divider">

			<!-- SITE LIST -->
			<ul id="wpac-site-list" class="wpac-list">

				<?php if ( ! empty( $sites ) ) : ?>

					<?php foreach ( $sites as $s ) : ?>

						<li
							class="wpac-item"
							data-id="<?php echo esc_attr( $s->id ); ?>"
							data-entity="<?php echo esc_attr( $s->entity_id ); ?>"
							data-siteid="<?php echo esc_attr( $s->site_id ); ?>"
							data-name="<?php echo esc_attr( $s->name ); ?>"
							data-slug="<?php echo esc_attr( $s->slug ); ?>"
							data-location="<?php echo esc_attr( $s->location ); ?>">

							<div class="wpac-item-info">
								<strong><?php echo esc_html( $s->name ); ?></strong>

								<small>
									Entity: <?php echo esc_html( $s->entity_id ); ?>
								</small>

								<small>
									Site ID: <?php echo esc_html( $s->site_id ); ?> •
									Slug: <?php echo esc_html( $s->slug ); ?> •
									Location: <?php echo esc_html( $s->location ); ?>
								</small>
							</div>

							<div class="wpac-item-actions">
								<button
									type="button"
									class="button wpac-edit-site">
									Edit
								</button>

								<button
									type="button"
									class="button wpac-delete-site">
									Delete
								</button>
							</div>

						</li>

					<?php endforeach; ?>

				<?php else : ?>

					<li class="wpac-empty">
						No sites found
					</li>

				<?php endif; ?>

			</ul>

		</div>
	</div>
</div>
