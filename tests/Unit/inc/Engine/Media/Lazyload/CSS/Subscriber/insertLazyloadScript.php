<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Subscriber;

use Engine\Media\Lazyload\CSS\Subscriber\SubscriberTrait;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::insert_lazyload_script
 */
class Test_insertLazyloadScript extends TestCase {

	use SubscriberTrait;

	public function set_up() {
		$this->init_subscriber();
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		Functions\when('rocket_get_constant')->alias(function ($name) use ($config) {
			if('WP_ROCKET_VERSION' === $name) {
				return $config['WP_ROCKET_VERSION'];
			}
			if('WP_ROCKET_ASSETS_JS_URL' === $name) {
				return $config['WP_ROCKET_ASSETS_JS_URL'];
			}

			if('WP_ROCKET_ASSETS_JS_PATH' === $name) {
				return $config['WP_ROCKET_ASSETS_JS_PATH'];
			}

			return null;
		});




		$this->context->expects()->is_allowed()->andReturn($config['is_allowed']);

		if($config['is_allowed']) {

			$this->filesystem->expects()->exists($expected['path'])->andReturn($config['exists']);

			$this->filesystem->expects()->get_contents($expected['path'])->andReturn($config['script_data']);
			Filters\expectApplied('rocket_lazyload_threshold')->with(300)->andReturn($config['threshold']);
			Functions\expect('wp_register_script')->with('rocket_lazyload_css', '', [], false, true);
			Functions\expect('wp_enqueue_script')->with('rocket_lazyload_css');
			Functions\expect('wp_add_inline_script')->with('rocket_lazyload_css', $expected['script_data'], 'after');
			Functions\expect('wp_localize_script')->with('rocket_lazyload_css', 'rocket_lazyload_css_data', $expected['data']);
		}

		$this->subscriber->insert_lazyload_script();
    }
}
