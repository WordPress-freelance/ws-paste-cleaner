<?php
/**
 * @covers WS_Paste_Cleaner_Admin
 */
class AdminTest extends \WP_Mock\Tools\TestCase {

	/** @var WS_Paste_Cleaner_Admin */
	private $admin;

	public function setUp(): void {
		\WP_Mock::setUp();
		$this->admin = new WS_Paste_Cleaner_Admin( 'ws-paste-cleaner', '1.0.0' );
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	// ─── process_settings ─────────────────────────────────────────

	public function test_process_settings_persists_valid_level_and_auto(): void {

		\WP_Mock::userFunction( 'update_option' )
			->once()
			->with( 'ws_paste_cleaner_level', 'aggressive' )
			->andReturn( true );

		\WP_Mock::userFunction( 'update_option' )
			->once()
			->with( 'ws_paste_cleaner_auto', 1 )
			->andReturn( true );

		$this->admin->process_settings( array(
			'ws_paste_cleaner_level' => 'aggressive',
			'ws_paste_cleaner_auto'  => '1',
		) );
	}

	public function test_process_settings_falls_back_to_moderate_for_invalid_level(): void {

		\WP_Mock::userFunction( 'update_option' )
			->once()
			->with( 'ws_paste_cleaner_level', 'moderate' )
			->andReturn( true );

		\WP_Mock::userFunction( 'update_option' )
			->once()
			->with( 'ws_paste_cleaner_auto', 0 )
			->andReturn( true );

		$this->admin->process_settings( array(
			'ws_paste_cleaner_level' => 'malicious-payload',
			// no ws_paste_cleaner_auto
		) );
	}

	public function test_process_settings_treats_missing_auto_as_zero(): void {

		\WP_Mock::userFunction( 'update_option' )
			->once()
			->with( 'ws_paste_cleaner_level', 'light' )
			->andReturn( true );

		\WP_Mock::userFunction( 'update_option' )
			->once()
			->with( 'ws_paste_cleaner_auto', 0 )
			->andReturn( true );

		$this->admin->process_settings( array(
			'ws_paste_cleaner_level' => 'light',
		) );
	}

	public function test_process_settings_uses_default_when_level_missing(): void {

		\WP_Mock::userFunction( 'update_option' )
			->once()
			->with( 'ws_paste_cleaner_level', 'moderate' )
			->andReturn( true );

		\WP_Mock::userFunction( 'update_option' )
			->once()
			->with( 'ws_paste_cleaner_auto', 0 )
			->andReturn( true );

		$this->admin->process_settings( array() );
	}

	public function test_process_settings_strips_slashes_and_tags_from_level(): void {

		\WP_Mock::userFunction( 'update_option' )
			->once()
			->with( 'ws_paste_cleaner_level', 'moderate' )
			->andReturn( true );

		\WP_Mock::userFunction( 'update_option' )
			->once()
			->with( 'ws_paste_cleaner_auto', 0 )
			->andReturn( true );

		// XSS-shaped, slashed input. After unslash + sanitize_text_field +
		// allow-list check, this should fall back to "moderate".
		$this->admin->process_settings( array(
			'ws_paste_cleaner_level' => "<script>\\\"alert('x')\\\";</script>aggressive",
		) );
	}

	// ─── enqueue gating ───────────────────────────────────────────

	public function test_enqueue_styles_does_nothing_off_plugin_screen(): void {
		// No WP_Mock::userFunction calls — if enqueue_styles tries to enqueue
		// anything, the assertion below fails.
		$this->admin->enqueue_styles( 'edit.php' );
		$this->addToAssertionCount( 1 );
	}

	public function test_enqueue_scripts_does_nothing_off_plugin_screen(): void {
		$this->admin->enqueue_scripts( 'edit.php' );
		$this->addToAssertionCount( 1 );
	}

	// ─── add_plugin_admin_menu ────────────────────────────────────

	public function test_add_plugin_admin_menu_registers_settings_subpage(): void {

		\WP_Mock::userFunction( 'add_options_page' )
			->once()
			->with(
				'WS Paste Cleaner',
				'WS Paste Cleaner',
				'manage_options',
				'ws-paste-cleaner',
				\Mockery::type( 'array' )
			)
			->andReturn( 'settings_page_ws-paste-cleaner' );

		$this->admin->add_plugin_admin_menu();
	}

	// ─── admin_body_class ─────────────────────────────────────────

	public function test_add_admin_body_class_appends_when_on_plugin_screen(): void {

		$screen     = new WP_Screen();
		$screen->id = 'settings_page_ws-paste-cleaner';

		\WP_Mock::userFunction( 'get_current_screen' )->andReturn( $screen );

		$out = $this->admin->add_admin_body_class( 'existing classes' );
		$this->assertStringContainsString( 'ws-paste-cleaner-page', $out );
		$this->assertStringContainsString( 'existing classes', $out );
	}

	public function test_add_admin_body_class_unchanged_off_plugin_screen(): void {

		$screen     = new WP_Screen();
		$screen->id = 'edit-post';

		\WP_Mock::userFunction( 'get_current_screen' )->andReturn( $screen );

		$out = $this->admin->add_admin_body_class( 'existing' );
		$this->assertSame( 'existing', $out );
	}

	public function test_add_admin_body_class_unchanged_when_no_screen(): void {

		\WP_Mock::userFunction( 'get_current_screen' )->andReturn( null );

		$out = $this->admin->add_admin_body_class( 'existing' );
		$this->assertSame( 'existing', $out );
	}

	// ─── inline_reset_css ─────────────────────────────────────────

	public function test_inline_reset_css_emits_scoped_overrides_on_plugin_screen(): void {

		$screen     = new WP_Screen();
		$screen->id = 'settings_page_ws-paste-cleaner';

		\WP_Mock::userFunction( 'get_current_screen' )->andReturn( $screen );

		ob_start();
		$this->admin->inline_reset_css();
		$css = ob_get_clean();

		$this->assertStringContainsString( '.ws-paste-cleaner-page', $css );
		$this->assertStringContainsString( '#wpwrap', $css );
		// Sanity: never set margin:0 on #wpcontent (would clip the sidebar).
		$this->assertDoesNotMatchRegularExpression(
			'/#wpcontent\s*\{\s*margin\s*:\s*0/i',
			$css
		);
	}

	public function test_inline_reset_css_silent_off_plugin_screen(): void {

		$screen     = new WP_Screen();
		$screen->id = 'edit-post';

		\WP_Mock::userFunction( 'get_current_screen' )->andReturn( $screen );

		ob_start();
		$this->admin->inline_reset_css();
		$out = ob_get_clean();

		$this->assertSame( '', $out );
	}
}
