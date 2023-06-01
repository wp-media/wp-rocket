<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Mockery;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;


use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::disable_cloudflare_option
 */
class Test_disableCloudflareOption extends TestCase {

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

        $this->cloudflare = new Cloudflare($this->options);
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

			return null;
		});
		$this->assertSame($expected['enabled'], $this->cloudflare->disable_cloudflare_option($config['enabled']));
    }
}
