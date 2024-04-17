<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\AdminSubscriber;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Settings\Settings;
use WP_Rocket\Engine\Media\Lazyload\AdminSubscriber;
use WP_Rocket\Tests\Integration\TestCase;
use Mockery;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\AdminSubscriber::sanitize_exclude_lazyload
 *
 * @group AdminOnly
 * @group Media
 * @group Lazyload
 */
class Test_SanitizeExcludeLazyload extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_input_sanitize', 'sanitize_exclude_lazyload' );
	}

	public function tear_down() {
		parent::tear_down();

		$this->restoreWpHook( 'rocket_input_sanitize' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $input, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_input_sanitize', $input, new Settings( Mockery::mock( Options_Data::class ) ) )
		);
	}
}
