<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\CDN\Cloudflare;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::add_rocket_purge_url_to_purge_url
 */
class Test_addRocketPurgeUrlToPurgeUrl extends TestCase {

	protected $config;

	private static $post_id;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$post_id = $factory->post->create();
		// Set global for WP<5.2 where get_the_content() doesn't take the $post parameter.
		$GLOBALS['post'] = get_post( self::$post_id );
		setup_postdata( self::$post_id );
	}


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
			$post_id = self::$post_id;
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
