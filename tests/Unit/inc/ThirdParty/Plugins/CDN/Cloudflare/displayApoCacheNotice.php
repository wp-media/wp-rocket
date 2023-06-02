<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use CoquardcyrWpArticleScheduler\Dependencies\League\Plates\Template\Func;
use Mockery;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;

use WP_Rocket\Engine\Admin\Beacon\Beacon;
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

			return null;
		});
		$this->configure_user_can($config, $expected);
		$this->configure_check_plugin($config, $expected);
		$this->configure_cloudflare($config, $expected);
		$this->configure_check_apo($config, $expected);
		$this->configure_check_screen($config, $expected);
		$this->configure_check_mobile_cache($config, $expected);
		$this->configure_notice($config, $expected);
        $this->cloudflare->display_apo_cache_notice();
    }

	protected function configure_user_can($config, $expected) {
		Functions\expect('current_user_can')->with('rocket_manage_options')->andReturn($config['can']);
	}

	protected function configure_check_screen($config, $expected) {
		if(! $config['can'] ) {
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

	protected function configure_check_apo($config, $expected) {
		if( ! $config['right_screen'] || ! $config['can'] || ! $config['is_plugin_activated']) {
			return;
		}
		Functions\expect('wp_get_http_headers')->with($config['home_url'])->andReturn($config['headers']);
	}

	protected function configure_cloudflare($config, $expected) {
		if( ! $config['right_screen'] || ! $config['can'] || $config['has_apo'] ) {
			return;
		}
		Functions\expect('is_plugin_active')->with('cloudflare/cloudflare.php')->andReturn($config['plugin_enabled']);
	}

	protected function configure_check_mobile_cache($config, $expected) {
		if(! $config['is_plugin_activated'] || ! $config['can'] || ! $config['has_apo'] || ! $config['right_screen'] ) {
			return;
		}
		var_dump($config['cloudflare_mobile_cache']);
		$this->options->expects()->get('do_caching_mobile_files', 0)->andReturn($config['mobile_cache']);
		Functions\expect('get_option')->with('automatic_platform_optimization_cache_by_device_type', [])->andReturn($config['cloudflare_mobile_cache']);
	}

	protected function configure_notice($config, $expected) {
		if(! $config['should_display'] ) {
			Functions\expect('rocket_notice_html')->never();
			return;
		}

		Functions\expect('rocket_notice_html')->with($expected['notice']);
	}

}
