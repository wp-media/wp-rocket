<?php
namespace WP_Rocket\Tests\Integration\inc\classes\preload\Homepage;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Tests\Integration\Fixtures\Preload\Process_Wrapper;
use WP_Rocket\Preload\Homepage;

/**
 * @covers \WP_Rocket\Preload\Homepage::preload
 * @group Preload
 */
class Test_Preload extends TestCase {
	protected $site_url           = 'https://smashingcoding.com';
	protected $identifier         = 'rocket_preload';
	protected $option_hook_prefix = 'pre_get_rocket_option_';
	protected $preloadErrorsTransient;
	protected $preloadRunningTransient;
	protected $process;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/Preload/Process_Wrapper.php';
	}

	public function setUp() {
		parent::setUp();

		add_filter( 'site_url', [ $this, 'setSiteUrl' ] );

		$this->preloadErrorsTransient  = get_transient( 'rocket_preload_errors' );
		$this->preloadRunningTransient = get_transient( 'rocket_preload_running' );
		$this->process                 = new Process_Wrapper();
	}

	public function setSiteUrl() {
		return $this->site_url;
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'site_url', [ $this, 'setSiteUrl' ] );

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

		set_transient( 'rocket_preload_errors', $this->preloadErrorsTransient );
		set_transient( 'rocket_preload_running', $this->preloadRunningTransient );

		$this->preloadErrorsTransient  = null;
		$this->preloadRunningTransient = null;
		$this->process                 = null;
	}

	public function testShouldPreloadWhenValidUrls() {
		$home_urls = [
			[ 'url' => "{$this->site_url}/mobile-preload-homepage/" ],
			[ 'url' => "{$this->site_url}/2020/02/18/mobile-preload-post-tester/" ],
			[ 'url' => "{$this->site_url}/category/mobile-preload/", 'mobile' => 1 ],
		];

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

		// This one is excluded when requesting "{$this->site_url}/mobile-preload-homepage/", but included when requesting "{$this->site_url}/2020/02/18/mobile-preload-post-tester/" and "{$this->site_url}/category/mobile-preload/".
		$this->assertContains( "{$this->site_url}/mobile-preload-homepage/", $queue );
		$this->assertContains( "{$this->site_url}/mobile-preload-homepage/fr", $queue );
		$this->assertContains( "{$this->site_url}/mobile-preload-homepage/es", $queue );
		$this->assertContains( [ 'url' => "{$this->site_url}/mobile-preload-homepage/", 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => "{$this->site_url}/mobile-preload-homepage/fr", 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => "{$this->site_url}/mobile-preload-homepage/es", 'mobile' => true ], $queue );
		$this->assertNotContains( 'https://toto.org', $queue );
	}
}
