<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Mockery;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

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
		Functions\when('home_url')->justReturn($config['home_url']);
	    Functions\expect('wp_remote_retrieve_headers')->with($expected['response'])->andReturn($config['headers']);
	    Functions\expect('wp_remote_request')->with($expected['home_url'], $expected['configs'])->andReturn($config['response']);
		$this->configure_apply_mandatory_cookies($config, $expected);
		$this->configure_apply_dynamic_cookies($config, $expected);
		$this->configure_notice($config, $expected);
        $this->cloudflare->display_apo_cache_notice();
    }

	protected function configure_apply_mandatory_cookies($config, $expected) {
		if(! $config['has_apo']) {
			return;
		}

		Filters\expectApplied('rocket_cache_mandatory_cookies')->with([])->andReturn($config['mandatory_cookies']);
	}

	protected function configure_apply_dynamic_cookies($config, $expected) {
		if(! $config['has_apo']) {
			return;
		}

		Filters\expectApplied('rocket_cache_dynamic_cookies')->with([])->andReturn($config['dynamic_cookies']);
	}

	protected function configure_screen($config, $expected) {

	}

	protected function configure_notice($config, $expected) {
		if(! $config['should_display']) {
			Functions\expect('rocket_notice_html')->never();
			return;
		}
		Functions\expect('rocket_notice_html')->with($expected['notice']);
	}
}
