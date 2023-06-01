<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\CDN\Cloudflare;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::display_apo_cookies_notice
 */
class Test_displayApoCookiesNotice extends TestCase {

	public function set_up()
	{
		parent::set_up();
		add_filter('pre_http_request', [$this, 'request'], 10, 2);
		add_filter('rocket_cache_mandatory_cookies', [$this, 'mandatory_cookies']);
		add_filter('rocket_cache_dynamic_cookies', [$this, 'dynamic_cookies']);
	}

	public function tear_down()
	{
		remove_filter('rocket_cache_dynamic_cookies', [$this, 'dynamic_cookies']);
		remove_filter('rocket_cache_mandatory_cookies', [$this, 'mandatory_cookies']);
		remove_filter('pre_http_request', [$this, 'request'], 10);
		parent::tear_down();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		ob_start();
		do_action('admin_notices');
		$notices = ob_get_clean();
		if($config['should_display']) {
			$this->assertStringContainsString(
				$this->format_the_html( $expected['notice']['message'] ),
				$this->format_the_html( $notices )
			);
		} else {
			$this->assertStringNotContainsString(
				$this->format_the_html( $expected['notice']['message'] ),
				$this->format_the_html( $notices )
			);
		}
	}

	public function request($args, $url) {
		if('http://example.org' === $url) {
			return $this->config['response_fixture'];
		}
	}

	public function mandatory_cookies() {
		return $this->config['mandatory_cookies'];
	}

	public function dynamic_cookies() {
		return $this->config['dynamic_cookies'];
	}
}
