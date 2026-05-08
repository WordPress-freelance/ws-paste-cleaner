<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Removes only the options created by this plugin. No external calls,
 * no Hub-related cleanup.
 *
 * @link       https://wordpress-freelance.com
 * @since      1.0.0
 *
 * @package    WS_Paste_Cleaner
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$options = array(
	'ws_paste_cleaner_level',
	'ws_paste_cleaner_auto',
	'ws_paste_cleaner_stats',
);

// Single-site cleanup.
foreach ( $options as $option ) {
	delete_option( $option );
}

// Multisite cleanup.
if ( is_multisite() ) {
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
	foreach ( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
		foreach ( $options as $option ) {
			delete_option( $option );
		}
		restore_current_blog();
	}
}
