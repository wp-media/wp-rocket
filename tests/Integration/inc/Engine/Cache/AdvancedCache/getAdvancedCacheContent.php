<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\AdvancedCache::get_advanced_cache_content
 * @uses   ::is_rocket_generate_caching_mobile_files
 * @uses   ::rocket_get_constant
 * @uses   ::rocket_direct_filesystem
 *
 * @group  AdvancedCache
 */
class Test_GetAdvancedCacheContent extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/AdvancedCache/getAdvancedCacheContent.php';
	private   $original_settings;
	private static $advanced_cache;

	public static function setUpBeforeClass() {
		$container            = apply_filters( 'rocket_container', null );
		self::$advanced_cache = $container->get( 'advanced_cache' );
	}

	public function setUp() {
		parent::setUp();

		// Mocks the various filesystem constants.
		$this->whenRocketGetConstant();

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
	public function testShouldReturnExpectedContent( $settings, $expected ) {
		update_option(
			'wp_rocket_settings',
			array_merge( $this->original_settings, $this->config['settings'], $settings )
		);

		$this->assertSame( $expected, self::$advanced_cache->get_advanced_cache_content() );
	}
}
