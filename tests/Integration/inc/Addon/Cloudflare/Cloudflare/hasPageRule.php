<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Cloudflare;

use WP_Error;
use WP_Rocket\Addon\Cloudflare\API\Client;
use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Cloudflare::has_page_rule
 *
 * @group Cloudflare
 */
class TestHasPageRule extends TestCase {
	use HttpRequestTrait;

	// Not needed here: the settings trait's set_up() write to wp_rocket_settings triggers the
	// Cloudflare Subscriber's own real zone lookup before this test gets a chance to mock it.
	protected static $use_settings_trait = false;

	private $cloudflare;

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		add_filter( 'pre_get_rocket_option_cloudflare_zone_id', [ $this, 'mock_cloudflare_zone_id' ] );

		$container = apply_filters( 'rocket_container', null );

		$this->cloudflare = $container->get( 'cloudflare' );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_cloudflare_zone_id', [ $this, 'mock_cloudflare_zone_id' ] );

		$this->tear_down_http();

		parent::tear_down();
	}

	/** Forces the Cloudflare zone ID option to a fixed value for the mocked HTTP fixtures. */
	public function mock_cloudflare_zone_id() {
		return '12345';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->config['http'] = [
			Client::CLOUDFLARE_API . 'zones/12345/pagerules?status=active' => $config['response'],
		];

		$result = $this->cloudflare->has_page_rule( $config['action_value'] );

		if ( 'error' === $expected ) {
			$this->assertInstanceOf(
				WP_Error::class,
				$result
			);
		} else {
			$this->assertSame(
				$expected,
				$result
			);
		}
	}
}
