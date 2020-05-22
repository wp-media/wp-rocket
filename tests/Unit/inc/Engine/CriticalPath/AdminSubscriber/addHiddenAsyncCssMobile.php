<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AdminSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\AdminSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::add_hidden_async_css_mobile
 *
 * @group  CriticalPath
 */
class Test_AddHiddenAsyncCssMobile extends TestCase {

	public function setUp() {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddValueToArray( $hidden_fields, $expected ) {
		$subscriber = new AdminSubscriber(
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Beacon::class ),
			Mockery::mock( CriticalCSS::class ),
			'wp-content/cache/critical-css/',
			'wp-content/plugins/wp-rocket/views/cpcss'
		);

        $this->assertSame(
			$expected,
			$subscriber->add_hidden_async_css_mobile( $hidden_fields )
		);
	}
}
