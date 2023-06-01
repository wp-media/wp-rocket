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
		Functions\expect('is_plugin_active')->with('cloudflare/cloudflare.php')->andReturn($config['plugin_active']);
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
        $this->cloudflare->display_apo_cache_notice();
    }

	protected function configure_check_apo($config, $expected) {
		if( ! $config['is_plugin_active']) {
			return;
		}
		$this->options->get('automatic_platform_optimization', false)->andReturn($config['settings']);
	}

	protected function configure_check_screen() {

	}

	protected function configure_check_mobile_cache() {

	}

	protected function configure_notice() {

	}

}
