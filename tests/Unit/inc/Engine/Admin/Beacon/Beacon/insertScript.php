<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Beacon\Beacon;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Support\Data;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Theme;

/**
 * @covers \WP_Rocket\Engine\Admin\Beacon\Beacon::insert_script
 * @group  Beacon
 */
class Test_InsertScript extends TestCase {
	private $beacon;
	private $options;
	private $data;

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Theme.php';
	}

	public function setUp() : void {
		parent::setUp();

		Functions\stubEscapeFunctions();
		$this->options = Mockery::mock( Options_Data::class );
		$this->data    = Mockery::mock( Data::class );
		$this->beacon  = Mockery::mock( Beacon::class . '[generate]', [
			$this->options,
			WP_ROCKET_PLUGIN_ROOT . 'views/settings',
			$this->data
		] );
	}

	public function testShouldNotInsertWhenNoCapacity() {
		Functions\when( 'current_user_can' )->justReturn( false );

		$this->assertNull( $this->beacon->insert_script() );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnBeaconScript( $config, $expected ) {
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_user_locale' )->justReturn( $config['locale'] );
		Functions\when( 'wp_json_encode' )->alias( 'json_encode' );
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'get_transient' )->justReturn( $config['customer_data'] );
		Functions\when( 'is_rtl' )->justReturn( $config['rtl'] );

		$this->options->shouldReceive( 'get' )
			->with( 'consumer_email' )
			->andReturn( 'dummy@wp-rocket.me' );

		$this->data->shouldReceive( 'get_support_data' )
			->once()
			->andReturn( json_decode( $expected['data']['session'] ) );

		$this->options->shouldReceive( 'get_options' )
			->andReturn( [] );

		$this->setUpGenerate( 'beacon', $expected['data'] );

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
