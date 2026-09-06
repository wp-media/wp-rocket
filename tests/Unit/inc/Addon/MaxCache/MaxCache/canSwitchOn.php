<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\MaxCache\MaxCache;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\MaxCache\MaxCache;
use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\MaxCache\MaxCache::can_switch_on
 *
 * @group MaxCache
 */
class TestCanSwitchOn extends TestCase {
	/**
	 * Sets the stage for each test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
	}

	/**
	 * Cleans up after each test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		unset( $_SERVER['DOCUMENT_ROOT'] );

		parent::tearDown();
	}

	/**
	 * Checks whether a site may be switched on without being asked.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Server and plugin state for the case.
	 * @param bool  $expected Whether the add-on may switch itself on.
	 *
	 * @return void
	 */
	public function testShouldReturnExpectedDecision( $config, $expected ) {
		Functions\when( '__' )->returnArg();
		Functions\when( 'is_multisite' )->justReturn( false );
		Functions\when( 'get_locale' )->justReturn( 'en_US' );
		Functions\when( 'home_url' )->justReturn( 'https://example.org' );
		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
		Functions\when( 'is_rocket_generate_caching_mobile_files' )->justReturn( false );
		Functions\when( 'is_rocket_cache_mobile' )->justReturn( true );
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'wp_normalize_path' )->returnArg();
		Functions\when( 'get_rocket_cache_reject_uri' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_reject_ua' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_reject_cookies' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_mandatory_cookies' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_query_string' )->justReturn( [] );
		Functions\when( 'rocket_get_ignored_parameters' )->justReturn( [] );
		Functions\when( 'is_ssl' )->justReturn( $config['is_ssl'] ?? false );
		Functions\when( 'content_url' )->justReturn( 'https://example.org/wp-content' );
Functions\when( 'site_url' )->justReturn( 'https://example.org' );
		Functions\when( 'includes_url' )->justReturn( 'https://example.org/wp-includes/' );
		Functions\when( 'untrailingslashit' )->alias(
			function ( $value ) {
				return rtrim( (string) $value, '/\\' );
			}
		);
		Functions\when( 'trailingslashit' )->alias(
			function ( $value ) {
				return rtrim( $value, '/\\' ) . '/';
			}
		);
		Functions\when( 'rocket_get_constant' )->alias(
			function ( $name, $default = null ) {
				$constants = [
					'WP_ROCKET_CACHE_PATH' => '/var/www/html/wp-content/cache/wp-rocket/',
'ABSPATH'              => '/var/www/html/',
					'WP_CONTENT_DIR'       => '/var/www/html/wp-content',
				];

				return $constants[ $name ] ?? $default;
			}
		);
		// wpm_apply_filters_typed() is left alone: it is a real function that calls apply_filters(),
		// which is what the stub below replaces.
		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) use ( $config ) {
				return 'rocket_maxcache_mode' === $tag ? $config['mode'] : $value;
			}
		);

		$options_api = Mockery::mock( Options::class );
		// The settings row this case describes: the add-on reads it through the injected object.
		$options_api->shouldReceive( 'get' )->with( 'settings', [] )->andReturn( [ 'cache_ssl' => 1 ] );
		$options_api->shouldReceive( 'get' )->andReturn( false );
		$options_api->shouldReceive( 'set' )->andReturn( true );

		$maxcache = new class( $options_api, $config['writes_files'], $config['daemon'] ) extends MaxCache {
			/**
			 * Whether the plugin is set up to write cache files.
			 *
			 * @var bool
			 */
			private $writes_files;

			/**
			 * Whether the daemon accepts a connection.
			 *
			 * @var bool
			 */
			private $daemon;

			/**
			 * Constructor.
			 *
			 * @param Options $options_api  Options instance.
			 * @param bool    $writes_files Whether the plugin writes cache files.
			 * @param bool    $daemon       Whether the daemon accepts a connection.
			 */
			public function __construct( Options $options_api, bool $writes_files, bool $daemon ) {
				parent::__construct( $options_api );

				$this->writes_files = $writes_files;
				$this->daemon       = $daemon;
			}

			/**
			 * {@inheritdoc}
			 */
			protected function plugin_writes_cache_files(): bool {
				return $this->writes_files;
			}


			/**
			 * {@inheritdoc}
			 */
			protected function configd_answers(): bool {
				return $this->daemon;
			}
		};

		$this->assertSame( $expected, $maxcache->can_switch_on() );
	}
}
