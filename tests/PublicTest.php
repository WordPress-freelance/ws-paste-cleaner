<?php
/**
 * @covers WS_Paste_Cleaner_Public
 */
class PublicTest extends \WP_Mock\Tools\TestCase {

	/** @var WS_Paste_Cleaner_Public */
	private $public;

	public function setUp(): void {
		\WP_Mock::setUp();
		$this->public = new WS_Paste_Cleaner_Public( 'ws-paste-cleaner', '1.0.0' );
		$_POST = array();
	}

	public function tearDown(): void {
		$_POST = array();
		\WP_Mock::tearDown();
	}

	// ─── ajax_clean_html ──────────────────────────────────────────

	public function test_ajax_clean_html_denied_without_edit_posts_capability(): void {

		\WP_Mock::userFunction( 'check_ajax_referer' )
			->once()
			->with( 'ws_paste_cleaner', 'nonce' )
			->andReturn( true );

		\WP_Mock::userFunction( 'current_user_can' )
			->once()
			->with( 'edit_posts' )
			->andReturn( false );

		// 403 sent. update_option must NOT be reached.
		\WP_Mock::userFunction( 'wp_send_json_error' )->once();

		$this->public->ajax_clean_html();
	}

	public function test_ajax_clean_html_happy_path_increments_stats_and_returns_cleaned(): void {

		$_POST = array(
			'html'  => addslashes( '<p class="MsoNormal">Hello</p>' ),
			'level' => 'moderate',
		);

		\WP_Mock::userFunction( 'check_ajax_referer' )->once()->andReturn( true );
		\WP_Mock::userFunction( 'current_user_can' )->once()->with( 'edit_posts' )->andReturn( true );
		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'ws_paste_cleaner_stats', 0 )
			->andReturn( 41 );
		\WP_Mock::userFunction( 'update_option' )
			->once()
			->with( 'ws_paste_cleaner_stats', 42 );

		$captured = null;
		\WP_Mock::userFunction( 'wp_send_json_success' )
			->once()
			->andReturnUsing( function ( $payload ) use ( &$captured ) {
				$captured = $payload;
			} );

		$this->public->ajax_clean_html();

		$this->assertIsArray( $captured );
		$this->assertArrayHasKey( 'html', $captured );
		// Cleaned: no class= attribute, content survives.
		$this->assertStringNotContainsString( 'class=', $captured['html'] );
		$this->assertStringContainsString( 'Hello', $captured['html'] );
	}

	public function test_ajax_clean_html_uses_moderate_default_when_level_missing(): void {

		$_POST = array( 'html' => addslashes( '<p style="color:red">Hi</p>' ) );

		\WP_Mock::userFunction( 'check_ajax_referer' )->once()->andReturn( true );
		\WP_Mock::userFunction( 'current_user_can' )->once()->andReturn( true );
		\WP_Mock::userFunction( 'get_option' )->once()->andReturn( 0 );
		\WP_Mock::userFunction( 'update_option' )->once();

		$captured = null;
		\WP_Mock::userFunction( 'wp_send_json_success' )
			->once()
			->andReturnUsing( function ( $payload ) use ( &$captured ) {
				$captured = $payload;
			} );

		$this->public->ajax_clean_html();

		// Moderate strips inline styles → no style= attribute.
		$this->assertStringNotContainsString( 'style=', $captured['html'] );
	}

	// ─── ajax_test_clean ──────────────────────────────────────────

	public function test_ajax_test_clean_denied_without_manage_options_capability(): void {

		\WP_Mock::userFunction( 'check_ajax_referer' )
			->once()
			->with( 'ws_paste_cleaner_test', 'nonce' )
			->andReturn( true );

		\WP_Mock::userFunction( 'current_user_can' )
			->once()
			->with( 'manage_options' )
			->andReturn( false );

		// 403 sent — and update_option must NOT be called.
		\WP_Mock::userFunction( 'wp_send_json_error' )->once();

		$this->public->ajax_test_clean();
	}

	public function test_ajax_test_clean_happy_path_does_not_increment_stats(): void {

		$_POST = array(
			'html'  => addslashes( '<p>Test content</p>' ),
			'level' => 'aggressive',
		);

		\WP_Mock::userFunction( 'check_ajax_referer' )->once()->andReturn( true );
		\WP_Mock::userFunction( 'current_user_can' )->once()->andReturn( true );

		$captured = null;
		\WP_Mock::userFunction( 'wp_send_json_success' )
			->once()
			->andReturnUsing( function ( $payload ) use ( &$captured ) {
				$captured = $payload;
			} );

		$this->public->ajax_test_clean();

		$this->assertIsArray( $captured );
		$this->assertArrayHasKey( 'html', $captured );
		// Test handler does NOT touch stats — assertion implicit:
		// no get_option/update_option were mocked, so any call would fail.
	}

	// ─── enqueue gating (auto OFF) ────────────────────────────────

	public function test_enqueue_gutenberg_does_nothing_when_auto_disabled(): void {

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'ws_paste_cleaner_auto', 1 )
			->andReturn( 0 );

		$this->public->enqueue_gutenberg_scripts();
		$this->addToAssertionCount( 1 );
	}

	public function test_register_tinymce_returns_unchanged_when_auto_disabled(): void {

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'ws_paste_cleaner_auto', 1 )
			->andReturn( 0 );

		$plugins = array( 'foo' => 'bar' );
		$out     = $this->public->register_tinymce_plugin( $plugins );
		$this->assertSame( $plugins, $out );
	}

	public function test_register_tinymce_adds_handle_when_auto_enabled(): void {

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'ws_paste_cleaner_auto', 1 )
			->andReturn( 1 );

		$plugins = array();
		$out     = $this->public->register_tinymce_plugin( $plugins );
		$this->assertArrayHasKey( 'ws_paste_cleaner', $out );
		$this->assertStringContainsString( 'ws-paste-cleaner-tinymce.js', $out['ws_paste_cleaner'] );
	}
}
