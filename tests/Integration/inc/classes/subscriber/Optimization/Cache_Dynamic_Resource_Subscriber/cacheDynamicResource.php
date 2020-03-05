<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\Optimization\Cache_Dynamic_Resource_Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Subscriber\Optimization\Cache_Dynamic_Resource_Subscriber::cache_dynamic_resource
 * @group  CacheDynamicResource
 */
class Test_CacheDynamicResource extends FilesystemTestCase {
	private static $container;
	private $subscriber;
	protected $rootVirtualDir = 'wp-content';
	protected $structure = [
		'cache' => [
			'busting' => [
				'1' => [],
			],
		],
		'themes' => [
			'twentytwenty' => [
				'style.php' => 'test',
				'assets'    => [
					'script.php' => 'test',
				]
			]
		],
		'plugins' => [
			'hello-dolly' => [
				'style.php'  => '',
				'script.php' => '',
			]
		],
	];

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$container = apply_filters( 'rocket_container', null );
	}

	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )->atLeast( 1 )->with( 'WP_ROCKET_CACHE_BUSTING_PATH' )->andReturn( $this->filesystem->getUrl( '/cache/busting/' ) );
		$this->subscriber = self::$container->get( 'cache_dynamic_resource_subscriber' );
	}

	public function testShouldReplaceURLWhenDynamicCSSFile() {
		Functions\when( 'current_filter' )->justReturn( 'style_loader_src' );

		$callback = function() {
			return '123456';
		};
	
		add_filter( 'pre_get_rocket_option_minify_css_key', $callback );

		$this->assertSame(
			'http://example.org/wp-content/cache/busting/1/wp-content/themes/twentytwenty/style-123456.css',
			$this->subscriber->cache_dynamic_resource( 'http://example.org/wp-content/themes/twentytwenty/style.php' )
		);

		remove_filter( 'pre_get_rocket_option_minify_css_key', $callback );
	}
}
