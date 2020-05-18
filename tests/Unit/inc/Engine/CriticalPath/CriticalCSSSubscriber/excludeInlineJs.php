<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::exclude_inline_js
 * @group  Subscribers
 * @group  CriticalCss
 */
class Test_ExcludeInlineJs extends TestCase {
	private $critical_css;
	private $subscriber;

	public function setUp() {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\expect( 'home_url' )->once()->with( '/' )->andReturn( 'http://example.com' );

		$this->critical_css = Mockery::mock( CriticalCSS::class, [ Mockery::mock( CriticalCSSGeneration::class ) ] );
		$this->subscriber   = new CriticalCSSSubscriber( $this->critical_css, Mockery::mock( Options_Data::class ) );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldInsertCriticalCSS( $excluded_inline, $expected_inline ) {
		// Run it.
		$excluded_inline = $this->subscriber->exclude_inline_js( $excluded_inline );
		$this->assertSame( $excluded_inline, $expected_inline );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'excludeInlineJs' );
	}
}
