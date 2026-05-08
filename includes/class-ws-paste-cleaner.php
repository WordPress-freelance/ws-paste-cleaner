<?php
/**
 * The core plugin class.
 *
 * @link       https://wordpress-freelance.com
 * @since      1.0.0
 *
 * @package    WS_Paste_Cleaner
 * @subpackage WS_Paste_Cleaner/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core plugin class.
 *
 * Defines internationalization, admin-specific hooks, and public-facing
 * site hooks. Maintains the unique identifier of this plugin and its
 * current version.
 *
 * @since      1.0.0
 * @package    WS_Paste_Cleaner
 * @subpackage WS_Paste_Cleaner/includes
 * @author     WebStrategy
 */
class WS_Paste_Cleaner {

	/**
	 * The loader that's responsible for maintaining and registering all hooks
	 * that power the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WS_Paste_Cleaner_Loader $loader
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->version     = defined( 'WS_PASTE_CLEANER_VERSION' ) ? WS_PASTE_CLEANER_VERSION : '1.0.0';
		$this->plugin_name = 'ws-paste-cleaner';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ws-paste-cleaner-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ws-paste-cleaner-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ws-paste-cleaner-cleaner.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ws-paste-cleaner-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ws-paste-cleaner-public.php';

		$this->loader = new WS_Paste_Cleaner_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new WS_Paste_Cleaner_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WS_Paste_Cleaner_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu',            $plugin_admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_post_ws_paste_cleaner_save', $plugin_admin, 'save_settings' );

		// Avada / third-party theme white-frame fix.
		$this->loader->add_filter( 'admin_body_class',      $plugin_admin, 'add_admin_body_class' );
		$this->loader->add_action( 'admin_head',            $plugin_admin, 'inline_reset_css' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality.
	 *
	 * Note: editor hooks (Gutenberg + TinyMCE) are technically server-rendered
	 * in admin, but per WPPB convention they live on the public side because
	 * they enrich the front-end editing experience and never touch admin
	 * chrome or settings UI.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WS_Paste_Cleaner_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'enqueue_block_editor_assets', $plugin_public, 'enqueue_gutenberg_scripts' );
		$this->loader->add_filter( 'mce_external_plugins',        $plugin_public, 'register_tinymce_plugin' );

		// AJAX handlers (logged-in editors only via wp_ajax_*).
		$this->loader->add_action( 'wp_ajax_ws_paste_cleaner_clean', $plugin_public, 'ajax_clean_html' );
		$this->loader->add_action( 'wp_ajax_ws_paste_cleaner_test',  $plugin_public, 'ajax_test_clean' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Plugin name accessor.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Loader accessor.
	 *
	 * @since  1.0.0
	 * @return WS_Paste_Cleaner_Loader
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Version accessor.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}
}
