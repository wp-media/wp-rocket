<?php
namespace WP_Rocket\Tests\Integration\inc\classes\preload\Homepage;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Tests\Integration\Fixtures\Preload\Process_Wrapper;
use WP_Rocket\Preload\Homepage;

/**
 * @covers \WP_Rocket\Preload\Homepage::preload
 * @group Preload
 */
class Test_Preload extends TestCase {
	protected $identifier         = 'rocket_preload';
	protected $option_hook_prefix = 'pre_get_rocket_option_';
	protected $homeWpOption;
	protected $preloadErrorsTransient;
	protected $preloadRunningTransient;
	protected $process;

	public static function setUpBeforeClass() {
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/Preload/Process_Wrapper.php';
	}

	public function setUp() {
		parent::setUp();

		$this->homeWpOption            = get_option( 'home' );
		$this->preloadErrorsTransient  = get_transient( 'rocket_preload_errors' );
		$this->preloadRunningTransient = get_transient( 'rocket_preload_running' );
		$this->process                 = new Process_Wrapper();

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

		update_option( 'home', $this->homeWpOption );
		set_transient( 'rocket_preload_errors', $this->preloadErrorsTransient );
		set_transient( 'rocket_preload_running', $this->preloadRunningTransient );

		$this->homeWpOption            = null;
		$this->preloadErrorsTransient  = null;
		$this->preloadRunningTransient = null;
		$this->process                 = null;
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
			$path       = WP_ROCKET_TESTS_FIXTURES_DIR . '/Preload/Homepage/' . $file_name . $mobile_sub . '.html';

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

		add_filter( $this->option_hook_prefix . 'manual_preload', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'cache_mobile', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'do_caching_mobile_files', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'cache_reject_uri', [ $this, 'return_empty_array' ] );

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

	public function return_0() {
		return 0;
	}

	public function return_1() {
		return 1;
	}

	public function return_empty_array() {
		return [];
	}
}
