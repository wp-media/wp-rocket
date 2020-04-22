<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::regenerate_minify_css_key
 * @group  Optimize
 * @group  AdminSubscriber
 * @group  AdminOnly
 */
class Test_RegenerateMinifyCssKey extends TestCase {
	private $original_settings;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		update_option(
			'wp_rocket_settings',
			array_merge(
				get_option( 'wp_rocket_settings', [] ),
				[
					'minify_css'  => false,
					'exclude_css' => [],
					'cdn'         => false,
					'cdn_cnames'  => [],
				]
			)
		);
	}

	public function setUp() {
		parent::setUp();

		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		$this->original_settings = get_option( 'wp_rocket_settings', [] );
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
	public function testRegenerateMinifyCssKey( $new_value, $should_run, $expected ) {
		if ( $should_run ) {
			Functions\expect( 'create_rocket_uniqid' )
				->once()
				->andReturn( 'minify_css_key' );
		} else {
			Functions\expect( 'create_rocket_uniqid' )->never();
		}

		update_option(
			'wp_rocket_settings',
			array_merge( $this->original_settings, $new_value )
		);

		$options = get_option( 'wp_rocket_settings', [] );

		foreach ( $expected as $expected_key => $expected_value ) {
			$this->assertSame(
				$expected_value,
				$options[ $expected_key ]
			);
		}
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
