<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\MaxCache\MaxCache;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\MaxCache\MaxCache;
use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\MaxCache\MaxCache::get_unsupported_reason
 *
 * @group MaxCache
 */
class TestGetUnsupportedReason extends TestCase {
	/**
	 * Sets the stage for each test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
		$_SERVER['SERVER_PORT']   = '443';
	}

	/**
	 * Cleans up after each test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		unset( $_SERVER['DOCUMENT_ROOT'], $_SERVER['SERVER_PORT'], $_SERVER['HTTP_X_FORWARDED_PROTO'], $_SERVER['HTTPS'], $_SERVER['SERVER_SOFTWARE'] );

		unset( $GLOBALS['is_apache'] );

		parent::tearDown();
	}

	/**
	 * Checks the refusal for a given configuration.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array  $config   Site and plugin state for the case.
	 * @param string $expected Fragment expected in the reason, empty when supported.
	 *
	 * @return void
	 */
	public function testShouldReturnExpectedReason( $config, $expected ) {
		$reason = $this->maxcache_for( $config )->get_unsupported_reason();

		if ( '' === $expected ) {
			$this->assertSame( '', $reason, 'A supported configuration must not be refused.' );

			return;
		}

		$this->assertStringContainsString( $expected, $reason );
	}

	/**
	 * Returns an add-on instance judging the site the case describes.
	 *
	 * @param array $config Site and plugin state for the case.
	 *
	 * @return MaxCache
	 */
	private function maxcache_for( array $config ): MaxCache {
		$config = array_merge(
			[
				'mode'              => 'apache',
				'multisite'         => false,
				'locale'            => 'en_US',
				'writes_files'      => true,
				'cache_logged_user' => 0,
				'secret_cache_key'  => 'abc123',
				'logged_shared'     => false,
				'logged_in_cookie'  => 'wordpress_logged_in_abc123',
				'mobile_cache'      => true,
				'mobile_files'      => false,
				'home'              => 'https://example.org',
				'cache_ssl'         => 1,
				'serving_vetoed'    => false,
				'url_no_dots'       => false,
				'behind_proxy'      => false,
				'forwarded_only'    => false,
				'dynamic_cookies'   => [],
				'reject_uri'        => '',
				'cache_path'        => '/var/www/html/wp-content/cache/wp-rocket/',
			],
			$config
		);

		/*
		 * The dangerous shape: PHP knows the request is secure — a snippet in wp-config, a filter —
		 * while nothing the server reads says so, and the two then name different files.
		 */
		if ( $config['behind_proxy'] ) {
			$_SERVER['SERVER_PORT'] = '80';
		}

		// The safe direction: a forwarded header on a plain listener, and PHP does not call the
		// request secure either.
		if ( ! empty( $config['forwarded_only'] ) ) {
			$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
			$_SERVER['SERVER_PORT']            = '80';
		}

		if ( ! empty( $config['forwarded_https'] ) ) {
			$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
		}

		// What the snippet in wp-config.php does on every site behind a TLS-terminating proxy, and
		// what a server terminating TLS itself sets: the same variable, and only the port and the
		// header tell the two apart.
		if ( ! empty( $config['https_env'] ) ) {
			$_SERVER['HTTPS'] = 'on';
		}

		if ( isset( $config['port'] ) ) {
			$_SERVER['SERVER_PORT'] = $config['port'];
		}

		// What the plugin's own writer judges the server by. Absent by default, which is what WP-CLI
		// and cron look like: there the add-on has no request to judge by and does not refuse.
		if ( isset( $config['server_software'] ) ) {
			$_SERVER['SERVER_SOFTWARE'] = $config['server_software'];
			$GLOBALS['is_apache']       = false !== stripos( (string) $config['server_software'], 'apache' );
		}

		Functions\when( '__' )->returnArg();
		Functions\when( 'is_multisite' )->justReturn( $config['multisite'] );
		Functions\when( 'get_locale' )->justReturn( $config['locale'] );
		Functions\when( 'home_url' )->justReturn( $config['home'] );
		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
		Functions\when( 'is_rocket_generate_caching_mobile_files' )->justReturn( $config['mobile_files'] );
		Functions\when( 'is_rocket_cache_mobile' )->justReturn( $config['mobile_cache'] );
		Functions\when( 'get_rocket_cache_dynamic_cookies' )->justReturn( $config['dynamic_cookies'] );
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'sanitize_key' )->alias(
			function ( $key ) {
				return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
			}
		);
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'wp_normalize_path' )->returnArg();
		Functions\when( 'get_rocket_cache_reject_uri' )->justReturn( $config['reject_uri'] );
		Functions\when( 'get_rocket_cache_reject_ua' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_reject_cookies' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_mandatory_cookies' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_query_string' )->justReturn( [] );
		Functions\when( 'rocket_get_ignored_parameters' )->justReturn( [] );
		Functions\when( 'is_ssl' )->justReturn( $config['is_ssl'] ?? $config['behind_proxy'] );
		// Nothing here writes: the verdict about a proxy is read from the request being served.
		$options_api = Mockery::mock( Options::class );
		$options_api->shouldReceive( 'set' )->never();
		// The settings row this case describes: the add-on reads it through the injected object.
		$options_api->shouldReceive( 'get' )->with( 'settings', [] )->andReturn( $config );
		Functions\when( 'content_url' )->justReturn( 'https://example.org/wp-content' );
Functions\when( 'site_url' )->justReturn( 'https://example.org' );
		Functions\when( 'includes_url' )->justReturn( 'https://example.org/wp-includes/' );
		Functions\when( 'untrailingslashit' )->alias(
			function ( $value ) {
				return rtrim( $value, '/\\\\' );
			}
		);
		Functions\when( 'trailingslashit' )->alias(
			function ( $value ) {
				return rtrim( $value, '/\\' ) . '/';
			}
		);
		Functions\when( 'rocket_get_constant' )->alias(
			function ( $name, $default = null ) use ( $config ) {
				$constants = [
					'WP_ROCKET_CACHE_PATH' => $config['cache_path'],
'ABSPATH'              => '/var/www/html/',
					'WP_CONTENT_DIR'       => '/var/www/html/wp-content',
					'LOGGED_IN_COOKIE'     => $config['logged_in_cookie'],
				];

				return $constants[ $name ] ?? $default;
			}
		);
		Functions\when( 'get_rocket_option' )->alias(
			function ( $name, $default = false ) use ( $config ) {
				return $config[ $name ] ?? $default;
			}
		);
		// wpm_apply_filters_typed() is left alone: it is a real function that calls apply_filters(),
		// which is what the stub below replaces.
		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) use ( $config ) {
				switch ( $tag ) {
					case 'rocket_maxcache_mode':
						return $config['mode'];
					case 'rocket_htaccess_mod_rewrite':
						return $config['serving_vetoed'] ? false : $value;
					case 'rocket_url_no_dots':
						return $config['url_no_dots'];
					case 'rocket_common_cache_logged_users':
						return $config['logged_shared'];
					default:
						return $value;
				}
			}
		);

		$maxcache = new class( $options_api, $config['writes_files'] ) extends MaxCache {
			/**
			 * Whether the plugin is set up to write cache files.
			 *
			 * @var bool
			 */
			private $writes_files;

			/**
			 * Constructor.
			 *
			 * @param Options $options_api  Options instance.
			 * @param bool    $writes_files Whether the plugin writes cache files.
			 */
			public function __construct( Options $options_api, bool $writes_files ) {
				parent::__construct( $options_api );

				$this->writes_files = $writes_files;
			}

			/**
			 * {@inheritdoc}
			 */
			protected function plugin_writes_cache_files(): bool {
				return $this->writes_files;
			}

		};

		return $maxcache;
	}

	/**
	 * Checks that a callback in the chain can ask this class about the same site.
	 *
	 * @return void
	 */
	public function testShouldAnswerACallbackThatAsksThisClassBack() {
		$calls    = 0;
		$maxcache = $this->maxcache_for( [] );

		// Re-stated after the helper: the same answers, plus a callback that asks this class back.
		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) use ( &$calls, $maxcache ) {
				if ( 'rocket_maxcache_mode' === $tag ) {
					return 'apache';
				}

				// One of the filters the chain reads itself, so the guard is exercised where it matters.
				if ( 'rocket_url_no_dots' !== $tag ) {
					return $value;
				}

				++$calls;

				// Self-limited: a class that re-enters here would otherwise run until the stack gives
				// out, and a hung process says less than a count does.
				if ( $calls < 3 ) {
					$maxcache->get_unsupported_reason();
				}

				return $value;
			}
		);

		$maxcache->get_unsupported_reason();

		$this->assertSame( 1, $calls, 'The chain must be run once per request.' );
	}
}
