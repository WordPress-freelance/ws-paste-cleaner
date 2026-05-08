<?php
/**
 * @covers WS_Paste_Cleaner_Cleaner
 */
class CleanerTest extends \WP_Mock\Tools\TestCase {

	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	// ─── Routing & guards ────────────────────────────────────────

	public function test_empty_string_returns_empty(): void {
		$this->assertSame( '', WS_Paste_Cleaner_Cleaner::clean_html( '' ) );
	}

	public function test_non_string_input_returns_empty(): void {
		$this->assertSame( '', WS_Paste_Cleaner_Cleaner::clean_html( null ) );
		$this->assertSame( '', WS_Paste_Cleaner_Cleaner::clean_html( false ) );
		$this->assertSame( '', WS_Paste_Cleaner_Cleaner::clean_html( array() ) );
		$this->assertSame( '', WS_Paste_Cleaner_Cleaner::clean_html( 42 ) );
	}

	public function test_default_level_is_moderate(): void {
		$html     = '<p style="color:red">Hello</p>';
		$default  = WS_Paste_Cleaner_Cleaner::clean_html( $html );
		$moderate = WS_Paste_Cleaner_Cleaner::clean_html( $html, 'moderate' );
		$this->assertSame( $moderate, $default );
	}

	public function test_unknown_level_falls_back_to_moderate(): void {
		$html     = '<p style="color:red">Hello</p>';
		$unknown  = WS_Paste_Cleaner_Cleaner::clean_html( $html, 'lolwat' );
		$moderate = WS_Paste_Cleaner_Cleaner::clean_html( $html, 'moderate' );
		$this->assertSame( $moderate, $unknown );
	}

	// ─── Light level ─────────────────────────────────────────────

