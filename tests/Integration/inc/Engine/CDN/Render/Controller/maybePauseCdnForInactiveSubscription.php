<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Render\Controller;

use WP_Rocket\Engine\CDN\Render\Controller;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::maybe_pause_cdn_for_inactive_subscription
 * @group  CDN
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_MaybePauseCdnForInactiveSubscription extends TestCase {

	/**
	 * @var Controller
	 */
	private $controller;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * Per-test cdn_type filter closure, removed in tear_down().
	 *
	 * @var callable|null
	 */
	private $cdn_type_filter_callback = null;

	public function set_up() {
		parent::set_up();

		$container        = apply_filters( 'rocket_container', null );
		$this->controller = $container->get( 'cdn_render_controller' );
		$this->user       = $container->get( 'user' );

		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );
	}

	public function tear_down() {
		if ( null !== $this->cdn_type_filter_callback ) {
			remove_filter( 'pre_get_rocket_option_cdn_type', $this->cdn_type_filter_callback );
			$this->cdn_type_filter_callback = null;
		}

		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, $expected ): void {
		$cdn_type                       = $config['cdn_type'];
		$this->cdn_type_filter_callback = static function () use ( $cdn_type ) {
			return $cdn_type;
		};
		add_filter( 'pre_get_rocket_option_cdn_type', $this->cdn_type_filter_callback );

		$this->set_subscription_transient( $config );
		$this->set_user_license( $config );

		$result = $this->controller->maybe_pause_cdn_for_inactive_subscription( $config['cdn_option'] );

		$this->assertSame( $expected, $result );
	}

	/**
	 * Sets the rocketcdn_status transient from fixture config.
	 */
	private function set_subscription_transient( array $config ): void {
		if ( ! isset( $config['subscription_status'] ) ) {
			return;
		}

		$data = [
			'subscription_status' => $config['subscription_status'],
			'plan_type'           => $config['plan_type'] ?? 'free',
			'status_code'         => 200,
			'cdn_url'             => $config['cdn_url'] ?? '',
		];

		if ( isset( $config['website_status'] ) ) {
			$data['website_status'] = $config['website_status'];
		}

		set_transient( 'rocketcdn_status', $data, HOUR_IN_SECONDS );
	}

	/**
	 * Configures the User instance with the given license state.
	 */
	private function set_user_license( array $config ): void {
		$licence                            = new \stdClass();
		$licence->is_revoked                = ! empty( $config['license_revoked'] );
		$licence->plugin_updates_ban_reason = $config['ban_reason'] ?? '';

		$user_data                     = new \stdClass();
		$user_data->licence_expiration = ! empty( $config['license_expired'] )
			? time() - DAY_IN_SECONDS
			: time() + YEAR_IN_SECONDS;
		$user_data->licence            = $licence;
		$user_data->is_reseller        = ! empty( $config['is_reseller'] );

		$this->user->set_user( $user_data );
	}
}
