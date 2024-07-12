<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Subscriber;

use Brain\Monkey\Filters;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::maybe_replace_css_images
 */
class TestMaybeReplaceCssImages extends TestCase {
	use SubscriberTrait;

	public function set_up() {
		$this->init_subscriber();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		if($config['is_allowed']) {
			Filters\expectApplied('rocket_generate_lazyloaded_css')->with($expected['data'])->andReturn($config['data']);
		}

		$this->context->shouldReceive('is_allowed')->andReturn($config['is_allowed']);
		$this->assertSame($expected['output'], $this->subscriber->maybe_replace_css_images($config['html']));
	}
}
