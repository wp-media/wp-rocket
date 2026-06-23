<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\FrontendSubscriber;

use ReflectionProperty;
use WP_Rocket\Engine\CDN\RocketCDN\FrontendSubscriber;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\FrontendSubscriber::set_cdn_zone
 * @group  CDN
 * @group  RocketCDN
 */
class Test_SetCdnZone extends TestCase {

	/**
	 * @var FrontendSubscriber
	 */
	private $subscriber;

	/**
	 * @var \WP_Rocket\Admin\Options
	 */
	private $options_api;

	/**
	 * Reflection property to reset per-request memoization between tests.
	 *
	 * @var ReflectionProperty
	 */
	private $memoized_url_prop;

	public function set_up() {
		parent::set_up();

		$container         = apply_filters( 'rocket_container', null );
		$this->subscriber  = $container->get( 'rocketcdn_frontend_subscriber' );
		$this->options_api = $container->get( 'options_api' );

		$this->memoized_url_prop = new ReflectionProperty( FrontendSubscriber::class, 'rocketcdn_url' );
		$this->memoized_url_prop->setAccessible( true );

		set_current_screen( 'front' );

		delete_transient( 'rocketcdn_status' );
	}

	public function tear_down() {
		$settings = $this->options_api->get( 'settings', [] );
		unset( $settings['cdn_type'] );
		$this->options_api->set( 'settings', $settings );

		delete_transient( 'rocketcdn_status' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ): void {
		$settings             = $this->options_api->get( 'settings', [] );
		$settings['cdn_type'] = $config['cdn_type'];
		$this->options_api->set( 'settings', $settings );

		$this->setup_subscription_transient( $config );

		$this->memoized_url_prop->setValue( $this->subscriber, null );

		$result = $this->subscriber->set_cdn_zone( null );

		$this->assertSame( $expected['cdn_zone'], $result );
	}

	/**
	 * Sets up the rocketcdn_status transient based on fixture config.
	 *
	 * @param array $config Fixture config.
	 * @return void
	 */
	private function setup_subscription_transient( array $config ): void {
		if ( ! isset( $config['has_active_subscription'] ) ) {
			return;
		}

		if ( $config['has_active_subscription'] ) {
			set_transient(
				'rocketcdn_status',
				[
					'subscription_status' => 'running',
					'plan_type'           => 'free',
					'status_code'         => 200,
					'cdn_url'             => $config['cdn_url'] ?? '',
				],
				HOUR_IN_SECONDS
			);
		} else {
			set_transient(
				'rocketcdn_status',
				[
					'subscription_status' => 'cancelled',
					'plan_type'           => 'free',
					'status_code'         => 200,
					'cdn_url'             => '',
				],
				HOUR_IN_SECONDS
			);
		}
	}
}
