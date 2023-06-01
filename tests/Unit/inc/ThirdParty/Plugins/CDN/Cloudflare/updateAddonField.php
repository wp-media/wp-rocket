<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Mockery;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;
use WP_Rocket\Admin\Options_Data;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::update_addon_field
 */
class Test_updateAddonField extends TestCase {

    /**
     * @var Options_Data
     */
    protected $options;

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
    public function testShouldReturnAsExpected( $config, $expected )
    {
		$this->stubTranslationFunctions();
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
        $this->assertSame($expected, $this->cloudflare->update_addon_field($config['settings']));
    }
}
