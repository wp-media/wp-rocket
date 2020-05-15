<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AdminSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CriticalPath\AdminSubscriber;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::add_hidden_async_css_mobile
 * @group  CriticalPath
 */
class Test_AddHiddenAsyncCssMobile extends TestCase {
	public function setUp() {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->beacon     = Mockery::mock( Beacon::class );
		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new AdminSubscriber(
			$this->options,
			$this->beacon,
			'wp-content/cache/critical-css/',
			'wp-content/plugins/wp-rocket/views/metabox/cpcss'
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddValueToArray( $hidden_fields, $expected ) {
        $this->assertSame(
			$expected,
			$this->subscriber->add_hidden_async_css_mobile( $hidden_fields )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'addHiddenAsyncCssMobile' );
	}
}
