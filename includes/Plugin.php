<?php
/**
 * Main Plugin File.
 *
 * @package WP_Platform_Access_Control
 */

namespace WPAC;

defined( 'ABSPATH' ) || exit;

use WPAC\Admin\Menu;
use WPAC\Ajax\AccessAjax;
use WPAC\Core\Activator;
use WPAC\Core\CapabilityRegistry;
use WPAC\Core\Deactivator;
use WPAC\Core\Upgrader;
use WPAC\Services\AccessManager;
use WPAC\Services\EntityManager;
use WPAC\Services\RoleManager;
use WPAC\Services\ScopeManager;
use WPAC\Services\SiteManager;
use WPAC\Services\AuthManager;
use WPAC\Core\Router;

/**
 * Main Plugin Bootstrap Class.
 *
 * Responsible for initializing services and exposing global access APIs.
 */
class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Access manager instance.
	 *
	 * @var AccessManager
	 */
	private AccessManager $access_manager;


	/**
	 * Access manager instance.
	 *
	 * @var AuthManager
	 */
	private AuthManager $auth_manager;



	/**
	 * Access manager instance.
	 *
	 * @var EntityManager
	 */
	private EntityManager $entity_manager;

	/**
	 * Access manager instance.
	 *
	 * @var SiteManager
	 */
	private SiteManager $site_manager;



	/**
	 * Role manager instance.
	 *
	 * @var RoleManager
	 */
	private RoleManager $role_manager;

	/**
	 * Scope manager instance.
	 *
	 * @var ScopeManager
	 */
	private ScopeManager $scope_manager;

	/**
	 * Capability Registry.
	 *
	 * @since 1.0.0
	 * @var CapabilityRegistry $registry
	 */
	private CapabilityRegistry $registry;

	/**
	 * Menu
	 *
	 * @since 1.0.0
	 * @var Menu
	 */
	private Menu $menu;

	/**
	 * Get plugin instance.
	 *
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize plugin.
	 *
	 * @return void
	 */
	public static function init(): void {
		self::instance()->boot();
	}

	/**
	 * Boot plugin services.
	 *
	 * @return void
	 */
	private function boot(): void {
		$this->register_services();
		$this->register_menu();
		$this->register_hooks();
	}

	/**
	 * Redirect to dashboard if homepage is accessed.
	 *
	 * @return void
	 */
	public function homepage_redirect() {
		if ( ! current_user_can( 'manage_options' ) && ! wp_doing_ajax() ) {
			wp_safe_redirect( home_url() );
			exit;
		}
	}

	/**
	 * Register admin menu.
	 *
	 * @since 1.0.0
	 */
	public function register_menu(): void {
		$this->menu = new Menu();
		$this->menu->init();
	}

	/**
	 * Render Dashboard.
	 *
	 * @since 1.0.0
	 */
	public function render_dashboard(): void {
		$this->load_view( 'dashboard' );
	}

	/**
	 * Load View.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $view View.
	 */
	private function load_view( string $view ): void {
		$file = WPAC_PLUGIN_DIR . "/admin/views/{$view}.php";

		if ( file_exists( $file ) ) {
			include $file;
		} else {
			echo "<div class='notice notice-error'>View not found: {" . esc_html( $view ) . '}</div>';
		}
	}

	/**
	 * Register core services.
	 *
	 * @return void
	 */
	private function register_services(): void {
		$this->auth_manager   = new AuthManager();
		$this->entity_manager = new EntityManager();
		$this->site_manager   = new SiteManager();
		$this->role_manager   = new RoleManager();
		$this->scope_manager  = new ScopeManager();
		$this->registry       = new CapabilityRegistry();
		$this->access_manager = new AccessManager(
			$this->role_manager,
			$this->scope_manager
		);
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	private function register_hooks(): void {
		add_action( 'init', array( $this, 'on_init' ) );
		// Block all non-platform pages.
		add_action(
			'template_redirect',
			function () {
				$allowed = array(
					'/wpac-platform',
					'/wpac-platform/login',
					'/wpac-platform/logout',
				);

				$path = '/' . trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

				if ( ! in_array( $path, $allowed ) ) {
					if ( ! is_user_logged_in() ) {
						wp_safe_redirect( home_url( '/wpac-platform/login' ) );
					} else {
						wp_safe_redirect( home_url( '/wpac-platform' ) );
					}
					exit;
				}
			}
		);
		// Restrict wp-admin for non-admins.
		add_action(
			'admin_init',
			function () {
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_safe_redirect( home_url( '/wpac-platform' ) );
					exit;
				}
			}
		);
		add_filter( 'show_admin_bar', '__return_false' ); // optional.
	}

	/**
	 * Runs on WordPress init hook.
	 *
	 * @return void
	 */
	public function on_init(): void {
		AccessAjax::init();
		Router::init();
		do_action( 'wpac_loaded' );
	}

	/**
	 * Plugin activation.
	 *
	 * @return void
	 */
	public static function activate(): void {
		Activator::run();
	}

	/**
	 * Plugin deactivation.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		Deactivator::run();
	}

	/**
	 * Plugin upgrade handler.
	 *
	 * @param string|null $old_version Old version.
	 * @param string|null $new_version New version.
	 * @return void
	 */
	public static function upgrade( $old_version, $new_version ): void {
		Upgrader::run( $old_version, $new_version );
	}

	/**
	 * Get Access Manager.
	 *
	 * @return AccessManager
	 */
	public function access(): AccessManager {
		return $this->access_manager;
	}

	/**
	 * Get Role Manager.
	 *
	 * @return RoleManager
	 */
	public function roles(): RoleManager {
		return $this->role_manager;
	}

	/**
	 * Get Scope Manager.
	 *
	 * @return ScopeManager
	 */
	public function scope(): ScopeManager {
		return $this->scope_manager;
	}

	/**
	 * Get Entities.
	 *
	 * @since 1.0.0
	 *
	 * @return EntityManager
	 */
	public function entities(): EntityManager {
		return $this->entity_manager;
	}

	/**
	 * Get Site Manager.
	 *
	 * @since 1.0.0
	 *
	 * @return SiteManager
	 */
	public function sites(): SiteManager {
		return $this->site_manager;
	}
}
