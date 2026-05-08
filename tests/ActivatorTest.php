<?php
/**
 * @covers WS_Paste_Cleaner_Activator
 * @covers WS_Paste_Cleaner_Deactivator
 */
class ActivatorTest extends \WP_Mock\Tools\TestCase {

	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_activate_registers_three_default_options(): void {

		\WP_Mock::userFunction( 'add_option' )
			->once()
			->with( 'ws_paste_cleaner_level', 'moderate' )
			->andReturn( true );

		\WP_Mock::userFunction( 'add_option' )
			->once()
			->with( 'ws_paste_cleaner_auto', 1 )
			->andReturn( true );

		\WP_Mock::userFunction( 'add_option' )
			->once()
			->with( 'ws_paste_cleaner_stats', 0 )
			->andReturn( true );

		WS_Paste_Cleaner_Activator::activate();
	}

	public function test_deactivate_does_nothing(): void {
		// Sanity-check: deactivate() must not throw nor call any WP function.
		WS_Paste_Cleaner_Deactivator::deactivate();
		$this->addToAssertionCount( 1 );
	}
}
