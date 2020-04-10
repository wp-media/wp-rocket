<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::clean_minify
 * @group  Optimize
 * @group  AdminSubscriber
 */
class Test_CleanMinify extends TestCase {
	private $admin_susbcriber;

	public function setUp() {
		parent::setUp();
		$this->admin_susbcriber = new AdminSubscriber();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCombineCSS( $old_value, $value, $shouldRun ) {
		if ( $shouldRun ) {
			Functions\expect( 'rocket_clean_minify' )
				->once()
				->with( 'css' );
		} else {
			Functions\expect( 'rocket_clean_minify' )->never();
		}
		$this->admin_susbcriber->clean_minify( $old_value, $value );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'cleanMinify' );
	}
}
