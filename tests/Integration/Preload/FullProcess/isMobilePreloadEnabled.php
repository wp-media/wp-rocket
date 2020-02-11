<?php
namespace WP_Rocket\Tests\Integration\Preload\FullProcess;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Preload\Full_Process;

/**
 * @covers \WP_Rocket\Preload\Full_Process::is_mobile_preload_enabled
 * @group Preload
 */
class Test_isMobilePreloadEnabled extends TestCase {
	protected $identifier = 'rocket_preload';
	protected $manualPreloadOption;
	protected $cacheMobileOption;
	protected $doCachingMobileFilesOption;
	protected $process;

	public function setUp() {
		parent::setUp();

		$this->manualPreloadOption        = get_rocket_option( 'manual_preload' );
		$this->cacheMobileOption          = get_rocket_option( 'cache_mobile' );
		$this->doCachingMobileFilesOption = get_rocket_option( 'do_caching_mobile_files' );
		$this->process                    = new Full_Process();
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

		$this->manualPreloadOption        = null;
		$this->cacheMobileOption          = null;
		$this->doCachingMobileFilesOption = null;
		$this->process                    = null;
	}

	public function testShouldReturnTrueWhenOptionsEnabled() {
		update_rocket_option( 'manual_preload', 1 );
		update_rocket_option( 'cache_mobile', 1 );
		update_rocket_option( 'do_caching_mobile_files', 1 );

		$this->assertTrue( $this->process->is_mobile_preload_enabled() );
	}

	public function testShouldReturnFalseWhenOptionsDisabled() {
		update_rocket_option( 'manual_preload', 0 );
		update_rocket_option( 'cache_mobile', 1 );
		update_rocket_option( 'do_caching_mobile_files', 1 );

		$this->assertFalse( $this->process->is_mobile_preload_enabled() );

		update_rocket_option( 'manual_preload', 1 );
		update_rocket_option( 'cache_mobile', 0 );
		update_rocket_option( 'do_caching_mobile_files', 1 );

		$this->assertFalse( $this->process->is_mobile_preload_enabled() );

		update_rocket_option( 'manual_preload', 1 );
		update_rocket_option( 'cache_mobile', 1 );
		update_rocket_option( 'do_caching_mobile_files', 0 );

		$this->assertFalse( $this->process->is_mobile_preload_enabled() );
	}

	public function testShouldReturnBooleanWhenFiltered() {
		update_rocket_option( 'manual_preload', 0 );
		update_rocket_option( 'cache_mobile', 1 );
		update_rocket_option( 'do_caching_mobile_files', 1 );

		add_filter( 'rocket_mobile_preload_enabled', [ $this, 'mobilePreloadEnabledFilter' ], 123 );

		$this->assertTrue( $this->process->is_mobile_preload_enabled() );

		remove_filter( 'rocket_mobile_preload_enabled', [ $this, 'mobilePreloadEnabledFilter' ], 123 );

		update_rocket_option( 'manual_preload', 1 );
		update_rocket_option( 'cache_mobile', 1 );
		update_rocket_option( 'do_caching_mobile_files', 1 );

		add_filter( 'rocket_mobile_preload_enabled', '__return_empty_string', 123 );

		$this->assertFalse( $this->process->is_mobile_preload_enabled() );

		remove_filter( 'rocket_mobile_preload_enabled', '__return_empty_string', 123 );
	}

	public function mobilePreloadEnabledFilter() {
		return 'foobar';
	}
}
