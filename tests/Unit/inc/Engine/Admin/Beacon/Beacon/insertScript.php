<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Beacon\Beacon;

use Mockery;
use WP_Theme;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Beacon\Beacon::insert_script
 * @group  Beacon
 */
class Test_InsertScript extends TestCase {
	private $beacon;
	private $options;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Theme.php';
	}

	public function setUp() {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->beacon  = Mockery::mock( Beacon::class . '[insert_script]', [
			$this->options,
			WP_ROCKET_PLUGIN_ROOT . 'views/settings'
		] );
	}

	public function testShouldNotInsertWhenNoCapacity() {
		Functions\when( 'current_user_can' )->justReturn( false );

		$this->beacon->shouldReceive( 'insert_script' )
			->never();
	}

	/**
	 * @dataProvider configTestData
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
		$this->rocket_version = '3.6';
		Functions\when( 'rocket_get_active_plugins' )->justReturn( [] );

		$this->options->shouldReceive( 'get' )
			->with( 'consumer_email' )
			->andReturn( 'dummy@wp-rocket.me' );

		$this->options->shouldReceive( 'get_options' )
			->andReturn( [] );

		$this->setUpGenerate( 'beacon', $expected['data'] );

		$this->beacon->shouldReceive( 'insert_script' )
			->once();

		ob_start();
		$this->beacon->insert_script();
		ob_get_clean();
	}

	protected function setUpGenerate( $view, $data ) {
		$this->beacon
			->shouldReceive( 'generate' )
			->with( $view, $data )
			->andReturn( '' );
	}
}
