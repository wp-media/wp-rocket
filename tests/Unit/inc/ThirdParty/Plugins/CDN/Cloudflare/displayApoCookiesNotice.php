<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use CoquardcyrWpArticleScheduler\Dependencies\League\Plates\Template\Func;
use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::display_apo_cookies_notice
 */
class Test_displayApoCookiesNotice extends TestCase {

	/**
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * @var Options
	 */
	protected $option_api;

	/**
	 * @var Beacon
	 */
	protected $beacon;

	/**
	 * @var Cloudflare
	 */
	protected $cloudflare;

	public function set_up() {
		parent::set_up();
		$this->options = Mockery::mock(Options_Data::class);
		$this->option_api = Mockery::mock(Options::class);
		$this->beacon = Mockery::mock(Beacon::class);

		$this->cloudflare = new Cloudflare($this->options, $this->option_api, $this->beacon);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected )
	{
		Functions\when('esc_url')->returnArg();
		Functions\when('esc_attr')->returnArg();
		$this->stubTranslationFunctions();
		Functions\when('home_url')->justReturn($config['home_url']);
		$this->configure_user_can($config, $expected);
		$this->configure_screen($config, $expected);
		$this->configure_apply_mandatory_cookies($config, $expected);
		$this->configure_apply_dynamic_cookies($config, $expected);
		$this->configure_apo($config,$expected);
		$this->configure_notice($config, $expected);
		$this->cloudflare->display_apo_cookies_notice();
	}

	protected function configure_user_can($config, $expected) {
		Functions\expect('current_user_can')->with('rocket_manage_options')->andReturn($config['can']);
	}

	protected function configure_screen($config, $expected) {
		if(! $config['can']) {
			return;
		}
		Functions\expect('get_current_screen')->andReturn($config['screen']);
	}

	protected function configure_apply_mandatory_cookies($config, $expected) {
		if(! $config['right_screen'] || ! $config['can']) {
			return;
		}

		Functions\expect('get_rocket_cache_mandatory_cookies')->with()->andReturn($config['mandatory_cookies']);
	}

	protected function configure_apo($config, $expected) {
		if(! $config['right_screen'] || ! $config['can'] || (count($config['dynamic_cookies']) === 0 && count($config['mandatory_cookies']) === 0)) {
			return;
		}
		Functions\expect('wp_get_http_headers')->with($expected['home_url'])->andReturn($config['headers']);
		$this->beacon->expects()->get_suggest('cloudflare_apo')->andReturn($config['beacon_response']);
	}

	protected function configure_apply_dynamic_cookies($config, $expected) {
		if(! $config['right_screen'] || ! $config['can']) {
			return;
		}

		Functions\expect('get_rocket_cache_dynamic_cookies')->with()->andReturn($config['dynamic_cookies']);
	}

	protected function configure_notice($config, $expected) {
		if(! $config['should_display']) {
			Functions\expect('rocket_notice_html')->never();
			return;
		}
		Functions\expect('rocket_notice_html')->with($expected['notice']);
	}
}
