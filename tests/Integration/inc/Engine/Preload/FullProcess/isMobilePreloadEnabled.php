<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\FullProcess;

use WP_Rocket\Tests\Integration\inc\Engine\Preload\PreloadTestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\FullProcess::is_mobile_preload_enabled
 * @uses   ::get_rocket_option
 * @group  Preload
 */
class Test_IsMobilePreloadEnabled extends PreloadTestCase {

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
}
