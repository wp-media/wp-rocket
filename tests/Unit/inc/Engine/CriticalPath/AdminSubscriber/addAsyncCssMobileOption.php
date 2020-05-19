<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AdminSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CriticalPath\AdminSubscriber;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::add_async_css_mobile_option
 * @group  CriticalPath
 */
class Test_AddAsyncCssMobileOption extends TestCase {
	public function setUp() {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->beacon     = Mockery::mock( Beacon::class );
		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new AdminSubscriber(
			$this->options,
			$this->beacon,
			'wp-content/cache/critical-css/',
			'wp-content/plugins/wp-rocket/views/cpcss'
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddOption( $options, $expected ) {
		$this->assertSame(
			$expected,
			$this->subscriber->add_async_css_mobile_option( $options )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'addAsyncCssMobileOption' );
	}
}
