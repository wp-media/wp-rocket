<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Subscriber;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::clear_generated_css
 */
class TestClearGeneratedCss extends TestCase {
	use SubscriberTrait;

	public function set_up() {
		$this->init_subscriber();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config ) {
		$this->filesystem_cache->expects()->clear();
		$this->subscriber->clear_generated_css();
	}
}
