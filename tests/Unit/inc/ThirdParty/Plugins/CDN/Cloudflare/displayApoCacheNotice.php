<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Mockery;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::display_apo_cache_notice
 */
class Test_displayApoCacheNotice extends TestCase {

    /**
     * @var Options_Data
     */
    protected $options;

    /**
     * @var Options
     */
    protected $option_api;

    /**
     * @var Cloudflare
     */
    protected $cloudflare;

    public function set_up() {
        parent::set_up();
        $this->options = Mockery::mock(Options_Data::class);
        $this->option_api = Mockery::mock(Options::class);

        $this->cloudflare = new Cloudflare($this->options, $this->option_api);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		Functions\expect('is_plugin_active')->with('cloudflare/cloudflare.php')->andReturn($config['plugin_enabled']);
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

			return null;
		});
		$this->configure_user_can($config, $expected);
		$this->configure_check_apo($config, $expected);
		$this->configure_check_screen($config, $expected);
		$this->configure_check_mobile_cache($config, $expected);
		$this->configure_notice($config, $expected);
        $this->cloudflare->display_apo_cache_notice();
    }

	protected function configure_user_can($config, $expected) {
		if(! $config['is_plugin_activated']) {
			return;
		}
		Functions\expect('current_user_can')->with('rocket_manage_options')->andReturn($config['can']);
	}

	protected function configure_check_apo($config, $expected) {
		if( ! $config['is_plugin_activated'] || ! $config['can']) {
			return;
		}
		$this->options->get('automatic_platform_optimization', false)->andReturn($config['settings']);
	}

	protected function configure_check_screen($config, $expected) {
		if( ! $config['is_plugin_activated'] || ! $config['can'] || ! $config['has_apo'] ) {
			return;
		}
		Functions\expect('get_current_screen')->andReturn($config['screen']);
	}

	protected function configure_check_mobile_cache($config, $expected) {
		if(! $config['is_plugin_activated'] || ! $config['can'] || ! $config['has_apo'] || $config['right_screen'] ) {
			return;
		}
		$this->options->expects()->get('cache_mobile', false)->andReturn($config['mobile_cache']);
		$this->option_api->expects()->get('automatic_platform_optimization_cache_by_device_type', false)->andReturn($config['cloudflare_mobile_cache']);
	}

	protected function configure_notice($config, $expected) {
		if(! $config['should_display'] ) {
			Functions\expect('rocket_notice_html')->never();
			return;
		}

		Functions\expect('rocket_notice_html')->with($expected['notice']);
	}

}
