<?php
namespace WP_Rocket\Tests\Integration\inc\classes\preload\Full_Process;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Preload\Full_Process;

/**
 * @covers \WP_Rocket\Preload\Full_Process::is_mobile_preload_enabled
 * @group Preload
 */
class Test_isMobilePreloadEnabled extends TestCase {
	protected $identifier         = 'rocket_preload';
	protected $option_hook_prefix = 'pre_get_rocket_option_';
	protected $process;

	public function setUp() {
		parent::setUp();

		$this->process = new Full_Process();
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
	}

	public function testShouldReturnTrueWhenOptionsEnabled() {
		add_filter( $this->option_hook_prefix . 'manual_preload', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'cache_mobile', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'do_caching_mobile_files', [ $this, 'return_1' ] );

		$this->assertTrue( $this->process->is_mobile_preload_enabled() );
	}

	public function testShouldReturnFalseWhenOptionsDisabled() {
		add_filter( $this->option_hook_prefix . 'manual_preload', [ $this, 'return_0' ] );
		add_filter( $this->option_hook_prefix . 'cache_mobile', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'do_caching_mobile_files', [ $this, 'return_1' ] );

		$this->assertFalse( $this->process->is_mobile_preload_enabled() );

		add_filter( $this->option_hook_prefix . 'manual_preload', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'cache_mobile', [ $this, 'return_0' ] );

		$this->assertFalse( $this->process->is_mobile_preload_enabled() );

		add_filter( $this->option_hook_prefix . 'cache_mobile', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'do_caching_mobile_files', [ $this, 'return_0' ] );

		$this->assertFalse( $this->process->is_mobile_preload_enabled() );
	}

	public function testShouldReturnBooleanWhenFiltered() {
		add_filter( 'rocket_mobile_preload_enabled', [ $this, 'mobilePreloadEnabledFilter' ] );
		add_filter( $this->option_hook_prefix . 'manual_preload', [ $this, 'return_0' ] );
		add_filter( $this->option_hook_prefix . 'cache_mobile', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'do_caching_mobile_files', [ $this, 'return_1' ] );

		$this->assertTrue( $this->process->is_mobile_preload_enabled() );

		remove_filter( 'rocket_mobile_preload_enabled', [ $this, 'mobilePreloadEnabledFilter' ] );
		add_filter( 'rocket_mobile_preload_enabled', [ $this, 'return_0' ] );
		add_filter( $this->option_hook_prefix . 'manual_preload', [ $this, 'return_1' ] );

		$this->assertFalse( $this->process->is_mobile_preload_enabled() );

		remove_filter( 'rocket_mobile_preload_enabled', [ $this, 'return_0' ] );
	}

	public function mobilePreloadEnabledFilter() {
		return 'foobar';
	}

	public function return_0() {
		return 0;
	}

	public function return_1() {
		return 1;
	}
}
