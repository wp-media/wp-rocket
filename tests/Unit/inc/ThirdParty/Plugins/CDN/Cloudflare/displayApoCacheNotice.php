<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Mockery;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;


use WP_Rocket\Tests\Unit\TestCase;

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
        $this->cloudflare->display_apo_cache_notice();
    }

}
