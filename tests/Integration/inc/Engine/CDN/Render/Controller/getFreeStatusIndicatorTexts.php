<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Render\Controller;

use WP_Rocket\Engine\CDN\Render\Controller;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::get_free_status_indicator_texts
 * @group  CDN
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_GetFreeStatusIndicatorTexts extends TestCase {

	/**
	 * @var Controller
	 */
	private $controller;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * Default text array passed into the filter.
	 *
	 * @var array
	 */
	private $base_texts = [
		'paused_status_text' => 'RocketCDN is paused',
		'active_status_text' => 'RocketCDN is active',
		'paused_details'     => 'RocketCDN is currently paused. Click Resume CDN to re-enable content delivery.',
		'status_text'        => '',
		'details'            => 'Start with your homepage...',
		'class'              => '',
	];

	public function set_up() {
		parent::set_up();

		$container        = apply_filters( 'rocket_container', null );
		$this->controller = $container->get( 'cdn_render_controller' );
		$this->user       = $container->get( 'user' );

		delete_transient( 'rocketcdn_status' );
	}

	public function tear_down() {
		delete_transient( 'rocketcdn_status' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ): void {
		$this->set_subscription_transient( $config );
		$this->set_user_license( $config );

		$result = $this->controller->get_free_status_indicator_texts(
			$this->base_texts,
			$config['pages_count'],
			$config['is_loading'],
			$config['free']
		);

		if ( '' !== $expected['class_contains'] ) {
			$this->assertStringContainsString( $expected['class_contains'], $result['class'] );
		} else {
			$this->assertStringNotContainsString( 'wpr-cdn-status--expired', $result['class'] );
		}

		if ( isset( $expected['paused_status_text_not'] ) ) {
			$this->assertNotSame( $expected['paused_status_text_not'], $result['paused_status_text'] );
		}

		if ( isset( $expected['paused_details_not_contains'] ) ) {
			$this->assertStringNotContainsString( $expected['paused_details_not_contains'], $result['paused_details'] );
		}

		if ( isset( $expected['paused_details_contains'] ) ) {
			$this->assertStringContainsString( $expected['paused_details_contains'], $result['paused_details'] );
		}
	}

	/**
	 * Sets the rocketcdn_status transient from fixture config.
	 */
	private function set_subscription_transient( array $config ): void {
		if ( ! isset( $config['subscription_status'] ) ) {
			return;
		}

		set_transient(
			'rocketcdn_status',
			[
				'subscription_status' => $config['subscription_status'],
				'plan_type'           => $config['plan_type'] ?? 'free',
				'status_code'         => 200,
				'cdn_url'             => $config['cdn_url'] ?? '',
			],
			HOUR_IN_SECONDS
		);
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
