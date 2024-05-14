<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Subscriber::clean_urls
 */
class Test_CleanUrls extends AdminTestCase
{
	protected $manual_preload;

	public function setUp(): void
	{
		parent::setUp();
		add_filter('pre_get_rocket_option_manual_preload', [$this, 'manual_preload']);
	}

	public function tear_down()
	{
		remove_filter('pre_get_rocket_option_manual_preload', [$this, 'manual_preload']);
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		$this->manual_preload = $config['manual_preload'];
		foreach ($config['data'] as $cache) {
			self::addCache($cache);
		}

		do_action('rocket_after_clean_terms', $config['urls']);

		foreach ($expected['data'] as $cache) {
			$this->assertTrue(self::cacheFound($cache));
		}
	}

	public function manual_preload() {
		return $this->manual_preload;
	}
}
