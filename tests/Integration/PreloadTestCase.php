<?php
namespace WP_Rocket\Tests\Integration;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Tests\Integration\Fixtures\Preload\Process_Wrapper;

class PreloadTestCase extends TestCase {
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

	/**
	 * @group Exclude
	 */
	public function testStopNowYouAreAnnoying() {
		// Prevent a warning. This should not run.
		$this->assertTrue( true );
	}
}
