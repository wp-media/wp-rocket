<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\Subscriber::maybe_disable_core_lazyload
 *
 * @group Media
 * @group Lazyload
 */
class Test_MaybeDisableCoreLazyload extends TestCase {
	private $lazyload;
	private $lazyload_iframes;
	private $lazyload_filter;
	private $lazyload_iframes_filter;

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_lazyload', [ $this, 'set_lazyload'] );
		remove_filter( 'pre_get_rocket_option_lazyload_iframes', [ $this, 'set_lazyload_iframes'] );
		remove_filter( 'do_rocket_lazyload', [ $this, 'set_lazyload_filter'] );
		remove_filter( 'do_rocket_lazyload_iframes', [ $this, 'set_lazyload_filter_iframes'] );

		parent::tear_down();
	}
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->lazyload = $config['lazyload'];
		$this->lazyload_iframes = $config['lazyload_iframes'];
		$this->lazyload_filter = $config['lazyload_filter'];
		$this->lazyload_iframes_filter = $config['lazyload_iframes_filter'];

		add_filter( 'pre_get_rocket_option_lazyload', [ $this, 'set_lazyload'] );
		add_filter( 'pre_get_rocket_option_lazyload_iframes', [ $this, 'set_lazyload_iframes'] );
		add_filter( 'do_rocket_lazyload', [ $this, 'set_lazyload_filter'] );
		add_filter( 'do_rocket_lazyload_iframes', [ $this, 'set_lazyload_filter_iframes'] );

		$this->assertSame(
			$expected,
			apply_filters( 'wp_lazy_loading_enabled', $config['value'], $config['tag_name'] )
		);
	}

	public function set_lazyload() {
		return $this->lazyload;
	}

	public function set_lazyload_iframes() {
		return $this->lazyload_iframes;
	}

	public function set_lazyload_filter() {
		return $this->lazyload_filter;
	}

	public function set_lazyload_filter_iframes() {
		return $this->lazyload_iframes_filter;
	}
}
