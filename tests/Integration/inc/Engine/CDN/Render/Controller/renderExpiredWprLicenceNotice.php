<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Render\Controller;

use WP_Rocket\Engine\CDN\Render\Controller;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::render_expired_wpr_licence_notice
 * @group  CDN
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_RenderExpiredWprLicenceNotice extends TestCase {

	/**
	 * CDN type override for the filter. null = no override (falls through to Options_Data cached value).
	 *
	 * @var string|null
	 */
	private static $cdn_type_override = null;

	/**
	 * @var Controller
	 */
	private $controller;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * Static filter callback. Returns null when no override is active so Options_Data falls through.
	 *
	 * @return string|null
	 */
	public static function cdn_type_cb(): ?string {
		return self::$cdn_type_override;
	}

	public function set_up() {
		parent::set_up();

		// Registered per-test (not in set_up_before_class()) because WP core's test suite
		// backs up hooks in setUp() and restores them in tearDown(): a filter added once at
		// the class level survives only until the first test's tearDown() wipes it out.
		add_filter( 'pre_get_rocket_option_cdn_type', [ static::class, 'cdn_type_cb' ] );

		$container        = apply_filters( 'rocket_container', null );
		$this->controller = $container->get( 'cdn_render_controller' );
		$this->user       = $container->get( 'user' );

		delete_transient( 'rocketcdn_status' );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_cdn_type', [ static::class, 'cdn_type_cb' ] );
		self::$cdn_type_override = null;

		delete_transient( 'rocketcdn_status' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, bool $expected ): void {
		self::$cdn_type_override = 'byocdn' === $config['cdn_type'] ? 'byocdn' : null;

		$this->set_subscription_transient( $config );
		$this->set_user_license( $config );

		ob_start();
		$this->controller->render_expired_wpr_licence_notice();
		$output = ob_get_clean();

		if ( $expected ) {
			$this->assertStringContainsString( 'wpr-cdn-expired__notice', $output );
		} else {
			$this->assertEmpty( $output );
		}

		if ( $expected && ! empty( $config['is_reseller'] ) ) {
			$this->assertStringNotContainsString( 'wpr-notice-close', $output );
		} elseif ( $expected ) {
			$this->assertStringContainsString( 'wpr-notice-close', $output );
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
		$user_data->renewal_url        = 'https://wp-rocket.me/account/';
		$user_data->is_reseller        = ! empty( $config['is_reseller'] );

		$this->user->set_user( $user_data );
	}

}
