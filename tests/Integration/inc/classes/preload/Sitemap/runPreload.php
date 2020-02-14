<?php
namespace WP_Rocket\Tests\Integration\inc\classes\preload\Sitemap;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Preload\Full_Process;
use WP_Rocket\Preload\Sitemap;

/**
 * Wrapper class used to test the results.
 */
class Process extends Full_Process {
	private $generatedKey;

	public function save() {
		$key = $this->generate_key();

		if ( ! empty( $this->data ) ) {
			update_site_option( $key, $this->data );
			$this->generatedKey = $key;
		} else {
			$this->generatedKey = null;
		}

		return $this;
	}

	public function getGeneratedKey() {
		return $this->generatedKey;
	}
}

/**
 * @covers \WP_Rocket\Preload\Sitemap::run_preload
 * @group Preload
 */
class Test_runPreload extends TestCase {
	protected $identifier         = 'rocket_preload';
	protected $option_hook_prefix = 'pre_get_rocket_option_';
	protected $homeWpOption;
	protected $preloadErrorsTransient;
	protected $preloadRunningTransient;
	protected $process;

	public function setUp() {
		parent::setUp();

		$this->homeWpOption            = get_option( 'home' );
		$this->preloadErrorsTransient  = get_transient( 'rocket_preload_errors' );
		$this->preloadRunningTransient = get_transient( 'rocket_preload_running' );
		$this->process                 = new Process();

		update_option( 'home', 'https://example.com/' );
	}

	public function tearDown() {
		parent::tearDown();

		if ( $this->process ) {
			// Added by \WP_Async_Request.
			remove_action( 'wp_ajax_' . $this->identifier, [ $this->process, 'maybe_handle' ] );
			remove_action( 'wp_ajax_nopriv_' . $this->identifier, [ $this->process, 'maybe_handle' ] );
			// Added by \WP_Background_Process.
			remove_action( $this->identifier . '_cron', [ $this->process, 'handle_cron_healthcheck' ] );
			remove_filter( 'cron_schedules', [ $this->process, 'schedule_cron_healthcheck' ] );
		}

		foreach ( [ 'manual_preload', 'cache_mobile', 'do_caching_mobile_files' ] as $option ) {
			remove_filter( $this->option_hook_prefix . $option, [ $this, 'return_0' ] );
			remove_filter( $this->option_hook_prefix . $option, [ $this, 'return_1' ] );
		}

		remove_filter( $this->option_hook_prefix . 'cache_reject_uri', [ $this, 'return_empty_array' ] );
		remove_filter( $this->option_hook_prefix . 'cache_query_strings', [ $this, 'return_cache_query_strings' ] );

		update_option( 'home', $this->homeWpOption );
		set_transient( 'rocket_preload_errors', $this->preloadErrorsTransient );
		set_transient( 'rocket_preload_running', $this->preloadRunningTransient );

		$this->homeWpOption            = null;
		$this->preloadErrorsTransient  = null;
		$this->preloadRunningTransient = null;
		$this->process                 = null;
	}

	public function testShouldNotPreloadWhenNoUrls() {
		$preload_process = $this->createMock( Full_Process::class );

		Actions\expectDone( 'before_run_rocket_sitemap_preload' )->never();

		// No URLs.
		( new Sitemap( $preload_process ) )->run_preload( [] );
	}

