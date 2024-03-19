<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber::treeshake
 * 
 * @group RUCSS
 */
class Test_treeshake extends FilesystemTestCase {

	use DBTrait;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Frontend/Subscriber/treeshake.php';

	protected $config;

	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		self::installFresh();
	}

	public static function tear_down_after_class()
	{
		self::uninstallAll();
		parent::tear_down_after_class();
	}

	public function set_up()
	{
		parent::set_up();
		add_filter('pre_get_rocket_option_remove_unused_css', [$this, 'rucss']);
	}

	public function tear_down()
	{
		remove_filter('pre_get_rocket_option_remove_unused_css', [$this, 'rucss']);
		parent::tear_down();
	}

    /**
     * @dataProvider providerTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		$this->config = $config;

		foreach ($config['rows'] as $row) {
			self::addResource($row);
		}

		$this->assertSame($expected['html'], apply_filters('rocket_buffer', $config['html']));

		foreach ($expected['rows'] as $row) {
			$this->assertTrue(self::resourceFound($row), json_encode($row) . ' not found');
		}
    }

	public function rucss() {
		return $this->config['rucss'];
	}
}
