<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\MaxCache\MaxCache;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\MaxCache\MaxCache;
use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\MaxCache\MaxCache::is_htaccess_needed
 *
 * @group MaxCache
 */
class TestIsHtaccessNeeded extends TestCase {
	/**
	 * What $_SERVER['DOCUMENT_ROOT'] held before this class touched it.
	 *
	 * @var array
	 */
	private $server = [];

	public function set_up() {
		parent::set_up();

		$this->server = $_SERVER;
	}

	public function tear_down() {
		$_SERVER = $this->server;

		parent::tear_down();
	}

	/**
	 * Checks who has to keep the file, for a given state.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Server mode, option, whether the module would answer, file contents
	 *                        and call arguments.
	 * @param bool  $expected Expected answer.
	 *
	 * @return void
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$config = array_merge(
			[
				'needed'        => false,
				'removing'      => false,
				'mode'          => 'nginx',
				'option'        => 0,
				'file'          => '',
				// The module answers where the switch is on, unless a case says otherwise.
				'enabled'       => null,
				'writable'      => true,
				'document_root' => '/var/www/html',
				'cache_path'    => '/var/www/html/wp-content/cache/wp-rocket/',
				// What the request says about the server. Absent is what WP-CLI and cron look like.
				'server_software' => 'nginx/1.24.0',
			],
			$config
		);

		if ( null === $config['server_software'] ) {
			unset( $_SERVER['SERVER_SOFTWARE'] );
		} else {
			$_SERVER['SERVER_SOFTWARE'] = $config['server_software'];
		}

		if ( null === $config['document_root'] ) {
			unset( $_SERVER['DOCUMENT_ROOT'] );
		} else {
			$_SERVER['DOCUMENT_ROOT'] = $config['document_root'];
		}

		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'rocket_get_constant' )->alias(
			function ( $name ) use ( $config ) {
				return 'WP_ROCKET_CACHE_PATH' === $name ? $config['cache_path'] : '/var/www/html/';
			}
		);

		$filesystem = Mockery::mock( 'WP_Filesystem_Direct' );
		// A file that is there and cannot be read is a state of its own: exists, contents false.
		$filesystem->shouldReceive( 'exists' )->andReturn( '' !== $config['file'] || ! empty( $config['unreadable'] ) );
		// The read is kept only while the file it came from is the same file.
		$filesystem->shouldReceive( 'mtime' )->andReturn( 1 );
		$filesystem->shouldReceive( 'size' )->andReturn( 1 );
		$filesystem->shouldReceive( 'get_contents' )->andReturn( empty( $config['unreadable'] ) ? $config['file'] : false );
		$filesystem->shouldReceive( 'is_writable' )->andReturn( $config['writable'] );

		Functions\when( 'get_home_path' )->justReturn( $config['home_path'] ?? '/var/www/html/' );
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $filesystem );
		Functions\when( 'get_rocket_cache_reject_uri' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_reject_ua' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_reject_cookies' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_mandatory_cookies' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_query_string' )->justReturn( [] );
		Functions\when( 'rocket_get_ignored_parameters' )->justReturn( [] );
		// wpm_apply_filters_typed() is left alone: it is a real function that calls apply_filters(),
		// which is what the stub below replaces.
		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) use ( $config ) {
				return 'rocket_maxcache_mode' === $tag ? $config['mode'] : $value;
			}
		);

		$options_api = Mockery::mock( Options::class );
		// The switch has an answer stored on this site unless the case says it never had one: what
		// was never answered never had directives written for it.
		$options_api->shouldReceive( 'get' )
			->with( 'settings', [] )
			->andReturn( array_key_exists( 'decided', $config ) && ! $config['decided'] ? [] : [ 'maxcache' => $config['option'] ] );
		$options_api->shouldReceive( 'get' )->andReturn( false );

		/*
		 * The subject is which caller has to keep the file; whether the module would answer at all is
		 * a question of its own, given here rather than driven through every option it reads. A
		 * subclass rather than a mock: open_socket() takes its error by reference, which a mock's
		 * proxying cannot pass on.
		 */
		$enabled = null === $config['enabled'] ? (bool) $config['option'] : $config['enabled'];

		$maxcache = new class( $options_api, $enabled ) extends MaxCache {
			/**
			 * Whether the module would answer.
			 *
			 * @var bool
			 */
			private $enabled;

			public function __construct( $options_api, $enabled ) {
				parent::__construct( $options_api );

				$this->enabled = $enabled;
			}

			public function is_enabled(): bool {
				return $this->enabled;
			}
		};

		$this->assertSame( $expected, $maxcache->is_htaccess_needed( $config['needed'], $config['removing'] ) );
	}
}
