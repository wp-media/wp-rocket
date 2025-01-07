<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use WP_Error;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::display_rocketcdn_cta
 * @uses   ::rocket_is_live_site
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_pricing_data
 * @uses   \WP_Rocket\Abstract_Render::generate
 * @uses   ::rocket_direct_filesystem
 *
 * @group  AdminOnly
 * @group  RocketCDN
 */
class Test_DisplayRocketcdnCta extends TestCase {

	public function set_up() {
		parent::set_up();

		update_option( 'date_format', 'Y-m-d' );
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'rocket_before_cdn_sections' );
		$actual = ob_get_clean();

		return empty( $actual ) ? '' : $this->format_the_html( $actual );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayPerData( $data, $expected, $config ) {
		$this->white_label = isset( $config['white_label'] ) ? $config['white_label'] : $this->white_label;

		if ( isset( $config['home_url'] ) ) {
			$this->home_url = $config['home_url'];
			add_filter( 'home_url', [ $this, 'home_url_cb' ] );
		}

		if ( isset( $data['rocketcdn_status'] ) ) {
			set_transient( 'rocketcdn_status', $data['rocketcdn_status'], MINUTE_IN_SECONDS );
		}

		if ( isset( $expected['integration']['assertNotContains'] ) ) {
			$this->assertStringNotContainsString( $expected['integration']['assertNotContains'], $this->getActualHtml() );

			return;
		}

		if ( isset( $expected['integration']['not_expected'] ) ) {
			foreach ( $expected['integration']['not_expected'] as $not_expected ) {
				$this->assertStringNotContainsString( $not_expected, $this->getActualHtml() );
			}

			return;
		}

		set_transient(
			'rocketcdn_pricing',
			$config['is_wp_error']
				? new WP_Error( 'rocketcdn_error', $data['rocketcdn_pricing'] )
				: $data['rocketcdn_pricing'],
			MINUTE_IN_SECONDS
		);

		if ( $config['rocket_rocketcdn_cta_hidden'] ) {
			$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
			wp_set_current_user( $user_id );
			add_user_meta( $user_id, 'rocket_rocketcdn_cta_hidden', true );
		}

		$this->assertStringContainsString( $this->format_the_html( $expected['integration'] ), $this->getActualHtml() );
	}
}