	public function testShouldPreloadSitemapsWhenValidUrls() {
		$sitemaps = [
			'https://example.com/sitemap.xml',
			'https://example.com/sitemap-mobile.xml',
		];

		// Fake the requests.
		Functions\when( 'wp_remote_get' )->alias( function( $url, $args = [] ) {
			$path = WP_ROCKET_TESTS_FIXTURES_DIR . '/Preload/Sitemap/';

			switch ( $url ) {
				case 'https://example.com/sitemap.xml':
					$path .= 'sitemap.xml';
					break;
				case 'https://example.com/sitemap-mobile.xml':
					$path .= 'sitemap-mobile.xml';
					break;
				default:
					return new \WP_Error( 'wrong-url', 'Wrong URL', [ $url ] );
			}

			if ( ! \file_exists( $path ) ) {
				return new \WP_Error( 'file-not-found', 'File not found', [ basename( $path ) ] );
			}

			return [
				'headers'       => null, // Requests_Utility_CaseInsensitiveDictionary object.
				'body'          => \file_get_contents( $path ),
				'response'      => [
					'code'    => 200,
					'message' => 'OK',
				],
				'cookies'       => [],
				'filename'      => '',
				'http_response' => null, // WP_HTTP_Requests_Response object.
			];
		} );

		add_filter( $this->option_hook_prefix . 'manual_preload', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'cache_mobile', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'do_caching_mobile_files', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'cache_reject_uri', [ $this, 'return_empty_array' ] );

		delete_transient( 'rocket_preload_errors' );

		( new Sitemap( $this->process ) )->run_preload( $sitemaps );

		$key = $this->process->getGeneratedKey();

		$this->assertNotNull( $key );

		$queue = get_site_option( $key );
		delete_site_option( $key );

		$this->assertContains( 'https://example.com/', $queue );
		$this->assertContains( 'https://example.com/fr/', $queue );
		$this->assertContains( 'https://example.com/es/', $queue );
		$this->assertContains( [ 'url' => 'https://example.com/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/fr/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/es/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/mobile/de/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/mobile/fr/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/mobile/es/', 'mobile' => true ], $queue );
		$this->assertCount( 9, $queue );
	}

	public function testShouldPreloadFallbackUrlsWhenInvalidSitemap() {
		$sitemaps = [
			'https://example.com/sitemap.xml',
			'https://example.com/sitemap-mobile.xml',
		];

		$post_id = wp_insert_post(
			[
				'post_title'   => 'Hoy',
				'post_content' => 'Hello World',
				'post_type'    => 'page',
				'post_status'  => 'publish',
			]
		);

		if ( method_exists( $this, 'assertIsInt' ) ) {
			$this->assertIsInt( $post_id );
		} else {
			// Deprecated in phpunit 8.
			$this->assertInternalType( 'int', $post_id );
		}

		$permalink = get_permalink( $post_id );

		$this->assertNotFalse( $permalink );

		// Fake the requests.
		Functions\when( 'wp_remote_get' )->alias( function( $url, $args = [] ) use ( $permalink ) {
			$path = WP_ROCKET_TESTS_FIXTURES_DIR . '/Preload/Sitemap/';

			switch ( $url ) {
				case 'https://example.com/sitemap.xml':
					$path .= 'sitemap.xml';
					break;
				// sitemap-mobile.xml will return an error, and will trigger get_urls().
				default:
					return new \WP_Error( 'wrong-url', 'Wrong URL', [ $url ] );
			}

			if ( ! \file_exists( $path ) ) {
				return new \WP_Error( 'file-not-found', 'File not found', [ basename( $path ) ] );
			}

			return [
				'headers'       => null, // Requests_Utility_CaseInsensitiveDictionary object.
				'body'          => \file_get_contents( $path ),
				'response'      => [
					'code'    => 200,
					'message' => 'OK',
				],
				'cookies'       => [],
				'filename'      => '',
				'http_response' => null, // WP_HTTP_Requests_Response object.
			];
		} );

		add_filter( $this->option_hook_prefix . 'manual_preload', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'cache_mobile', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'do_caching_mobile_files', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'cache_reject_uri', [ $this, 'return_empty_array' ] );
		add_filter( $this->option_hook_prefix . 'cache_query_strings', [ $this, 'return_cache_query_strings' ] ); // For our newly created page.

		delete_transient( 'rocket_preload_errors' );

		( new Sitemap( $this->process ) )->run_preload( $sitemaps );

		$key = $this->process->getGeneratedKey();

		$this->assertNotNull( $key );

		$queue = get_site_option( $key );
		delete_site_option( $key );

		$this->assertContains( 'https://example.com/', $queue );
		$this->assertContains( 'https://example.com/fr/', $queue );
		$this->assertContains( 'https://example.com/es/', $queue );
		$this->assertContains( $permalink, $queue );
		$this->assertContains( [ 'url' => 'https://example.com/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/fr/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/es/', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => $permalink, 'mobile' => true ], $queue );
		$this->assertCount( 8, $queue );

		wp_delete_post( $post_id, true );
	}

	public function return_0() {
		return 0;
	}

	public function return_1() {
		return 1;
	}

	public function return_empty_array() {
		return [];
	}

	public function return_cache_query_strings() {
		return [ 'page_id' ];
	}
}
