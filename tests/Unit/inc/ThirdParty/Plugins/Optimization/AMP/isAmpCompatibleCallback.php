<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization\AMP;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\ThirdParty\Plugins\Optimization\AMP;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::is_amp_compatible_callback
 * @group  ThirdParty
 * @group  WithAmp
 */
class Test_IsAmpCompatibleCallback extends TestCase {
	private $amp;
	private $options;
	private $cdn_subscriber;

	public function setUp() {
		parent::setUp();

		$this->options        = Mockery::mock( Options_Data::class );
		$this->cdn_subscriber = Mockery::mock( Subscriber::class );
		$this->amp            = new AMP( $this->options, $this->cdn_subscriber );
	}


	/**
	 * @dataProvider ampDataProvider
	 */
	public function testShouldReturnExpected( $theme_support, $expected ) {
		Functions\expect( 'get_option' )
			->once()
			->with( 'amp-options', [] )
			->andReturn( $theme_support );

		$this->assertSame( $expected, $this->amp->is_amp_compatible_callback( [] ) );
	}

	public function ampDataProvider() {
		return require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/Optimization/AMP/isAmpCompatibleCallback.php';
	}
}
