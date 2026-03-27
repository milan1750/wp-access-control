<?php
global $wpdb;

// Fetch entities and sites
$entities = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpac_entities ORDER BY name ASC" );
$sites    = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpac_sites ORDER BY id DESC" );
?>

<div class="wpac-wrap">

	<div class="wpac-content">
		<div class="wpac-card">

			<h2>Sites</h2>

			<!-- ================= ENTITY ================= -->
			<div class="wpac-field">
				<label>Entity</label>
				<select id="wpac-site-entity">
					<option value="">Select entity</option>
					<?php foreach ( $entities as $e ) : ?>
						<option value="<?php echo esc_attr( $e->id ); ?>">
							<?php echo esc_html( $e->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- ================= SITE ID (MANUAL / EXTERNAL) ================= -->
			<div class="wpac-field">
				<label>Site ID (Manual / External)</label>
				<input id="wpac-site-id" type="text" placeholder="Enter Site ID">
			</div>

			<!-- ================= SITE NAME ================= -->
			<div class="wpac-field">
				<label>Site Name</label>
				<input id="wpac-site-name" type="text" placeholder="Site name">
			</div>

			<!-- ================= SITE SLUG ================= -->
			<div class="wpac-field">
				<label>Site Slug</label>
				<input id="wpac-site-slug" type="text" placeholder="Auto generated slug">
			</div>

			<!-- ================= LOCATION ================= -->
			<div class="wpac-field">
				<label>Location</label>
				<input id="wpac-site-location" type="text" placeholder="Location">
			</div>

			<!-- ================= HIDDEN FIELD FOR EDIT ================= -->
			<input type="hidden" id="wpac-site-id-hidden">

			<!-- ================= BUTTONS ================= -->
			<div class="wpac-actions">
				<button id="wpac-save-site" class="button button-primary">Add Site</button>
				<button id="wpac-cancel-site" class="button" style="display:none;">Cancel</button>
			</div>

			<hr>

			<!-- ================= SITE LIST ================= -->
			<ul id="wpac-site-list" class="wpac-list">
				<?php if ( ! empty( $sites ) ) : ?>
					<?php foreach ( $sites as $s ) : ?>
						<li class="wpac-item"
							data-id="<?php echo esc_attr( $s->id ); ?>"
							data-entity="<?php echo esc_attr( $s->entity_id ); ?>"
							data-siteid="<?php echo esc_attr( $s->site_id ); ?>"
							data-name="<?php echo esc_attr( $s->name ); ?>"
							data-slug="<?php echo esc_attr( $s->slug ); ?>"
							data-location="<?php echo esc_attr( $s->location ); ?>">

							<div class="wpac-item-info">
								<strong><?php echo esc_html( $s->name ); ?></strong><br>
								<small><?php echo esc_html( $s->location ); ?></small><br>
								<small><b>Site ID:</b> <?php echo esc_html( $s->site_id ); ?> • <b>Slug:</b> <?php echo esc_html( $s->slug ); ?></small>
							</div>

							<div class="wpac-item-actions">
								<button class="wpac-edit-site button">Edit</button>
								<button class="wpac-delete button"
										data-type="site"
										data-id="<?php echo esc_attr( $s->id ); ?>">
									Delete
								</button>
							</div>

						</li>
					<?php endforeach; ?>
				<?php else : ?>
					<li class="wpac-empty">No sites found</li>
				<?php endif; ?>
			</ul>

		</div>
	</div>
</div>
