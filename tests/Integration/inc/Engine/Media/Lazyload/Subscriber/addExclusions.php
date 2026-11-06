<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\Subscriber::add_exclusions
 *
 * @group Media
 * @group Lazyload
 */
class Test_AddExclusions extends TestCase {
	private $options;

	public function set_up() {
		parent::set_up();
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_exclude_lazyload', [ $this, 'set_option_exclusions' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->options = $config['option'];

		add_filter( 'pre_get_rocket_option_exclude_lazyload', [ $this, 'set_option_exclusions' ] );

		$this->assertSame(
			array_values( $expected ),
			array_values( apply_filters( $config['filter'], $config['exclusions'] ) )
		);
	}

	public function set_option_exclusions() {
		return $this->options;
	}
}
