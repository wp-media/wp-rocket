<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Mockery;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::maybe_generate_cpcss_mobile
 *
 * @group  Subscribers
 * @group  CriticalPath
 */
class Test_MaybeGenerateCpcssMobile extends TestCase {

	private $subscriber;
	private $critical_css;

	public function setUp() {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\expect( 'home_url' )->once()->with( '/' )->andReturn( 'http://example.com/' );

		$options      = Mockery::mock( Options_Data::class );
		$this->critical_css = Mockery::mock( CriticalCSS::class, [
			Mockery::mock( CriticalCSSGeneration::class ),
			$options,
			null,
		] );
		$this->subscriber   = new CriticalCSSSubscriber( $this->critical_css, $options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldCallProcessHandler($config, $expected) {

		if( $expected['process_handler_called'] ) {
			$this->critical_css->shouldReceive('process_handler')
				->once()
				->with('mobile');
		}

		$this->subscriber->maybe_generate_cpcss_mobile( $config['old_value'], $config['value'] );

	}

}
