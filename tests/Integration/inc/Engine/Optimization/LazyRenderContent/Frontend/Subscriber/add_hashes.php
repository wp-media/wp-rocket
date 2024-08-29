<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\LazyRenderContent\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\inc\Engine\Optimization\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::rewrite
 *
 * @uses \WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Subscriber::add_hashes()
 * @group PerformanceHints
 */
class Test_add_hashes extends TestCase
{
	protected $path_to_test_data = '/inc/Engine/Optimization/LazyRenderContent/Frontend/Subscriber/add_hashes.php';

	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Install in set_up_before_class because of exists().
		self::installLrcTable();
	}

	public static function tear_down_after_class() {
		self::uninstallLrcTable();

		parent::tear_down_after_class();
	}
	public function set_up()
	{
		parent::set_up();

		$this->unregisterAllCallbacksExcept('rocket_critical_image_saas_visit_buffer', 'add_hashes', 16);
	}

	public function tear_down()
	{
		$this->restoreWpHook('rocket_critical_image_saas_visit_buffer');
		remove_filter( 'rocket_lazy_render_content_optimization', '__return_false' );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldWorkAsExpected( $config, $expected ) {
		self::addLrc($config['row']);

		add_filter( 'rocket_lazy_render_content_optimization', '__return_true' );

		$this->assertSame(
			$expected['html'],
			apply_filters( 'rocket_critical_image_saas_visit_buffer', $config['html'] )
		);
	}
}
