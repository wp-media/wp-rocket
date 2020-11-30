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
