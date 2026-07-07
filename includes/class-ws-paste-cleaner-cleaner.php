<?php
/**
 * The HTML cleaning logic.
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
 * The HTML cleaning logic.
 *
 * Static, stateless utility class. All methods return cleaned HTML.
 *
 * @package    WS_Paste_Cleaner
 * @subpackage WS_Paste_Cleaner/includes
 * @author     WebStrategy
 */
class WS_Paste_Cleaner_Cleaner {

	const LEVEL_LIGHT      = 'light';
	const LEVEL_MODERATE   = 'moderate';
	const LEVEL_AGGRESSIVE = 'aggressive';

	/**
	 * Clean HTML based on the chosen level.
	 *
	 * Every branch routes its output through kses_output(), which applies
	 * wp_kses_post as a final defensive filter. Whatever the entry point
	 * (AJAX handler, direct call from a partial, future extension points),
	 * nothing dangerous can leak back to the editor: <script>, <iframe>,
	 * on* handlers, javascript: URIs, and other executable payloads all
	 * get stripped in the last mile.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $html  The HTML to clean.
	 * @param  string $level The cleaning level (light, moderate, aggressive).
	 * @return string        The cleaned, kses-filtered HTML.
	 */
	public static function clean_html( $html, $level = self::LEVEL_MODERATE ) {

		if ( ! is_string( $html ) || '' === $html ) {
			return '';
		}

		switch ( $level ) {
			case self::LEVEL_LIGHT:
				return self::kses_output( self::clean_light( $html ) );

			case self::LEVEL_AGGRESSIVE:
				// Aggressive already strips all tags and rebuilds paragraphs
				// via esc_html(); the kses pass is redundant but harmless.
				return self::kses_output( self::clean_aggressive( $html ) );

			case self::LEVEL_MODERATE:
			default:
				return self::kses_output( self::clean_moderate( $html ) );
		}
	}

	/**
	 * Final safety net: strip anything wp_kses_post would refuse
	 * (<script>, <iframe>, on* handlers, javascript: URIs, dangerous
	 * data: schemes, style attributes with expression(), etc.).
	 *
	 * Called on every branch return in clean_html().
	 *
	 * @since  1.0.0
	 *
	 * @param  string $html
	 * @return string
	 */
	private static function kses_output( $html ) {

		if ( '' === $html ) {
			return '';
		}

		// wp_kses_post is available in every recent WP version but keep
		// the guard for direct-execution paths (unit tests, edge cases).
		if ( ! function_exists( 'wp_kses_post' ) ) {
			return $html;
		}

		return wp_kses_post( $html );
	}

	/**
	 * Light cleaning: strip Word-only metadata, preserve structure.
	 *
	 * Targets only what is safely Word-specific so the rest of the
	 * paste survives intact.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $html
	 * @return string
	 */
	private static function clean_light( $html ) {

		// Strip Office conditional comments wholesale (e.g., <!--[if gte mso 9]> ... <![endif]-->).
		$html = preg_replace( '/<!--\[if[^\]]*\]>.*?<!\[endif\]-->/is', '', $html );

		// Strip Word XML namespaces and their content.
		$html = preg_replace( '/<\?xml[^>]*>/i', '', $html );
		$html = preg_replace( '/<(o|w|v|m|st1):[^>]*>.*?<\/\1:[^>]*>/is', '', $html );
		$html = preg_replace( '/<(o|w|v|m|st1):[^>]*\/?>/i', '', $html );

		// Drop Mso-prefixed classes; leave other class attributes alone.
		$html = preg_replace( '/\sclass="[^"]*Mso[^"]*"/i', '', $html );
		$html = preg_replace( "/\sclass='[^']*Mso[^']*'/i", '', $html );

		return self::collapse_whitespace( $html );
	}

	/**
	 * Moderate cleaning: strip all Word markup, keep semantic structure.
	 *
	 * Default and recommended level. Keeps headings, lists, links and
	 * basic inline formatting (strong/em); strips classes, inline styles,
	 * Office namespaces, and unwraps empty span/div.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $html
	 * @return string
	 */
	private static function clean_moderate( $html ) {

		// 1. Office conditional comments and XML declarations.
		$html = preg_replace( '/<!--\[if[^\]]*\]>.*?<!\[endif\]-->/is', '', $html );
		$html = preg_replace( '/<\?xml[^>]*>/i', '', $html );

		// 2. Office-namespaced tags (with content).
		$html = preg_replace( '/<(o|w|v|m|st1):[^>]*>.*?<\/\1:[^>]*>/is', '', $html );
		$html = preg_replace( '/<(o|w|v|m|st1):[^>]*\/?>/i', '', $html );

		// 3. Strip class, style, lang and xml: attributes everywhere.
		$html = preg_replace( '/\s(?:class|style|lang|xml:[a-z-]+)\s*=\s*"[^"]*"/i', '', $html );
		$html = preg_replace( "/\s(?:class|style|lang|xml:[a-z-]+)\s*=\s*'[^']*'/i", '', $html );

		// 4. Unwrap span/font (no semantic value once classes/styles are gone).
		$html = preg_replace( '/<\/?span[^>]*>/i', '', $html );
		$html = preg_replace( '/<\/?font[^>]*>/i', '', $html );

		// 5. Unwrap empty divs (keeps content, drops the div).
		$html = preg_replace( '/<div[^>]*>(\s*)<\/div>/i', '', $html );
		$html = preg_replace( '/<\/?div[^>]*>/i', '', $html );

		// 6. Strip HTML comments (Office adds plenty).
		$html = preg_replace( '/<!--(?!\[if).*?-->/s', '', $html );

		return self::collapse_whitespace( $html );
	}

	/**
	 * Aggressive cleaning: strip all tags, rebuild as paragraphs.
	 *
	 * Useful when the source HTML is so badly structured that semantic
	 * recovery is hopeless. Output is plain paragraphs only.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $html
	 * @return string
	 */
	private static function clean_aggressive( $html ) {

		// Convert <br> to newlines so paragraph splits survive.
		$html = preg_replace( '/<br\s*\/?>/i', "\n", $html );

		// Extract plain text.
		$text = wp_strip_all_tags( $html );

		// Decode entities so &nbsp; etc. become spaces, then normalise.
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$text = str_replace( "\xC2\xA0", ' ', $text ); // NBSP → space.

		// Split on blank lines for paragraph boundaries.
		$paragraphs = preg_split( '/\n\s*\n/', $text );
		$paragraphs = is_array( $paragraphs ) ? array_filter( array_map( 'trim', $paragraphs ) ) : array();

		$out = '';
		foreach ( $paragraphs as $p ) {
			if ( '' !== $p ) {
				$out .= '<p>' . esc_html( $p ) . '</p>';
			}
		}

		return $out;
	}

	/**
	 * Collapse stray whitespace left behind by attribute removal.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $html
	 * @return string
	 */
	private static function collapse_whitespace( $html ) {
		// Collapse runs of spaces inside opening tags (e.g. "<p   >" → "<p>").
		$html = preg_replace( '/<([a-z][a-z0-9]*)\s+>/i', '<$1>', $html );
		// Collapse 3+ blank lines down to 2.
		$html = preg_replace( "/(\r?\n\s*){3,}/", "\n\n", $html );
		return trim( $html );
	}
}
