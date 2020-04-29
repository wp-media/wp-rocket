<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::regenerate_minify_css_key
 * @uses   ::create_rocket_uniqid
 *
 * @group  Optimize
 * @group  AdminSubscriber
 * @group  AdminOnly
 */
class Test_RegenerateMinifyCssKey extends TestCase {
	private static $original_settings;
	private        $old_settings = [];
	private        $config       = [];

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$original_settings = get_option( 'wp_rocket_settings', [] );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		update_option( 'wp_rocket_settings', self::$original_settings );
	}

	public function setUp() {
		parent::setUp();

		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		$this->old_settings = array_merge( self::$original_settings, $this->config['settings'] );
		update_option( 'wp_rocket_settings', $this->old_settings );
	}

	public function tearDown() {
		parent::tearDown();

		delete_option( 'wp_rocket_settings' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testRegenerateMinifyCssKey( $settings, $expected, $should_run ) {
		update_option(
			'wp_rocket_settings',
			array_merge( $this->old_settings, $settings )
		);

		$options = get_option( 'wp_rocket_settings', [] );

		if ( $should_run ) {
			$this->assertArrayHasKey( 'minify_css_key', $options );
			$this->assertEquals( strlen( '5ea8b0bf1b875099188739' ), strlen( $options['minify_css_key'] ) );
			unset( $expected['minify_css_key'] );
		}

		foreach ( $expected as $key => $value ) {
			$this->assertSame( $value, $options[ $key ] );
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
