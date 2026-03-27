<?php
/**
 * Main Plugin File.
 *
 * @package WP_Platform_Access_Control
 */

namespace WPAC;

defined( 'ABSPATH' ) || exit;

use WPAC\Core\Activator;
use WPAC\Core\Deactivator;
use WPAC\Core\CapabilityRegistry;
use WPAC\Core\Upgrader;
use WPAC\Core\RoleRegistry;
use WPAC\Core\ScopeRegistry;
use WPAC\Services\AccessManager;
use WPAC\Services\RoleManager;
use WPAC\Services\ScopeManager;
use WPAC\Admin\Menu;
use WPAC\Ajax\AccessAjax;

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
		do_action( 'wpac_register_capabilities', $this->registry );
		$this->register_services();
		$this->register_hooks();
		$this->register_menu();

		/**
		 * ROLES
		 */
		RoleRegistry::register( 'super_user', 'Super User', array( '*' ) );

		RoleRegistry::register(
			'manager',
			'Manager',
			array(
				'report:view',
				'report:create',
				'barcode:view',
			)
		);

		RoleRegistry::register(
			'analyst',
			'Analyst',
			array(
				'report:view',
			)
		);

		RoleRegistry::register( 'client', 'Client', array() );

		/**
		 * SCOPES
		 */
		ScopeRegistry::register( 'global', 'Global Access' );
		ScopeRegistry::register( 'entity', 'Company Level' );
		ScopeRegistry::register( 'site', 'Site Level' );
		AccessAjax::init();
	}

	/**
	 *
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
	}

	/**
	 * Runs on WordPress init hook.
	 *
	 * @return void
	 */
	public function on_init(): void {
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
}
