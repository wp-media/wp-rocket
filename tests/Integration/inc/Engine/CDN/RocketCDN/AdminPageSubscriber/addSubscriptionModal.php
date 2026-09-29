<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::add_subscription_modal
 * @uses   ::rocket_is_live_site
 * @uses   ::rocket_get_constant
 *
 * @group  AdminOnly
 * @group  RocketCDN
 * @group  RocketCDNAdminPage
 */
class Test_AddSubscriptionModal extends TestCase {

	public function set_up() {
		parent::set_up();

		add_filter( 'home_url', [ $this, 'home_url_cb' ] );
	}

	public function tear_down() {
		delete_transient( 'wp_rocket_customer_data' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayExpected( $config, $expected ) {
		$this->white_label = isset( $config['white_label'] ) ? $config['white_label'] : $this->white_label;
		$this->home_url = $config['home_url'];

		if ( isset( $config['user_data'] ) ) {
			// Convert nested arrays to objects to match the actual API response structure.
			$user_data = json_decode( wp_json_encode( $config['user_data'] ) );
			set_transient( 'wp_rocket_customer_data', $user_data, MINUTE_IN_SECONDS );
		}

		ob_start();
		do_action( 'rocket_settings_page_footer' );
		$actual = ob_get_clean();

		if ( ! empty ( $expected ) ) {
			$expected = $this->format_the_html( $expected );
		}

		if ( ! empty ( $actual ) ) {
			$actual = $this->format_the_html( $actual );
		}

		$this->assertSame( $expected, $actual );

		// AC-2: a button URL containing a single quote must never break out of the
		// double-quoted JSON string that wp_json_encode() emits into the inline <script> block.
		if ( isset( $config['user_data'] ) ) {
			$this->assertMatchesRegularExpression(
				'/window\.rocketcdnButtonUrl = "[^"]*";/',
				$actual,
				'The rendered rocketcdnButtonUrl assignment must be a syntactically valid, double-quoted JS string literal.'
			);
		}
	}
}
