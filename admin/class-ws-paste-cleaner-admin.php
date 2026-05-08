<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wordpress-freelance.com
 * @since      1.0.0
 *
 * @package    WS_Paste_Cleaner
 * @subpackage WS_Paste_Cleaner/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, settings page, and the
 * Avada / third-party theme white-frame fix.
 *
 * @package    WS_Paste_Cleaner
 * @subpackage WS_Paste_Cleaner/admin
 * @author     WebStrategy
 */
class WS_Paste_Cleaner_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name
	 * @param string $version
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Tells whether we're currently on the plugin's settings page.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return bool
	 */
	private function is_plugin_screen() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return ( $screen && false !== strpos( $screen->id, $this->plugin_name ) );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook
	 */
	public function enqueue_styles( $hook ) {

		if ( 'settings_page_' . $this->plugin_name !== $hook ) {
			return;
		}

		// Google Fonts: Lora (headings) + Inter (body) per WebStrategy charter.
		wp_enqueue_style(
			$this->plugin_name . '-fonts',
			'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lora:wght@500;600;700&display=swap',
			array(),
			$this->version
		);

		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/ws-paste-cleaner-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook
	 */
	public function enqueue_scripts( $hook ) {

		if ( 'settings_page_' . $this->plugin_name !== $hook ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name . '-test-zone',
			plugin_dir_url( __FILE__ ) . 'js/ws-paste-cleaner-test-zone.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		wp_localize_script(
			$this->plugin_name . '-test-zone',
			'wsPasteCleanerTest',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ws_paste_cleaner_test' ),
			)
		);
	}

	/**
	 * Register the administration menu for this plugin.
	 *
	 * @since 1.0.0
	 */
	public function add_plugin_admin_menu() {
		add_options_page(
			__( 'WS Paste Cleaner', 'ws-paste-cleaner' ),
			__( 'WS Paste Cleaner', 'ws-paste-cleaner' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_setup_page' )
		);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since 1.0.0
	 */
	public function display_plugin_setup_page() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/ws-paste-cleaner-admin-display.php';
	}

	/**
	 * Save plugin settings.
	 *
	 * @since 1.0.0
	 */
	public function save_settings() {

		check_admin_referer( 'ws_paste_cleaner_settings' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', 'ws-paste-cleaner' ) );
		}

		$level = isset( $_POST['ws_paste_cleaner_level'] )
			? sanitize_text_field( wp_unslash( $_POST['ws_paste_cleaner_level'] ) )
			: 'moderate';

		$allowed_levels = array( 'light', 'moderate', 'aggressive' );
		if ( ! in_array( $level, $allowed_levels, true ) ) {
			$level = 'moderate';
		}

		$auto = isset( $_POST['ws_paste_cleaner_auto'] ) ? 1 : 0;

		update_option( 'ws_paste_cleaner_level', $level );
		update_option( 'ws_paste_cleaner_auto', $auto );

		wp_safe_redirect(
			add_query_arg(
				array( 'page' => $this->plugin_name, 'saved' => '1' ),
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}

	/**
	 * Add a unique body class to the plugin admin page so we can scope
	 * the white-frame fix without leaking into other admin pages.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $classes
	 * @return string
	 */
	public function add_admin_body_class( $classes ) {
		if ( $this->is_plugin_screen() ) {
			$classes .= ' ws-paste-cleaner-page';
		}
		return $classes;
	}

	/**
	 * Inline reset CSS to neutralise Avada / third-party theme white-frame
	 * styling on this plugin's admin page.
	 *
	 * Rules:
	 *   - Background-only on #wpwrap / #wpcontent / #wpbody / #wpbody-content.
	 *   - Padding reset on #wpbody and #wpbody-content only — NOT on
	 *     #wpcontent (WordPress injects margin-left there for the sidebar).
	 *   - .wrap: reset everything except margin-left.
	 *
	 * @since 1.0.0
	 */
	public function inline_reset_css() {

		if ( ! $this->is_plugin_screen() ) {
			return;
		}

		echo '<style>
		.ws-paste-cleaner-page #wpwrap,
		.ws-paste-cleaner-page #wpcontent,
		.ws-paste-cleaner-page #wpbody,
		.ws-paste-cleaner-page #wpbody-content { background: #14121C !important; }

		.ws-paste-cleaner-page #wpbody,
		.ws-paste-cleaner-page #wpbody-content { padding: 0 !important; }

		.ws-paste-cleaner-page .wrap,
		.ws-paste-cleaner-page #wpcontent .wrap {
			margin: 0 !important;
			padding: 0 !important;
			background: #14121C !important;
			max-width: none !important;
		}
		</style>';
	}
}
