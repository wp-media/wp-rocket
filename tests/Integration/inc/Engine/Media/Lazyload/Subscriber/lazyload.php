<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\Subscriber::lazyload
 *
 * @uses ::rocket_get_constant
 *
 * @group Lazyload
 */
class Test_Lazyload extends TestCase {
	private $lazyload;
	private $iframes;

	public function set_up() {
		parent::set_up();

		$this->lazyload = null;
		$this->iframes  = null;

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'lazyload', 18 );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_lazyload', [ $this, 'setLazyload' ] );
		remove_filter( 'pre_get_rocket_option_lazyload_iframes', [ $this, 'setIframes' ] );
		remove_filter( 'rocket_use_native_lazyload_images', [ $this, 'return_false' ] );
		remove_filter( 'rocket_use_native_lazyload_images', [ $this, 'return_true' ] );

		global $wp_query;
		$wp_query->is_feed    = false;
		$wp_query->is_preview = false;
		$wp_query->is_search  = false;

		$this->restoreWpHook( 'rocket_buffer' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $html, $expected ) {
		$this->lazyload = $config['options']['lazyload'];
		$this->iframes  = $config['options']['lazyload_iframes'];

		set_current_screen( $config['is_admin'] ? 'settings_page_wprocket' : 'front' );

		global $wp_query;
		$wp_query->is_feed    = $config['is_feed'];
		$wp_query->is_preview = $config['is_preview'];
		$wp_query->is_search  = $config['is_search'];

		//Constants.
		$this->rest_request  = $config['is_rest_request'];
		$this->constants['DONOTLAZYLOAD'] = $config['is_not_lazy_load'];
		$this->donotrocketoptimize        = $config['is_rocket_optimize'];

		add_filter( 'pre_get_rocket_option_lazyload', [ $this, 'setLazyload' ] );
		add_filter( 'pre_get_rocket_option_lazyload_iframes', [ $this, 'setIframes' ] );

		if ( $config['is_native'] ) {
			add_filter( 'rocket_use_native_lazyload_images', [ $this, 'return_true' ] );
		} else {
			add_filter( 'rocket_use_native_lazyload_images', [ $this, 'return_false' ] );
		}

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( apply_filters( 'rocket_buffer', $html['original'] ) )
		);
	}

	public function setLazyload() {
		return $this->lazyload;
	}

	public function setIframes() {
		return $this->iframes;
	}
}
