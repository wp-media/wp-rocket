<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\GoogleFonts\Admin\Settings;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Settings;
use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Settings::enable_google_fonts()
 *
 * @group  GoogleFontsAdmin
 */
class Test_EnableGoogleFonts extends TestCase {
	private $beacon;
	private $options;
	private $settings;

	public function setUp() : void {
		parent::setUp();

		$this->beacon       = Mockery::mock( Beacon::class );
		$this->options      = Mockery::mock( Options_Data::class );
		$this->settings = new Settings(
			$this->options,
			$this->beacon,
			'wp-content/plugins/wp-rocket/views'
		);

		Functions\when( 'check_ajax_referer' )->justReturn( true );
	}

	/**
	 * @dataProvider provideTestData
	 */
	public function testShouldEnableGoogleFonts( $user_auth ) {
		Functions\when( 'current_user_can' )->justReturn( $user_auth );

		if ( ! $user_auth ) {
			$this->shouldBail();
		} else {
			$this->shouldSetOption();
		}
	}

	public function shouldBail() {
		Functions\expect( 'wp_send_json_error' )->once();

		$this->settings->enable_google_fonts();
	}

	public function shouldSetOption() {
		$this->options->shouldReceive( 'set' )
		              ->once()
		              ->with( 'minify_google_fonts', 1 );
		$this->options->shouldReceive( 'get_options' )
		              ->once();

		Functions\expect( 'update_option' )->once();
		Functions\expect( 'wp_send_json_success' )->once();

		$this->settings->enable_google_fonts();
	}

	public function provideTestData() {
		return $this->getTestData( __DIR__, 'enableGoogleFonts' );
	}
}
