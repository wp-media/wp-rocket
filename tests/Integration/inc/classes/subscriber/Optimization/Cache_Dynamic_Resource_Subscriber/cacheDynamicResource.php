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

	/**
	 * @dataProvider includedCSSURLProvider
	 */
	public function testShouldReplaceURLWhenDynamicCSSFile( $url, $expected ) {
		Functions\when( 'current_filter' )->justReturn( 'style_loader_src' );

		$callback = function() {
			return '123456';
		};
	
		add_filter( 'pre_get_rocket_option_minify_css_key', $callback );

		$this->assertSame(
			$expected,
			$this->subscriber->cache_dynamic_resource( $url )
		);

		remove_filter( 'pre_get_rocket_option_minify_css_key', $callback );
	}

	/**
	 * @dataProvider includedCSSCDNURLProvider
	 */
	public function testShouldReplaceURLWithCDNURLWhenDynamicCSSFile( $url, $expected ) {
		Functions\when( 'current_filter' )->justReturn( 'style_loader_src' );

		$callback_minify_key = function() {
			return '123456';
		};

		$callback_cdn_cnames = function() {
			return [
				'https://123456.rocketcdn.me',
			];
		};

		$callback_cdn_zones = function() {
			return [
				'css',
			];
		};
	
		add_filter( 'pre_get_rocket_option_cdn', '__return_true' );
		add_filter( 'pre_get_rocket_option_minify_css_key', $callback_minify_key );
		add_filter( 'pre_get_rocket_option_cdn_cnames', $callback_cdn_cnames );
		add_filter( 'pre_get_rocket_option_cdn_zone', $callback_cdn_zones );

		$this->assertSame(
			$expected,
			$this->subscriber->cache_dynamic_resource( $url )
		);

		remove_filter( 'pre_get_rocket_option_cdn', '__return_true' );
		remove_filter( 'pre_get_rocket_option_minify_css_key', $callback_minify_key );
		remove_filter( 'pre_get_rocket_option_cdn_cnames', $callback_cdn_cnames );
		remove_filter( 'pre_get_rocket_option_cdn_zone', $callback_cdn_zones );
	}

	/**
	 * @dataProvider includedJSURLProvider
	 */
	public function testShouldReplaceURLWhenDynamicJSFile( $url, $expected ) {
		Functions\when( 'current_filter' )->justReturn( 'script_loader_src' );

		$callback = function() {
			return '123456';
		};
	
		add_filter( 'pre_get_rocket_option_minify_js_key', $callback );

		$this->assertSame(
			$expected,
			$this->subscriber->cache_dynamic_resource( $url )
		);

		remove_filter( 'pre_get_rocket_option_minify_js_key', $callback );
	}

	/**
	 * @dataProvider includedJSCDNURLProvider
	 */
	public function testShouldReplaceURLWithCDNURLWhenDynamicJSFile( $url, $expected ) {
		Functions\when( 'current_filter' )->justReturn( 'script_loader_src' );

		$callback_minify_key = function() {
			return '123456';
		};

		$callback_cdn_cnames = function() {
			return [
				'https://123456.rocketcdn.me',
			];
		};

		$callback_cdn_zones = function() {
			return [
				'js',
			];
		};
	
		add_filter( 'pre_get_rocket_option_cdn', '__return_true' );
		add_filter( 'pre_get_rocket_option_minify_js_key', $callback_minify_key );
		add_filter( 'pre_get_rocket_option_cdn_cnames', $callback_cdn_cnames );
		add_filter( 'pre_get_rocket_option_cdn_zone', $callback_cdn_zones );

		$this->assertSame(
			$expected,
			$this->subscriber->cache_dynamic_resource( $url )
		);

		remove_filter( 'pre_get_rocket_option_cdn', '__return_true' );
		remove_filter( 'pre_get_rocket_option_minify_js_key', $callback_minify_key );
		remove_filter( 'pre_get_rocket_option_cdn_cnames', $callback_cdn_cnames );
		remove_filter( 'pre_get_rocket_option_cdn_zone', $callback_cdn_zones );
	}

	/**
	 * @dataProvider excludedURLProvider
	 */
	public function testShouldPreserveURLWhenURLIsExcluded( $url ) {
		$this->assertSame(
			$url,
			$this->subscriber->cache_dynamic_resource( $url )
		);
	}

	public function includedCSSURLProvider() {
		return [
			[ 
				'http://example.org/wp-content/themes/twentytwenty/style.php',
				'http://example.org/wp-content/cache/busting/1/wp-content/themes/twentytwenty/style-123456.css',
			 ],
			[
				'http://example.org/wp-content/plugins/hello-dolly/style.php?ver=5.3',
				'http://example.org/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/style-123456.css'
			],
		];
	}

	public function includedCSSCDNURLProvider() {
		return [
			[ 
				'http://example.org/wp-content/themes/twentytwenty/style.php',
				'https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/themes/twentytwenty/style-123456.css',
			],
			[
				'http://example.org/wp-content/plugins/hello-dolly/style.php?ver=5.3',
				'https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/style-123456.css'
			],
		];
	}

	public function includedJSURLProvider() {
		return [
			[ 
				'http://example.org/wp-content/themes/twentytwenty/assets/script.php',
				'http://example.org/wp-content/cache/busting/1/wp-content/themes/twentytwenty/assets/script-123456.js',
			 ],
			[
				'http://example.org/wp-content/plugins/hello-dolly/script.php?ver=5.3',
				'http://example.org/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/script-123456.js'
			],
		];
	}

	public function includedJSCDNURLProvider() {
		return [
			[ 
				'http://example.org/wp-content/themes/twentytwenty/assets/script.php',
				'https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/themes/twentytwenty/assets/script-123456.js',
			 ],
			[
				'http://example.org/wp-content/plugins/hello-dolly/script.php?ver=5.3',
				'https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/script-123456.js'
			],
		];
	}

	public function excludedURLProvider() {
		return [
			[ 'http://example.org/wp-content/themes/storefront/style.css' ],
			[ 'https://example.org/wp-content/themes/storefront/script.js' ],
			[ 'http://example.org' ],
			[ 'http://example.org/wp-admin/admin-ajax.php' ],
			[ 'https://example.org/wp-content/plugins/test/style.php?data=foo&ver=5.3' ],
			[ 'https://example.org/wp-content/plugins/test/script.php?data=foo' ],
			[ 'http://en.example.org/wp-content/plugins/test/style.css' ],
			[ 'https://example.de/wp-content/themes/storefront/assets/script.js?ver=5.3' ],
			[ 'http://123456.rocketcdn.me/wp-content/plugins/test/style.css' ],
		];
	}
}
