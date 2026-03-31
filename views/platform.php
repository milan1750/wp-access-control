<?php
defined( 'ABSPATH' ) || exit;

$user            = wp_get_current_user();
$registered_apps = apply_filters( 'wpac_get_registered_apps', array() );

$current_app = isset( $_GET['app'] ) ? sanitize_key( $_GET['app'] ) : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>WPAC Platform</title>

	<?php wp_head(); ?>

	<link rel="stylesheet" href="<?php echo esc_url( WPAC_PLUGIN_URL . 'assets/platform.css' ); ?>">
	<script src="<?php echo esc_url( includes_url( '/js/jquery/jquery.js' ) ); ?>"></script>

</head>

<body id="wpac-platform-<?php echo esc_attr( $current_app ); ?>">

	<div id="wpac-platform-container">

		<header>

			<div class="platform-left">
				<button id="sidebar-toggle">☰</button>
				<span class="platform-title">Platform</span>
			</div>

			<div class="user-menu">
				<img src="<?php echo esc_url( get_avatar_url( $user->ID ) ); ?>" class="user-avatar">

				<div class="dropdown-content">
					<a href="#">Profile</a>
					<a href="<?php echo esc_url( home_url( '/wpac-platform/logout' ) ); ?>">Logout</a>
				</div>

			</div>

		</header>


		<div class="platform-layout">

			<!-- SIDEBAR -->
			<aside id="platform-sidebar" class="collapsed">

				<h3 class="sidebar-title">Apps</h3>

				<div class="sidebar-app-list">

					<?php
					foreach ( $registered_apps as $app ) :

						$active = ( $current_app === $app['slug'] ) ? 'active' : '';
						$url    = add_query_arg( 'app', $app['slug'], home_url( '/wpac-platform' ) );

						?>

						<a href="<?php echo esc_url( $url ); ?>" class="app-card <?php echo esc_attr( $active ); ?>">

							<img src="<?php echo esc_url( $app['icon'] ); ?>">

							<span><?php echo esc_html( $app['name'] ); ?></span>

						</a>

					<?php endforeach; ?>

				</div>

			</aside>


			<!-- MAIN -->
			<main id="app-body">

				<?php
				if ( $current_app === 'dashboard' ) {
					echo '<h2>Welcome to WPAC Platform</h2>';
				}
				do_action( 'wpac_render_app_' . $current_app );
				?>

			</main>

		</div>

	</div>


	<?php wp_footer(); ?>

</body>

</html>
