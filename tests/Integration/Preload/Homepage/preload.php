<?php
namespace WP_Rocket\Tests\Integration\Preload\Homepage;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

use WP_Rocket\Preload\Full_Process;
use WP_Rocket\Preload\Homepage;

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
 * @covers \WP_Rocket\Preload\Homepage::preload
 * @group Preload
 */
class Test_preload extends TestCase {
	protected $identifier = 'rocket_preload';
	protected $manualPreloadOption;
	protected $cacheMobileOption;
	protected $doCachingMobileFilesOption;
	protected $cacheRejectUriOption;
	protected $cacheQueryStringsOptions;
	protected $homeWpOption;
	protected $preloadErrorsTransient;
	protected $process;

	public function setUp() {
		parent::setUp();

		$this->manualPreloadOption        = get_rocket_option( 'manual_preload' );
		$this->cacheMobileOption          = get_rocket_option( 'cache_mobile' );
		$this->doCachingMobileFilesOption = get_rocket_option( 'do_caching_mobile_files' );
		$this->cacheRejectUriOption       = get_rocket_option( 'cache_reject_uri' );
		$this->cacheQueryStringsOption    = get_rocket_option( 'cache_query_strings' );
		$this->homeWpOption               = get_option( 'home' );
		$this->preloadErrorsTransient     = get_transient( 'rocket_preload_errors' );
		$this->preloadRunningTransient    = get_transient( 'rocket_preload_running' );
		$this->process                    = new Process();

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

		update_rocket_option( 'manual_preload', $this->manualPreloadOption );
		update_rocket_option( 'cache_mobile', $this->cacheMobileOption );
		update_rocket_option( 'do_caching_mobile_files', $this->doCachingMobileFilesOption );
		update_rocket_option( 'cache_reject_uri', $this->cacheRejectUriOption );
		update_rocket_option( 'cache_query_strings', $this->cacheQueryStringsOption );
		update_option( 'home', $this->homeWpOption );
		set_transient( 'rocket_preload_errors', $this->preloadErrorsTransient );
		set_transient( 'rocket_preload_running', $this->preloadRunningTransient );

		$this->manualPreloadOption        = null;
		$this->cacheMobileOption          = null;
		$this->doCachingMobileFilesOption = null;
		$this->cacheRejectUriOption       = null;
		$this->cacheQueryStringsOption    = null;
		$this->homeWpOption               = null;
		$this->preloadErrorsTransient     = null;
		$this->preloadRunningTransient    = null;
		$this->process                    = null;
	}

	public function testShouldPreloadWhenValidUrls() {
		$home_urls = [
			[ 'url' => 'https://example.com/' ],
			[ 'url' => 'https://example.com/foobar/' ],
			[ 'url' => 'https://example.com/category/barbaz/', 'mobile' => 1 ],
		];

		// Fake the requests.
		Functions\when( 'wp_remote_get' )->alias( function( $url, $args = [] ) {
			switch ( \trailingslashit( $url ) ) {
				case 'https://example.com/':
					$file_name = 'home';
					break;
				case 'https://example.com/foobar/':
					$file_name = 'foobar';
					break;
				case 'https://example.com/category/barbaz/':
					$file_name = 'category-barbaz';
					break;
				default:
					return new \WP_Error( 'wrong-url', 'Wrong URL', [ $url ] );
			}

			$mobile_sub = ! empty( $args['user-agent'] ) && strpos( $args['user-agent'], 'iPhone' ) ? '-mobile' : '';
			$path       = WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/Preload/Homepage/' . $file_name . $mobile_sub . '.html';

			if ( ! \file_exists( $path ) ) {
				return new \WP_Error( 'file-not-found', 'File not found', [ $file_name . $mobile_sub . '.html' ] );
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

		update_rocket_option( 'manual_preload', 1 );
		update_rocket_option( 'cache_mobile', 1 );
		update_rocket_option( 'do_caching_mobile_files', 1 );
		update_rocket_option( 'cache_reject_uri', [] );
		delete_transient( 'rocket_preload_errors' );

		( new Homepage( $this->process ) )->preload( $home_urls );

		$key = $this->process->getGeneratedKey();

		$this->assertNotNull( $key );

		$queue = get_site_option( $key );
		delete_site_option( $key );

		$this->assertContains( 'https://example.com/fr', $queue );
		$this->assertContains( 'https://example.com/es', $queue );
		$this->assertContains( 'https://example.com/de', $queue );
		$this->assertContains( [ 'url' => 'https://example.com/mobile', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/mobile/fr', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/mobile/es', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/mobile/de', 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => 'https://example.com/mobile/it', 'mobile' => true ], $queue );
		$this->assertCount( 8, $queue );
	}
}
