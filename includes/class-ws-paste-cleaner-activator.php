<?php
/**
 * Fired during plugin activation.
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
 * Defines all code necessary to run during plugin activation.
 *
 * @since      1.0.0
 * @package    WS_Paste_Cleaner
 * @subpackage WS_Paste_Cleaner/includes
 * @author     WebStrategy
 */
class WS_Paste_Cleaner_Activator {

	/**
	 * Set default options on activation.
	 *
	 * Uses add_option() to avoid clobbering existing values on reactivation.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		add_option( 'ws_paste_cleaner_level', 'moderate' );
		add_option( 'ws_paste_cleaner_auto', 1 );
		add_option( 'ws_paste_cleaner_stats', 0 );
	}
}
