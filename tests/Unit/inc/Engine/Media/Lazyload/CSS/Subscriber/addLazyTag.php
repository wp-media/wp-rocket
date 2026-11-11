<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Subscriber;

use Brain\Monkey\Filters;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::add_lazy_tag
 */
class TestAddLazyTag extends TestCase {
	use SubscriberTrait;

	public function set_up() {
		$this->init_subscriber();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->configureProcess( $config, $expected );
		$this->assertSame(
			$expected['output'],
			$this->subscriber->add_lazy_tag( $config['data'] )
		);
	}

	protected function configureProcess( $config, $expected ) {
		if(! key_exists('html', $config['data']) || ! key_exists('lazyloaded_images', $config['data'])) {
			return;
		}
		Filters\expectApplied('rocket_css_image_lazyload_images_load')->with([])->andReturn($config['load_filtered']);

		$this->tag_generator->expects()->generate($expected['lazyloaded_images'], $expected['loaded'])->andReturn($config['tags']);

	}
}
