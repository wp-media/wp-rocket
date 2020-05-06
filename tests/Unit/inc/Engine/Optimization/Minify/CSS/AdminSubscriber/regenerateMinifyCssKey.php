<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::regenerate_minify_css_key
 *
 * @group  Optimize
 * @group  AdminSubscriber
 */
class Test_RegenerateMinifyCssKey extends TestCase {
	private $config;

	public function setUp() {
		parent::setUp();

		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}
	}

	/**
	 * @dataProvider providerTestData
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

	public function providerTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data'];
	}

	private function loadConfig() {
		$this->config = $this->getTestData( __DIR__, 'regenerateMinifyCssKey' );
	}
}
