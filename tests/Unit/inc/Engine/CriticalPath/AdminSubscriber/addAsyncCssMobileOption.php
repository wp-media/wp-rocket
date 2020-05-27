<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AdminSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Engine\CriticalPath\AdminSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::add_async_css_mobile_option
 *
 * @group  CriticalPath
 */
class Test_AddAsyncCssMobileOption extends TestCase {

	public function setUp() {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddOption( $options, $expected ) {
		$subscriber = new AdminSubscriber(
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Beacon::class ),
			Mockery::mock( CriticalCSS::class ),
			Mockery::mock( ProcessorService::class ),
			'wp-content/cache/critical-css/',
			'wp-content/plugins/wp-rocket/views/metabox/cpcss'
		);


		$this->assertSame(
			$expected,
			$subscriber->add_async_css_mobile_option( $options )
		);
	}
}
