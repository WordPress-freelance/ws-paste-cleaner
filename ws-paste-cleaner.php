<?php
/**
 * WS Paste Cleaner
 *
 * @package           WS_Paste_Cleaner
 * @author            WebStrategy
 * @copyright         WebStrategy
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       WS Paste Cleaner
 * Plugin URI:        https://wordpress-freelance.com/ws-paste-cleaner
 * Description:       Automatically strips Microsoft Word formatting on paste in the WordPress editor. Compatible with Gutenberg and the Classic Editor.
 * Version:           1.0.1
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            WebStrategy
 * Author URI:        https://wordpress-freelance.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ws-paste-cleaner
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'WS_PASTE_CLEANER_VERSION', '1.0.1' );
define( 'WS_PASTE_CLEANER_PLUGIN_NAME', 'ws-paste-cleaner' );
define( 'WS_PASTE_CLEANER_PLUGIN_FILE', __FILE__ );
define( 'WS_PASTE_CLEANER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WS_PASTE_CLEANER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_ws_paste_cleaner() {
	require_once WS_PASTE_CLEANER_PLUGIN_DIR . 'includes/class-ws-paste-cleaner-activator.php';
	WS_Paste_Cleaner_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_ws_paste_cleaner() {
	require_once WS_PASTE_CLEANER_PLUGIN_DIR . 'includes/class-ws-paste-cleaner-deactivator.php';
	WS_Paste_Cleaner_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ws_paste_cleaner' );
register_deactivation_hook( __FILE__, 'deactivate_ws_paste_cleaner' );

/**
 * The core plugin class.
 */
require WS_PASTE_CLEANER_PLUGIN_DIR . 'includes/class-ws-paste-cleaner.php';

/**
 * Begins execution of the plugin.
 */
function run_ws_paste_cleaner() {
	$plugin = new WS_Paste_Cleaner();
	$plugin->run();
}
run_ws_paste_cleaner();
