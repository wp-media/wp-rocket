<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\Subscriber::maybe_add_skip_attributes
 *
 * @group Media
 * @group Lazyload
 */
class Test_maybeAddSkipAttributes extends TestCase {
	private $is_native;

	public function tearDown() : void {
		parent::tearDown();

		remove_filter( 'rocket_use_native_lazyload', [ $this, 'set_is_native' ] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->is_native = $config['is_native'];

		add_filter( 'rocket_use_native_lazyload', [ $this, 'set_is_native' ] );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_lazyload_excluded_attributes', $config['exclusions'] )
		);
	}

	public function set_is_native() {
		return $this->is_native;
	}
}
