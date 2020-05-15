<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Beacon\Beacon;

use Mockery;
use WP_Theme;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;

/**
 * @covers \WP_Rocket\Engine\Admin\Beacon\Beacon::insert_script
 * @group  Beacon
 */
class Test_InsertScript extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Admin/Beacon/Beacon/insertScript.php';
	private $beacon;
	private $options;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Theme.php';
	}

	public function setUp() {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->beacon  = new Beacon( $this->options, $this->filesystem->getUrl( 'wp-content/plugins/wp-rocket/views/settings' ) );
	}

	private function getActualHtml() {
		ob_start();
		$this->beacon->insert_script();

		return $this->format_the_html( ob_get_clean() );
	}

	public function testShouldReturNullWhenNoCapacity() {
		Functions\when( 'current_user_can' )->justReturn( false );

		$this->assertNull( $this->beacon->insert_script() );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnBeaconScript( $locale, $expected ) {
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_user_locale' )->justReturn( $locale );
		Functions\when( 'esc_js' )->returnArg();
		Functions\when( 'wp_json_encode' )->alias( 'json_encode' );
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'wp_get_theme' )->alias( function() {
			return new WP_Theme( 'default', '/themes' );
		} );
		Functions\when( 'get_bloginfo' )->justReturn( '5.4' );
		Functions\when( 'rocket_get_constant' )->justReturn( '3.6' );
		Functions\when( 'rocket_get_active_plugins' )->justReturn( [] );

		$this->options->shouldReceive( 'get' )
			->with( 'consumer_email' )
			->andReturn( 'dummy@wp-rocket.me' );

		$this->options->shouldReceive( 'get_options' )
			->andReturn( [] );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->getActualHtml()
		);
	}
}
