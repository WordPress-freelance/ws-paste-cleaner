<?php
/**
 * Fired during plugin deactivation.
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
 * Defines all code necessary to run during plugin deactivation.
 *
 * @since      1.0.0
 * @package    WS_Paste_Cleaner
 * @subpackage WS_Paste_Cleaner/includes
 * @author     WebStrategy
 */
class WS_Paste_Cleaner_Deactivator {

	/**
	 * Deactivation logic.
	 *
	 * Options are preserved for reactivation. Full cleanup happens on
	 * uninstall (uninstall.php).
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Nothing to clean up on deactivation.
	}
}
