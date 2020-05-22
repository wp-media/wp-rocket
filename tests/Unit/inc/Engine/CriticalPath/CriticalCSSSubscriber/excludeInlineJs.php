<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::exclude_inline_js
 *
 * @group  Subscribers
 * @group  CriticalPath
 */
class Test_ExcludeInlineJs extends TestCase {

	public function setUp() {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\expect( 'home_url' )->once()->with( '/' )->andReturn( 'http://example.com/' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldInsertCriticalCSS( $excluded_inline, $expected_inline ) {
		$subscriber   = new CriticalCSSSubscriber(
			Mockery::mock( CriticalCSS::class, [ Mockery::mock( CriticalCSSGeneration::class ), Mockery::mock( Options_Data::class ), null ] ),
			Mockery::mock( Options_Data::class )
		);

		// Run it.
		$this->assertSame( $expected_inline, $subscriber->exclude_inline_js( $excluded_inline ) );
	}
}
