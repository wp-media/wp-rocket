<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Subscriber;

use Brain\Monkey\Filters;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::exclude_rocket_lazyload_excluded_src
 */
class TestExcludeRocketLazyloadExcludedSrc extends TestCase {
	use SubscriberTrait;

	public function set_up() {
		parent::set_up();
		$this->init_subscriber();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		Filters\expectApplied('rocket_lazyload_excluded_src')->with([])->andReturn($config['excluded_src']);

		$this->assertSame($expected, $this->subscriber->exclude_rocket_lazyload_excluded_src($config['excluded'], $config['urls']));
	}
}
