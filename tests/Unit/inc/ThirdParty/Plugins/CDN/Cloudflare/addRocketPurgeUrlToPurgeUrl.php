<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Mockery;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::add_rocket_purge_url_to_purge_url
 */
class Test_addRocketPurgeUrlToPurgeUrl extends TestCase {

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
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Functions\expect('get_post')->with($expected['post_id'])->andReturn($config['post']);
		$this->configure_apply_filter($config, $expected);
        $this->assertSame($expected['result'], $this->cloudflare->add_rocket_purge_url_to_purge_url($config['purge_urls']), $config['post_id']);
    }

	protected function configure_apply_filter($config, $expected) {
		if(! $config['post']) {
			return;
		}

		Filters\expectApplied('rocket_post_purge_urls')->with($expected['purge_urls'], $expected['post'])->andReturn($config['filtered_purge_urls']);
	}
}
