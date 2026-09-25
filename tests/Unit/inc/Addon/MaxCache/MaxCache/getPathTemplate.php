<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\MaxCache\MaxCache;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\MaxCache\MaxCache;
use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\MaxCache\MaxCache::get_path_template
 *
 * @group MaxCache
 */
class TestGetPathTemplate extends TestCase {
	/**
	 * Instance under test.
	 *
	 * @var MaxCache
	 */
	private $maxcache;

	/**
	 * The settings row the case under test describes.
	 *
	 * @var array
	 */
	private $row = [];

	/**
	 * Sets the stage for each test.
	 *
	 * @return void
	 */
	/**
	 * The options the case's row is read through.
	 *
	 * @var \WP_Rocket\Admin\Options
	 */
	private $options_api;

	/**
	 * Returns it, for a case that needs an add-on of its own.
	 *
	 * @return \WP_Rocket\Admin\Options
	 */
	private function maxcache_options() {
		return $this->options_api;
	}

	protected function setUp(): void {
		parent::setUp();

		$options_api = Mockery::mock( Options::class );
		// Answered when asked, not when mocked: the row each case describes is set in the test body.
		$options_api->shouldReceive( 'get' )->with( 'settings', [] )->andReturnUsing(
			function () {
				return $this->row;
			}
		);
		$options_api->shouldReceive( 'get' )->andReturn( false );
		$options_api->shouldReceive( 'set' )->andReturn( true );

		$this->options_api = $options_api;
		$this->maxcache    = new MaxCache( $options_api );

		Functions\when( 'sanitize_text_field' )->returnArg();
		// Answered when asked, not when stubbed: the case's row is set in the test body.
		Functions\when( 'content_url' )->alias(
			function () {
				return $this->row['content_url'] ?? 'https://example.org/wp-content';
			}
		);
		Functions\when( 'site_url' )->alias(
			function () {
				return $this->row['site_url'] ?? 'https://example.org';
			}
		);
		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
		Functions\when( 'includes_url' )->justReturn( 'https://example.org/wp-includes/' );
		Functions\when( 'untrailingslashit' )->alias(
			function ( $value ) {
				return rtrim( $value, '/\\\\' );
			}
		);
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'trailingslashit' )->alias(
			function ( $value ) {
				return rtrim( $value, '/\\' ) . '/';
			}
		);
		Functions\when( 'wp_normalize_path' )->returnArg();
		Functions\when( 'is_ssl' )->justReturn( false );
		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
		Functions\when( 'sanitize_key' )->alias(
			function ( $key ) {
				return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
			}
		);
		Functions\when( 'rocket_get_constant' )->alias(
			function ( $name, $default = null ) {
				$constants = [
					'WP_ROCKET_CACHE_PATH' => $this->row['cache_path'] ?? '/var/www/html/wp-content/cache/wp-rocket/',
					'WP_CONTENT_DIR'       => '/var/www/html/wp-content',
					'ABSPATH'              => $this->row['abspath'] ?? '/var/www/html/',
					'LOGGED_IN_COOKIE'     => 'wordpress_logged_in_abc123',
				];

				return $constants[ $name ] ?? $default;
			}
		);

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
	 * Builds the template for a given set of options.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array  $config   Plugin options for the case.
	 * @param string $expected Expected template.
	 *
	 * @return void
	 */
	public function testShouldReturnExpectedTemplate( $config, $expected ) {
		$config = array_merge(
			[
				'cache_ssl'         => 1,
				'cache_webp'        => 0,
				'cache_logged_user' => 0,
				'secret_cache_key'  => 'abc123',
				'webp_disabled'     => false,
				'logged_shared'     => false,
				'cookies'           => [],
				'gzip'              => false,
				'mobile_files'      => false,
				'detection'         => true,
				'tablet'            => 'desktop',
			],
			$config
		);

		$this->row = $config;

		if ( ! $config['detection'] ) {
			// The library the plugin names mobile files with is not loadable here, so it names one
			// file for every device and the template must not ask for the other.
			$this->maxcache = new class( $this->maxcache_options() ) extends MaxCache {
				protected function device_detection_available(): bool {
					return false;
				}
			};
		}

		Functions\when( 'get_rocket_cache_dynamic_cookies' )->justReturn( $config['cookies'] );
		Functions\when( 'is_rocket_generate_caching_mobile_files' )->justReturn( $config['mobile_files'] );
		// wpm_apply_filters_typed() is left alone: it is a real function that calls apply_filters(),
		// which is what the stub below replaces.
		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) use ( $config ) {
				switch ( $tag ) {
					case 'rocket_force_gzip_htaccess_rules':
						return $config['gzip'];
					case 'rocket_common_cache_logged_users':
						return $config['logged_shared'];
					case 'rocket_disable_webp_cache':
						return $config['webp_disabled'];
					case 'rocket_cache_mobile_files_tablet':
						return $config['tablet'];
					default:
						return $value;
				}
			}
		);

		if ( ! $config['gzip'] ) {
			// The suffix is only appended when a compressed variant is written.
			$this->assertStringNotContainsString( '{GZIP_SUFFIX}', $this->maxcache->get_path_template() );
		}

		$this->assertSame( $expected, $this->maxcache->get_path_template() );
	}
}
