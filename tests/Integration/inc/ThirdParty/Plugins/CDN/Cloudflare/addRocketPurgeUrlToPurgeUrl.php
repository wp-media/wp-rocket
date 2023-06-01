<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\CDN\Cloudflare;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::add_rocket_purge_url_to_purge_url
 */
class Test_addRocketPurgeUrlToPurgeUrl extends TestCase {

	protected $config;

	public function set_up()
	{
		parent::set_up();
		add_filter('rocket_post_purge_urls', [$this, 'rocket_post_purge_urls']);
	}

	public function tear_down()
	{
		remove_filter('rocket_post_purge_urls', [$this, 'rocket_post_purge_urls']);
		parent::tear_down();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {

		if($config['post']) {
			$post_id = wp_insert_post([]);
		} else {
			$post_id = -1;
		}

		$this->config = $config;
        $this->assertSame($expected['result'], apply_filters('cloudflare_purge_by_url', $config['purge_urls'], $post_id));
    }

	public function rocket_post_purge_urls() {
		return $this->config['rocket_post_purge_urls'];
	}
}
