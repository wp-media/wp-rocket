<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\MaxCache\MaxCache;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\MaxCache\MaxCache;
use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\MaxCache\MaxCache::get_rules
 *
 * @group MaxCache
 */
class TestGetRules extends TestCase {
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
	 * Whether the plugin's exclusion list was asked for afresh.
	 *
	 * @var bool|null
	 */
	private $reject_uri_forced;

	/**
	 * How many times the plugin's dynamic-cookie list was asked for.
	 *
	 * @var int
	 */
	private $dynamic_cookie_reads = 0;

	/**
	 * Checks that one block is built from one reading of that list.
	 *
	 * The refusal check and the path template are both built from it, and a callback that answers
	 * differently the second time would have one block describe two configurations.
	 *
	 * @return void
	 */
	public function testShouldReadTheDynamicCookieListOnce() {
		$this->rules_for( [ 'dynamic_cookies' => [ 'device' ] ] );

		$this->assertSame( 1, $this->dynamic_cookie_reads );
	}

	/**
	 * Checks that the exclusions written are the ones the row holds now.
	 *
	 * That getter keeps its own static and only recomputes when it is told to, which is what the
	 * plugin's own config writer does — this block is written from the same request as that one.
	 *
	 * @return void
	 */
	public function testShouldAskForTheExclusionsTheRowHoldsNow() {
		$this->rules_for( [] );

		$this->assertTrue( $this->reject_uri_forced );
	}

	/**
	 * Checks the directives written for a given configuration.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Site and plugin state for the case.
	 * @param array $expected Directives that must and must not be present.
	 *
	 * @return void
	 */
	public function testShouldReturnExpectedRules( $config, $expected ) {
		$rules = $this->rules_for( $config );

		foreach ( $expected['contains'] as $directive ) {
			$this->assertStringContainsString( $directive, $rules );
		}

		foreach ( $expected['not_contains'] as $directive ) {
			$this->assertStringNotContainsString( $directive, $rules );
		}
	}

	/**
	 * Checks that the login entry taken out of the exclusions is the one the plugin puts in.
	 *
	 * Both sides spell that entry themselves, so this case lets the plugin's own builder answer and
	 * pins the two together: a change to its spelling fails here rather than quietly leaving logged-in
	 * visitors excluded from a cache the module was just told it can serve them.
	 *
	 * @return void
	 */
	public function testShouldTakeOutTheLoginEntryThePluginItselfWrites() {
		if ( ! defined( 'COOKIEHASH' ) ) {
			define( 'COOKIEHASH', 'abc123' );
		}

		if ( ! defined( 'LOGGED_IN_COOKIE' ) ) {
			define( 'LOGGED_IN_COOKIE', 'wordpress_logged_in_abc123' );
		}

		$rules = $this->rules_for(
			[
				'cache_logged_user' => 1,
				'logged_shared'     => true,
				// Answered by get_rocket_cache_reject_cookies() itself rather than by a stub.
				'reject_cookies'    => null,
			]
		);

		$this->assertStringNotContainsString( 'wordpress_logged_in', $rules );
		$this->assertStringContainsString( 'wp-postpass_', $rules );
	}

	/**
	 * Returns the directives this add-on writes for a given site.
	 *
	 * @param array $config Site and plugin state for the case.
	 *
	 * @return string
	 */
	private function rules_for( array $config ): string {
		$config = array_merge(
			[
				'maxcache'           => 1,
				'cache_ssl'          => 1,
				'cache_logged_user'  => 0,
				'secret_cache_key'   => 'abc123',
				'logged_shared'      => false,
				'mobile_cache'       => true,
				'mobile_files'       => false,
				'tablet'             => 'desktop',
				'reject_uri'         => '',
				'reject_ua'          => '',
				'reject_cookies'     => '',
				'query_strings'      => [],
				'ignored_parameters' => [],
				'gzip'               => false,
				'mode'               => 'apache',
				// What the plugin reads the server from: empty under WP-CLI and cron, so false there.
				'apache'             => true,
			],
			$config
		);

		Functions\when( '__' )->returnArg();
		Functions\when( 'is_multisite' )->justReturn( false );
		Functions\when( 'get_locale' )->justReturn( 'en_US' );
		Functions\when( 'home_url' )->justReturn( 'https://example.org' );
		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'wp_normalize_path' )->returnArg();
		Functions\when( 'is_ssl' )->justReturn( $config['is_ssl'] ?? false );
		Functions\when( 'sanitize_key' )->alias(
			function ( $key ) {
				return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
			}
		);
		Functions\when( 'get_option' )->justReturn( false );
		Functions\when( 'update_option' )->justReturn( true );
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
			function ( $name, $default = null ) {
				$constants = [
					'WP_ROCKET_CACHE_PATH' => '/var/www/html/wp-content/cache/wp-rocket/',
'ABSPATH'              => '/var/www/html/',
					'WP_CONTENT_DIR'       => '/var/www/html/wp-content',
					'LOGGED_IN_COOKIE'     => 'wordpress_logged_in_abc123',
					'COOKIEHASH'           => 'abc123',
				];

				return $constants[ $name ] ?? $default;
			}
		);
		Functions\when( 'is_rocket_generate_caching_mobile_files' )->justReturn( $config['mobile_files'] );
		Functions\when( 'is_rocket_cache_mobile' )->justReturn( $config['mobile_cache'] );
		Functions\when( 'get_rocket_cache_reject_uri' )->alias(
			function ( $force = false ) use ( $config ) {
				$this->reject_uri_forced = $force;

				return $config['reject_uri'];
			}
		);
		Functions\when( 'get_rocket_cache_reject_ua' )->justReturn( $config['reject_ua'] );
		if ( null !== $config['reject_cookies'] ) {
			Functions\when( 'get_rocket_cache_reject_cookies' )->justReturn( $config['reject_cookies'] );
		}
		Functions\when( 'get_rocket_cache_mandatory_cookies' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_dynamic_cookies' )->alias(
			function () use ( $config ) {
				++$this->dynamic_cookie_reads;

				return $config['dynamic_cookies'] ?? [];
			}
		);
		Functions\when( 'get_rocket_cache_query_string' )->justReturn( $config['query_strings'] );
		Functions\when( 'rocket_get_ignored_parameters' )->justReturn( $config['ignored_parameters'] );
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
					case 'rocket_force_gzip_htaccess_rules':
						return $config['gzip'];
					case 'rocket_url_no_dots':
						return false;
					case 'rocket_cache_mobile_files_tablet':
						return $config['tablet'];
					case 'rocket_common_cache_logged_users':
						return $config['logged_shared'];
					default:
						return $value;
				}
			}
		);

		$options_api = Mockery::mock( Options::class );
		// The settings row this case describes: the add-on reads it through the injected object.
		$options_api->shouldReceive( 'get' )->with( 'settings', [] )->andReturn( $config );
		$options_api->shouldReceive( 'get' )->andReturn( false );
		$options_api->shouldReceive( 'set' )->andReturn( true );

		$maxcache = new class( $options_api ) extends MaxCache {
			/**
			 * {@inheritdoc}
			 */
			protected function plugin_writes_cache_files(): bool {
				return true;
			}

		};

		$was_apache           = $GLOBALS['is_apache'] ?? null;
		$GLOBALS['is_apache'] = $config['apache'];

		$rules = $maxcache->get_rules();

		if ( null === $was_apache ) {
			unset( $GLOBALS['is_apache'] );
		} else {
			$GLOBALS['is_apache'] = $was_apache;
		}

		return $rules;
	}
}
