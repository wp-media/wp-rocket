<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\ASTrait;

/**
 * @covers \WP_Rocket\Engine\Preload\Subscriber::on_activation
 * @group  Preload
 */
class Test_OnActivation extends AdminTestCase
{
	use ASTrait;

	protected $manual_sitemap;
	protected $sitemaps;

	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		self::installFresh();
	}

	public static function tear_down_after_class()
	{
		parent::tear_down_after_class();
		self::uninstallAll();
	}

	public function setUp(): void
	{
		parent::setUp();
		add_filter('pre_get_rocket_option_manual_preload', [$this, 'manual_sitemap']);
		add_filter('rocket_sitemap_preload_list', [$this, 'return_sitemaps']);
	}

	public function tearDown(): void
	{
		remove_filter('rocket_sitemap_preload_list', [$this, 'return_sitemaps']);
		remove_filter('pre_get_rocket_option_manual_preload', [$this, 'manual_sitemap']);
		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		$this->sitemaps = $config['return_sitemaps'];
		$this->manual_sitemap = $config['is_enabled'];

		do_action('rocket_activation');

		foreach ($expected['sitemaps'] as $sitemap) {
			$this->assertEquals($expected['exist'], self::taskExist('rocket_preload_job_parse_sitemap', [$sitemap]));
		}
	}

	public function manual_sitemap() {
		return $this->manual_sitemap;
	}

	public function return_sitemaps() {
		return $this->sitemaps;
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'onActivation' );
	}
}
