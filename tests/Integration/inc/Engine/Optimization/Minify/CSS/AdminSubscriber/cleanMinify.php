<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::clean_minify
 * @group  Optimize
 * @group  AdminSubscriber
 * @group  AdminOnly
 */
class Test_CleanMinify extends TestCase {
	private $config;
	private $original_settings;

	public function setUp() {
		parent::setUp();

		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		$this->original_settings = get_option( 'wp_rocket_settings', [] );

		update_option(
			'wp_rocket_settings',
			array_merge( $this->original_settings, $this->config['settings'] )
		);
	}

	public function tearDown() {
		parent::tearDown();

		if ( empty( $this->original_settings ) ) {
			delete_option( 'wp_rocket_settings' );
		} else {
			update_option( 'wp_rocket_settings', $this->original_settings );
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testCleanMinify( $value, $shouldRun ) {
		if ( $shouldRun ) {
			Functions\expect( 'rocket_clean_minify' )
				->once()
				->with( 'css' );
		} else {
			Functions\expect( 'rocket_clean_minify' )->never();
		}

		update_option(
			'wp_rocket_settings',
			array_merge( $this->original_settings, $value )
		);
	}

	public function providerTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data'];
	}

	private function loadConfig() {
		$this->config = $this->getTestData( __DIR__, 'cleanMinify' );
	}
}
