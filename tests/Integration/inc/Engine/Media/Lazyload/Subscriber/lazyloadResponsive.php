<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\Subscriber::lazyload_responsive
 *
 * @group Media
 * @group Lazyload
 */
class Test_LazyloadResponsive extends TestCase {
	private $is_native;

	public function tear_down() : void {
		parent::tear_down();

		remove_filter( 'rocket_use_native_lazyload_images', [ $this, 'set_is_native' ] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $html, $expected ) {
		$this->is_native = $config['is_native'];

		add_filter( 'rocket_use_native_lazyload_images', [ $this, 'set_is_native' ] );

		$this->assertSame(
			$expected,
			apply_filters('rocket_lazyload_html', $html )
		);
	}

	public function set_is_native() {
		return $this->is_native;
	}
}
