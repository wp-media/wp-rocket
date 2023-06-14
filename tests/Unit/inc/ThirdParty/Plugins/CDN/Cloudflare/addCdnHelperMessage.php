<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use Brain\Monkey\Functions;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::add_cdn_helper_message
 */
class Test_addCdnHelperMessage extends TestCase {

    /**
     * @var Options_Data
     */
    protected $options;

    /**
     * @var Options
     */
    protected $options_api;

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
        $this->options_api = Mockery::mock(Options::class);
        $this->beacon = Mockery::mock(Beacon::class);

        $this->cloudflare = new Cloudflare($this->options, $this->options_api, $this->beacon);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Functions\when('is_plugin_active')->justReturn($config['plugin_enabled']);
		Functions\when('get_option')->alias(function ($name) use ($config) {
			if('cloudflare_api_email' === $name) {
				return $config['cf_email'];
			}
			if('cloudflare_api_key' === $name) {
				return $config['cf_key'];
			}
			if('cloudflare_cached_domain_name' === $name) {
				return $config['cf_domain'];
			}
		});
        $this->assertSame($expected, $this->cloudflare->add_cdn_helper_message($config['addons']));
    }
}
