<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Context;

use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Context::get_driver
 * @group  CDN
 * @group  AdminOnly
 */
class Test_GetDriver extends TestCase {

	/**
	 * @var Context
	 */
	private $context;

	/**
	 * CDN type value returned by the pre_get_rocket_option_cdn_type filter.
	 *
	 * @var string
	 */
	private $cdn_type_value = 'rocketcdn';

	public function set_up() {
		parent::set_up();

		$container     = apply_filters( 'rocket_container', null );
		$this->context = $container->get( 'cdn_context' );

		delete_transient( 'rocketcdn_status' );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'cdn_type_cb' ] );
		$this->cdn_type_value = 'rocketcdn';

		delete_transient( 'rocketcdn_status' );

		parent::tear_down();
	}

	public function cdn_type_cb(): string {
		return $this->cdn_type_value;
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, string $expected ): void {
		$this->cdn_type_value = $config['cdn_type'];
		add_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'cdn_type_cb' ] );

		if ( isset( $config['has_active_subscription'] ) || ! empty( $config['is_in_grace_period'] ) ) {
			set_transient( 'rocketcdn_status', $this->build_transient( $config ), HOUR_IN_SECONDS );
		}

		$this->assertSame( $expected, $this->context->get_driver() );
	}

	/**
	 * Builds a rocketcdn_status transient payload from the fixture config.
	 *
	 * @param array $config Fixture config.
	 * @return array
	 */
	private function build_transient( array $config ): array {
		if ( ! empty( $config['has_active_subscription'] ) ) {
			return [
				'subscription_status' => 'running',
				'plan_type'           => ! empty( $config['is_paid'] ) ? 'paid' : 'free',
				'status_code'         => 200,
				'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			];
		}

		if ( ! empty( $config['is_in_grace_period'] ) ) {
			return [
				'subscription_status' => 'cancelled',
				'plan_type'           => 'paid',
				'status_code'         => 200,
				'cdn_url'             => 'https://test.delivery.rocketcdn.me',
				'website_status'      => 'pending_deletion',
			];
		}

		// Cancelled outside grace period (no website_status).
		return [
			'subscription_status' => 'cancelled',
			'plan_type'           => 'free',
			'status_code'         => 200,
			'cdn_url'             => '',
		];
	}
}
