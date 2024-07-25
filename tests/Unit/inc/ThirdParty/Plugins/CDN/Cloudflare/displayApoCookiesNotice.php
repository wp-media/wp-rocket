<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\CDN\{Cloudflare, CloudflareFacade};

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::display_apo_cookies_notice
 *
 * @group ThirdParty
 * @group CloudflarePlugin
 */
class TestDisplayApoCookiesNotice extends TestCase {
	protected $options;
	protected $option_api;
	protected $beacon;
	protected $cloudflare;

	public function set_up() {
		parent::set_up();

		$this->stubEscapeFunctions();
		$this->stubTranslationFunctions();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->option_api = Mockery::mock( Options::class );
		$this->beacon     = Mockery::mock( Beacon::class );
		$this->cloudflare = new Cloudflare( $this->options, $this->option_api, $this->beacon, Mockery::mock(  CloudflareFacade::class ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		Functions\when('home_url')->justReturn($config['home_url']);
		Functions\when('get_option')->alias(function ($name) use ($config) {
			if('cloudflare_api_email' === $name) {
				return $config['cloudflare_api_email'];
			}
			if('cloudflare_api_key' === $name) {
				return $config['cloudflare_api_key'];
			}

			if('cloudflare_cached_domain_name' === $name) {
				return $config['cloudflare_cached_domain_name'];
			}

			if('automatic_platform_optimization' === $name) {
				return $config['automatic_platform_optimization'];
			}

			return null;
		});
		$this->configure_user_can($config, $expected);
		$this->configure_screen($config, $expected);
		$this->configure_plugin($config, $expected);
		$this->configure_check_plugin($config, $expected);
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

	protected function configure_check_plugin($config, $expected) {
		if( ! $config['right_screen'] || ! $config['can']) {
			return;
		}
		Functions\expect('is_plugin_active')->with('cloudflare/cloudflare.php')->andReturn($config['plugin_enabled']);
	}

	protected function configure_apply_mandatory_cookies($config, $expected) {
		if(! $config['right_screen'] || ! $config['can']) {
			return;
		}

		Functions\expect('get_rocket_cache_mandatory_cookies')->with()->andReturn($config['mandatory_cookies']);
	}

	protected function configure_plugin($config, $expected) {
		if(! $config['right_screen'] || ! $config['can'] || (count($config['dynamic_cookies']) === 0 && count($config['mandatory_cookies']) === 0)) {
			return;
		}
	}

	protected function configure_apo($config, $expected) {
		if(! $config['right_screen'] || ! $config['can'] || (count($config['dynamic_cookies']) === 0 && count($config['mandatory_cookies']) === 0)) {
			return;
		}
		$this->beacon->shouldReceive('get_suggest')
			->with('cloudflare_apo')
			->andReturn($config['beacon_response']);	}

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