	public function test_light_strips_office_conditional_comments(): void {
		$input = '<!--[if gte mso 9]><w:WordDocument></w:WordDocument><![endif]--><p>Hello</p>';
		$out   = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'light' );
		$this->assertStringNotContainsString( 'mso', $out );
		$this->assertStringNotContainsString( 'w:WordDocument', $out );
		$this->assertStringContainsString( '<p>Hello</p>', $out );
	}

	public function test_light_strips_xml_declarations(): void {
		$input = '<?xml:namespace prefix="w" /><p>Hi</p>';
		$out   = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'light' );
		$this->assertStringNotContainsString( 'xml:namespace', $out );
		$this->assertStringContainsString( '<p>Hi</p>', $out );
	}

	public function test_light_strips_office_namespaced_tags(): void {
		$input = '<p>Hello<o:p></o:p></p><w:View>Print</w:View>';
		$out   = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'light' );
		$this->assertStringNotContainsString( '<o:p', $out );
		$this->assertStringNotContainsString( '<w:View', $out );
		$this->assertStringContainsString( 'Hello', $out );
	}

	public function test_light_strips_only_mso_classes_keeps_others(): void {
		$input = '<p class="MsoNormal">A</p><p class="my-class">B</p>';
		$out   = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'light' );
		$this->assertStringNotContainsString( 'MsoNormal', $out );
		$this->assertStringContainsString( 'class="my-class"', $out );
	}

	public function test_light_keeps_inline_styles(): void {
		$input = '<p style="color:red">A</p>';
		$out   = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'light' );
		$this->assertStringContainsString( 'style="color:red"', $out );
	}

	// ─── Moderate level ──────────────────────────────────────────

	public function test_moderate_strips_all_classes(): void {
		$input = '<p class="MsoNormal">A</p><p class="custom">B</p>';
		$out   = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'moderate' );
		$this->assertStringNotContainsString( 'class=', $out );
	}

	public function test_moderate_strips_inline_styles(): void {
		$input = '<p style="margin:0in;font-size:11pt;font-family:Calibri">Hello</p>';
		$out   = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'moderate' );
		$this->assertStringNotContainsString( 'style=', $out );
		$this->assertStringContainsString( 'Hello', $out );
	}

	public function test_moderate_strips_lang_and_xml_attrs(): void {
		$input = '<p lang="EN-US" xml:lang="en-us">Hi</p>';
		$out   = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'moderate' );
		$this->assertStringNotContainsString( 'lang=', $out );
		$this->assertStringNotContainsString( 'xml:lang=', $out );
	}

	public function test_moderate_unwraps_span_and_font(): void {
		$input = '<p><span>Hi</span> <font>World</font></p>';
		$out   = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'moderate' );
		$this->assertStringNotContainsString( '<span', $out );
		$this->assertStringNotContainsString( '<font', $out );
		$this->assertStringContainsString( 'Hi', $out );
		$this->assertStringContainsString( 'World', $out );
	}

	public function test_moderate_unwraps_div(): void {
		$input = '<div><p>Hello</p></div>';
		$out   = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'moderate' );
		$this->assertStringNotContainsString( '<div', $out );
		$this->assertStringContainsString( '<p>Hello</p>', $out );
	}

	public function test_moderate_preserves_semantic_tags(): void {
		$input = '<h2>Title</h2><p><strong>B</strong> and <em>I</em></p>'
		       . '<ul><li>Item</li></ul><a href="https://example.com">Link</a>';
		$out   = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'moderate' );
		$this->assertStringContainsString( '<h2>', $out );
		$this->assertStringContainsString( '<strong>', $out );
		$this->assertStringContainsString( '<em>', $out );
		$this->assertStringContainsString( '<ul>', $out );
		$this->assertStringContainsString( '<li>', $out );
		$this->assertStringContainsString( '<a href="https://example.com">', $out );
	}

	public function test_moderate_strips_html_comments_except_office(): void {
		$input = '<p>A</p><!-- editor note --><p>B</p>';
		$out   = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'moderate' );
		$this->assertStringNotContainsString( 'editor note', $out );
		$this->assertStringContainsString( '<p>A</p>', $out );
		$this->assertStringContainsString( '<p>B</p>', $out );
	}

	// ─── Aggressive level ────────────────────────────────────────

	public function test_aggressive_strips_all_inline_formatting(): void {
		$input = '<h2>Title</h2><p>Body <strong>here</strong>.</p>';
		$out   = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'aggressive' );
		$this->assertStringNotContainsString( '<h2>', $out );
		$this->assertStringNotContainsString( '<strong>', $out );
		$this->assertMatchesRegularExpression( '/^<p>.+<\/p>/s', $out );
	}

	public function test_aggressive_decodes_html_entities(): void {
		$input = '<p>Caf&eacute; &amp; Croissant</p>';
		$out   = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'aggressive' );
		$this->assertStringContainsString( 'Café', $out );
		// & is re-escaped via esc_html() so we look for &amp; in output.
		$this->assertStringContainsString( '&amp;', $out );
		$this->assertStringContainsString( 'Croissant', $out );
	}

	public function test_aggressive_normalises_nbsp_to_space(): void {
		$input = "<p>Hello\xC2\xA0World</p>";
		$out   = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'aggressive' );
		$this->assertStringNotContainsString( "\xC2\xA0", $out );
		$this->assertStringContainsString( 'Hello World', $out );
	}

	public function test_aggressive_returns_empty_for_whitespace_only(): void {
		$out = WS_Paste_Cleaner_Cleaner::clean_html( "   \n\n\t  ", 'aggressive' );
		$this->assertSame( '', $out );
	}

	// ─── End-to-end realistic Word paste ─────────────────────────

	public function test_realistic_word_paste_moderate(): void {
		$input  = '<!--[if gte mso 9]><xml><w:WordDocument><w:View>Normal</w:View></w:WordDocument></xml><![endif]-->';
		$input .= '<p class="MsoNormal" style="margin:0in;font-family:Calibri">';
		$input .= '<span lang="EN-US" style="font-weight:bold">Hello World</span>';
		$input .= '<o:p></o:p></p>';

		$out = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'moderate' );

		$this->assertStringNotContainsString( 'mso',   $out );
		$this->assertStringNotContainsString( '<w:',   $out );
		$this->assertStringNotContainsString( '<o:',   $out );
		$this->assertStringNotContainsString( 'class=', $out );
		$this->assertStringNotContainsString( 'style=', $out );
		$this->assertStringNotContainsString( 'lang=',  $out );
		$this->assertStringNotContainsString( '<span',  $out );
		$this->assertStringContainsString( 'Hello World', $out );
		$this->assertStringContainsString( '<p>', $out );
	}

	public function test_realistic_word_paste_aggressive_yields_paragraphs_only(): void {
		$input  = '<p class="MsoNormal" style="margin:0">First paragraph.</p>';
		$input .= '<p class="MsoNormal">Second <strong>paragraph</strong>.</p>';

		$out = WS_Paste_Cleaner_Cleaner::clean_html( $input, 'aggressive' );

		$this->assertStringNotContainsString( '<strong>', $out );
		$this->assertStringNotContainsString( 'class=',   $out );
		$this->assertStringContainsString( 'First paragraph', $out );
		$this->assertStringContainsString( 'Second paragraph', $out );
	}
}
