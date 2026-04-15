<?php
/**
 * Main Plugin Bootstrap Class.
 *
 * @package WP_Platform_Access_Control
 */

namespace WPAC;

defined( 'ABSPATH' ) || exit;


use WPAC\CapabilityRegistry;
use WPAC\Admin\Menu;
use WPAC\Ajax\Ajax;
use WPAC\Container;
use WPAC\Services\RoleService;
use WPAC\Services\UserCapabilityService;
use WPAC\Services\EntityService;
use WPAC\Services\ScopeService;
use WPAC\Services\SiteService;
use WPAC\Repositories\RoleRepository;
use WPAC\Repositories\RoleCapabilityRepository;
use WPAC\Repositories\UserRoleRepository;
use WPAC\Repositories\UserCapabilityRepository;
use WPAC\Repositories\EntityRepository;
use WPAC\Repositories\ScopeRepository;
use WPAC\Repositories\SiteRepository;
use WPAC\Core\Router;
use WPAC\Core\Activator;
use WPAC\Core\Deactivator;
use WPAC\Core\Upgrader;
use WPAC\Core\Auth;
use WPAC\Core\Microsoft;
use WPAC\Services\PermissionService;

/**
 * Main Plugin Bootstrap Class.
 *
 * Responsible for initializing services and exposing global access APIs.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 *
	 * @since 1.0.0
	 */
	private static ?Plugin $instance = null;


	/**
	 * Auth manager instance.
	 *
	 * @var AuthManager
	 *
	 * @since 1.0.0
	 */
	private AuthManager $auth_manager;


	/**
	 * Entity manager instance.
	 *
	 * @var EntityManager
	 *
	 * @since 1.0.0
	 */
	private EntityManager $entity_manager;

	/**
	 * Site manager instance.
	 *
	 * @var SiteManager
	 *
	 * @since 1.0.0
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
	 * Dependency Injection Container.
	 *
	 * @since 1.0.0
	 * @var Container
	 */
	private Container $container;

	/**
	 * Constructor.
	 *
	 * Initializes core services and registers hooks.
	 */
	public function __construct() {
		$this->container = new Container();
	}

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
		$this->register_repositories();
		$this->register_services();
		$this->register_menu();
		$this->register_hooks();
	}

	/**
	 * Register repositories in the container.
	 *
	 * @since 1.0.0
	 */
	private function register_repositories(): void {
		// Repositories.
		$this->container->set(
			RoleRepository::class,
			function () {
				return new RoleRepository();
			}
		);

		$this->container->set(
			RoleCapabilityRepository::class,
			function () {
				return new RoleCapabilityRepository();
			}
		);

		$this->container->set(
			UserRoleRepository::class,
			function () {
				return new UserRoleRepository();
			}
		);

		$this->container->set(
			UserRoleRepository::class,
			function () {
				return new UserRoleRepository();
			}
		);

		$this->container->set(
			UserCapabilityRepository::class,
			function () {
				return new UserCapabilityRepository();
			}
		);

		$this->container->set(
			EntityRepository::class,
			function () {
				return new EntityRepository();
			}
		);

		$this->container->set(
			SiteRepository::class,
			function () {
				return new SiteRepository();
			}
		);

		$this->container->set(
			ScopeRepository::class,
			function () {
				return new ScopeRepository();
			}
		);
	}

	/**
	 * Register services in the container.
	 *
	 * @since 1.0.0
	 */
	public function register_services(): void {

		// Services.
		$this->container->set(
			RoleService::class,
			function () {
				return new RoleService(
					wpac()->container()->get( RoleRepository::class ),
					wpac()->container()->get( RoleCapabilityRepository::class ),
					wpac()->container()->get( UserRoleRepository::class )
				);
			}
		);

		$this->container->set(
			EntityService::class,
			function () {
				return new EntityService(
					wpac()->container()->get( EntityRepository::class )
				);
			}
		);

		$this->container->set(
			ScopeService::class,
			function () {
				return new ScopeService(
					wpac()->container()->get( ScopeRepository::class )
				);
			}
		);

		$this->container->set(
			SiteService::class,
			function () {
				return new \WPAC\Services\SiteService(
					wpac()->container()->get( SiteRepository::class )
				);
			}
		);

		$this->container->set(
			PermissionService::class,
			function () {
				return new PermissionService(
					wpac()->container()->get( UserCapabilityRepository::class ),
					wpac()->container()->get( ScopeRepository::class )
				);
			}
		);

		$this->container->set(
			UserCapabilityService::class,
			function () {
				return new UserCapabilityService(
					wpac()->container()->get( UserCapabilityRepository::class )
				);
			}
		);
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
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	private function register_hooks(): void {

		add_filter( 'show_admin_bar', '__return_false' ); // optional.
		add_filter( 'allowed_redirect_hosts', array( $this, 'allowed_redirect_hosts' ) );
		add_action('wp_head', function () {
			echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
		});

		add_action( 'init', array( $this, 'on_init' ) );
		Ajax::register();
		Router::register();
	}

	/**
	 * Allowed Redirect Hosts
	 *
	 * @since 1.0.0
	 *
	 * @param  array $hosts Hosts.
	 * @return array
	 */
	public function allowed_redirect_hosts( $hosts ) {
		$hosts[] = 'login.microsoftonline.com';
		return $hosts;
	}

	/**
	 * Runs on WordPress init hook.
	 *
	 * @return void
	 */
	public function on_init(): void {
		Auth::init();
		Microsoft::init();
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
	 * @return \WPAC\Container
	 */
	public function container(): Container {
		return $this->container;
	}

	/**
	 * Get Entities.
	 *
	 * @return EntityService
	 */
	public function entities() {
		return $this->container->get( EntityService::class );
	}

	/**
	 * Get Sites.
	 *
	 * @since 1.0.0
	 */
	public function sites() {
		return $this->container->get( SiteService::class );
	}

	/**
	 * Get Scopes.
	 *
	 * @since 1.0.0
	 */
	public function scopes() {
		return $this->container()->get( ScopeService::class );
	}

	/**
	 * Get Roles.
	 *
	 * @since 1.0.0
	 */
	public function permissions() {
		return $this->container->get( PermissionService::class );
	}
}
