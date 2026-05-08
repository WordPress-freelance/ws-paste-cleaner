<?php
/**
 * PHPUnit + WP_Mock bootstrap for ws-paste-cleaner.
 *
 * Order matters:
 *   1. Define ABSPATH and plugin constants.
 *   2. Load Composer autoload (gives us PHPUnit + WP_Mock).
 *   3. Stub trivial WP functions WP_Mock doesn't supply automatically.
 *   4. Stub WP classes that may be referenced.
 *   5. WP_Mock::bootstrap()
 *   6. Load plugin classes under test.
 */

declare( strict_types = 1 );

// ─── 1. Constants ─────────────────────────────────────────────────

if ( ! defined( 'ABSPATH' ) )                     define( 'ABSPATH', '/tmp/wordpress/' );
if ( ! defined( 'WPINC' ) )                       define( 'WPINC', 'wp-includes' );
if ( ! defined( 'WP_DEBUG' ) )                    define( 'WP_DEBUG', false );
if ( ! defined( 'WS_PASTE_CLEANER_VERSION' ) )    define( 'WS_PASTE_CLEANER_VERSION', '1.0.0' );
if ( ! defined( 'WS_PASTE_CLEANER_PLUGIN_NAME' ) )define( 'WS_PASTE_CLEANER_PLUGIN_NAME', 'ws-paste-cleaner' );
if ( ! defined( 'WS_PASTE_CLEANER_PLUGIN_DIR' ) ) define( 'WS_PASTE_CLEANER_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
if ( ! defined( 'WS_PASTE_CLEANER_PLUGIN_URL' ) ) define( 'WS_PASTE_CLEANER_PLUGIN_URL', 'http://example.test/wp-content/plugins/ws-paste-cleaner/' );

// ─── 2. Composer autoload ─────────────────────────────────────────

$autoload = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( ! file_exists( $autoload ) ) {
	fwrite( STDERR, "Run `composer install` before executing the test suite.\n" );
	exit( 1 );
}
require_once $autoload;

// ─── 3. Native PHP stubs for trivial WP functions ─────────────────
//
// WP_Mock 0.5 does not auto-stub these — without stubs they fall back
// to undefined-function fatals during plugin code execution.

if ( ! function_exists( 'absint' ) ) {
	function absint( $n ) { return abs( (int) $n ); }
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'wp_unslash', $value );
		}
		return is_string( $value ) ? stripslashes( $value ) : $value;
	}
}

if ( ! function_exists( 'wp_check_invalid_utf8' ) ) {
	function wp_check_invalid_utf8( $string, $strip = false ) { return $string; }
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		$str = (string) $str;
		$str = wp_check_invalid_utf8( $str );
		$str = strip_tags( $str );
		$str = preg_replace( '/[\r\n\t]+/', ' ', $str );
		return trim( preg_replace( '/\s+/', ' ', $str ) );
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	function wp_strip_all_tags( $str, $remove_breaks = false ) {
		$str = (string) $str;
		$str = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $str );
		$str = strip_tags( $str );
		if ( $remove_breaks ) {
			$str = preg_replace( '/[\r\n\t ]+/', ' ', $str );
		}
		return trim( $str );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) { return esc_html( $text ); }
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) { return (string) $url; }
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = null ) { return $text; }
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = null ) { return esc_html( $text ); }
}

if ( ! function_exists( 'esc_html_e' ) ) {
	function esc_html_e( $text, $domain = null ) { echo esc_html( $text ); }
}

if ( ! function_exists( '_e' ) ) {
	function _e( $text, $domain = null ) { echo $text; }
}

// URL helpers — return predictable strings so assertions can rely on them.
if ( ! function_exists( 'plugin_dir_url' ) ) {
	function plugin_dir_url( $file ) {
		return 'http://example.test/wp-content/plugins/ws-paste-cleaner/' . basename( dirname( $file ) ) . '/';
	}
}
if ( ! function_exists( 'plugin_dir_path' ) ) {
	function plugin_dir_path( $file ) { return dirname( $file ) . '/'; }
}
if ( ! function_exists( 'plugin_basename' ) ) {
	function plugin_basename( $file ) { return basename( dirname( $file ) ) . '/' . basename( $file ); }
}
if ( ! function_exists( 'admin_url' ) ) {
	function admin_url( $path = '' ) { return 'http://example.test/wp-admin/' . ltrim( (string) $path, '/' ); }
}
if ( ! function_exists( 'add_query_arg' ) ) {
	function add_query_arg( $args, $url ) {
		return $url . ( strpos( $url, '?' ) !== false ? '&' : '?' ) . http_build_query( $args );
	}
}

// Enqueue / nonce no-ops — return null/empty string. Tests that need to
// assert these were called should use WP_Mock::userFunction() locally.
if ( ! function_exists( 'wp_enqueue_script' ) ) {
	function wp_enqueue_script( ...$args ) { return null; }
}
if ( ! function_exists( 'wp_enqueue_style' ) ) {
	function wp_enqueue_style( ...$args ) { return null; }
}
if ( ! function_exists( 'wp_localize_script' ) ) {
	function wp_localize_script( ...$args ) { return true; }
}
if ( ! function_exists( 'wp_create_nonce' ) ) {
	function wp_create_nonce( $action = -1 ) { return 'test-nonce'; }
}
if ( ! function_exists( 'wp_die' ) ) {
	function wp_die( $message = '', $title = '', $args = array() ) {
		throw new \RuntimeException( 'wp_die: ' . ( is_string( $message ) ? $message : '' ) );
	}
}
if ( ! function_exists( 'wp_safe_redirect' ) ) {
	function wp_safe_redirect( $location, $status = 302 ) { return true; }
}
if ( ! function_exists( 'load_plugin_textdomain' ) ) {
	function load_plugin_textdomain( $domain, $deprecated = false, $plugin_rel_path = false ) { return true; }
}
if ( ! function_exists( 'number_format_i18n' ) ) {
	function number_format_i18n( $number, $decimals = 0 ) { return number_format( (float) $number, (int) $decimals ); }
}

// ─── 4. Stub WP classes ───────────────────────────────────────────

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		public $errors = array();
		public $error_data = array();
		public function __construct( $code = '', $message = '', $data = '' ) {
			if ( '' !== $code ) {
				$this->errors[ $code ][] = $message;
				if ( '' !== $data ) {
					$this->error_data[ $code ] = $data;
				}
			}
		}
		public function get_error_code()    { return key( $this->errors ); }
		public function get_error_message() { $c = $this->get_error_code(); return $c ? $this->errors[ $c ][0] : ''; }
	}
}

if ( ! class_exists( 'WP_Screen' ) ) {
	class WP_Screen { public $id = ''; public $base = ''; }
}

// ─── 5. WP_Mock bootstrap ─────────────────────────────────────────

WP_Mock::bootstrap();

// ─── 6. Load plugin classes under test ────────────────────────────

$base = dirname( __DIR__ );
require_once $base . '/includes/class-ws-paste-cleaner-loader.php';
require_once $base . '/includes/class-ws-paste-cleaner-i18n.php';
require_once $base . '/includes/class-ws-paste-cleaner-cleaner.php';
require_once $base . '/includes/class-ws-paste-cleaner-activator.php';
require_once $base . '/includes/class-ws-paste-cleaner-deactivator.php';
require_once $base . '/admin/class-ws-paste-cleaner-admin.php';
require_once $base . '/public/class-ws-paste-cleaner-public.php';
require_once $base . '/includes/class-ws-paste-cleaner.php';
