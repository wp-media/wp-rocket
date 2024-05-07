<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Pressidium;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;
use Mockery;
use NinukisCaching;


/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Pressidium::purge_url
 * @group Pressidium
 */
class Test_purgeUrl extends TestCase
{

	/**
	 * @var NinukisCaching
	 */
	protected $ninukis_caching;
	public function set_up(): void {
		parent::set_up();
		$this->ninukis_caching = Mockery::mock('overload:' . NinukisCaching::class);
	}

	public function tear_down(): void
	{
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected($config): void
	{
		$this->ninukis_caching->expects()->purge_url();

		do_action('after_rocket_clean_file');
	}
}
