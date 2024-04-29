<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering ::is_rocket_generate_caching_mobile_files
 * @uses   ::get_rocket_option
 *
 * @group  Options
 * @group  Functions
 */
class Test_IsRocketGenerateCachingMobileFiles extends TestCase {
	private $config = [];

	protected function setUp() : void {
		parent::setUp();

		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedOptionValue( array $settings, $expected ) {
		$settings = array_merge( $this->config['settings'], $settings );
		Functions\expect( 'get_rocket_option' )
			->once()
			->with( 'cache_mobile', false )
			->andReturn( $settings['cache_mobile'] );
		if ( 1 === (int) $settings['cache_mobile' ] ) {
			Functions\expect( 'get_rocket_option' )
				->once()
				->with( 'do_caching_mobile_files', false )
				->andReturn( $settings['do_caching_mobile_files'] );
		}

		$this->assertSame( $expected, is_rocket_generate_caching_mobile_files() );
	}

	public function providerTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data'];
	}

	private function loadConfig() {
		$this->config = $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}
}
