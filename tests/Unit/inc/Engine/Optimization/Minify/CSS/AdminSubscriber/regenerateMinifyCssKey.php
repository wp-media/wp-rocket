<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::regenerate_minify_css_key
 *
 * @group  Optimize
 * @group  Minify
 * @group  AdminSubscriber
 */
class Test_RegenerateMinifyCssKey extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testRegenerateMinifyCssKey( $value, $expected, $should_run ) {
		if ( $should_run ) {
			Functions\expect( 'create_rocket_uniqid' )
				->once()
				->andReturn( 'minify_css_key' );
		} else {
			Functions\expect( 'create_rocket_uniqid' )->never();
		}

		$subcriber = new AdminSubscriber();
		$this->assertSame(
			$expected,
			$subcriber->regenerate_minify_css_key( $value, $this->config['settings'] )
		);
	}
}
