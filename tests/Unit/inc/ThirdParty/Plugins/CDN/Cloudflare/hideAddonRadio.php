<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\ThirdParty\Plugins\CDN\{Cloudflare,CloudflareFacade};
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::hide_addon_radio
 *
 * @group ThirdParty
 * @group CloudflarePlugin
 */
class Test_hideAddonRadio extends TestCase {

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

        $this->cloudflare = new Cloudflare($this->options, $this->option_api, $this->beacon, Mockery::mock( CloudflareFacade::class ) );
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
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
		$this->assertSame($expected, $this->cloudflare->hide_addon_radio($config['enable']));
    }

}
