<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Hosts the editor integrations (Gutenberg + Classic) and AJAX handlers.
 *
 * @link       https://wordpress-freelance.com
 * @since      1.0.0
 *
 * @package    WS_Paste_Cleaner
 * @subpackage WS_Paste_Cleaner/public
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The public-facing functionality of the plugin.
 *
 * @package    WS_Paste_Cleaner
 * @subpackage WS_Paste_Cleaner/public
 * @author     WebStrategy
 */
class WS_Paste_Cleaner_Public {

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
	 * Register Gutenberg scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_gutenberg_scripts() {

		if ( ! get_option( 'ws_paste_cleaner_auto', 1 ) ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name . '-gutenberg',
			plugin_dir_url( __FILE__ ) . 'js/ws-paste-cleaner-gutenberg.js',
			array( 'wp-blocks', 'wp-dom-ready', 'wp-edit-post', 'wp-data' ),
			$this->version,
			true
		);

		wp_localize_script(
			$this->plugin_name . '-gutenberg',
			'wsPasteCleaner',
			array(
				'level'   => get_option( 'ws_paste_cleaner_level', 'moderate' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ws_paste_cleaner' ),
			)
		);
	}

	/**
	 * Register the TinyMCE plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $plugins
	 * @return array
	 */
	public function register_tinymce_plugin( $plugins ) {

		if ( ! get_option( 'ws_paste_cleaner_auto', 1 ) ) {
			return $plugins;
		}

		$plugins['ws_paste_cleaner'] = plugin_dir_url( __FILE__ ) . 'js/ws-paste-cleaner-tinymce.js?v=' . $this->version;

		return $plugins;
	}

	/**
	 * AJAX handler for cleaning HTML (editor requests).
	 *
	 * Note: we deliberately do NOT call wp_kses_post() on the input. The
	 * cleaner is designed to strip Word-specific markup (`<o:p>`, `<w:*>`,
	 * `<v:*>`, MsoNormal classes) and wp_kses_post() would silently strip
	 * those tags before the cleaner ever sees them — leaving moderate/light
	 * levels partially inoperant. Permission is enforced by edit_posts +
	 * nonce; cleaned output is returned to the editor and never echoed
	 * unescaped.
	 *
	 * @since 1.0.0
	 */
	public function ajax_clean_html() {

		check_ajax_referer( 'ws_paste_cleaner', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access denied.', 'ws-paste-cleaner' ) ), 403 );
			return;
		}

		$html  = isset( $_POST['html'] )  ? wp_unslash( $_POST['html'] ) : '';
		$level = isset( $_POST['level'] ) ? sanitize_text_field( wp_unslash( $_POST['level'] ) ) : 'moderate';

		$cleaned = WS_Paste_Cleaner_Cleaner::clean_html( $html, $level );

		// Increment the local stats counter (no remote tracking).
		$stats = (int) get_option( 'ws_paste_cleaner_stats', 0 );
		update_option( 'ws_paste_cleaner_stats', $stats + 1 );

		wp_send_json_success( array( 'html' => $cleaned ) );
	}

	/**
	 * AJAX handler for the test zone (admin only).
	 *
	 * @since 1.0.0
	 */
	public function ajax_test_clean() {

		check_ajax_referer( 'ws_paste_cleaner_test', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Access denied.', 'ws-paste-cleaner' ) ), 403 );
			return;
		}

		$html  = isset( $_POST['html'] )  ? wp_unslash( $_POST['html'] ) : '';
		$level = isset( $_POST['level'] ) ? sanitize_text_field( wp_unslash( $_POST['level'] ) ) : 'moderate';

		$cleaned = WS_Paste_Cleaner_Cleaner::clean_html( $html, $level );

		wp_send_json_success( array( 'html' => $cleaned ) );
	}
}
