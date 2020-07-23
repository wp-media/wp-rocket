<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\WPCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WP_Rocket\Tests\Integration\CapTrait;

/**
 * @covers \WP_Rocket\Engine\Cache\WPCache::set_wp_cache_constant
 * @uses   ::rocket_valid_key
 *
 * @group WPCache
 * @group vfs
 */
class Test_SetWpCacheConstant extends FilesystemTestCase {
	use CapTrait;

	protected $path_to_test_data = '/inc/Engine/Cache/WPCache/setWpCacheConstant.php';
	private static $wp_cache;
	protected $user_id = 0;
	private $filter_set;

	public static function setUpBeforeClass() {
		self::hasAdminCapBeforeClass();

		$container = apply_filters( 'rocket_container', null );

		self::$wp_cache = $container->get( 'wp_cache' );
	}

	public function setUp() {
		parent::setUp();

		self::setAdminCap();

		$this->user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $this->user_id );
	}

	public function tearDown() {
		remove_filter( 'rocket_wp_config_name', [ $this, 'setWpCacheFilePath' ] );

		self::resetAdminCap();

		if ( $this->user_id > 0 ) {
			wp_delete_user( $this->user_id );
		}

		remove_filter( 'rocket_set_wp_cache_constant', [ $this, 'filterSetWpCacheConstant' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddWpCacheConstant( $config, $expected ) {
		$wp_config = $this->filesystem->getUrl( 'wp-config.php' );
		$this->filesystem->put_contents( $wp_config, $config['original'] );
		$this->filter_set = $config['filter'];

		add_filter( 'rocket_set_wp_cache_constant', [ $this, 'filterSetWpCacheConstant' ] );

		Functions\when( 'rocket_valid_key' )->justReturn( $config['valid_key'] );

		self::$wp_cache->set_wp_cache_constant( true );

		$this->assertEquals(
			$expected,
			str_replace( "\r\n", "\n", $this->filesystem->get_contents( $wp_config ) )
		);
	}

	public function filterSetWpCacheConstant() {
		return $this->filter_set;
	}
}
