<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Drivers\RocketCDNFree;

use WP_Rocket\Engine\CDN\Drivers\RocketCDNFree;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Drivers\RocketCDNFree::should_rewrite_url
 * @group  CDN
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_ShouldRewriteUrl extends TestCase {
	use DBTrait;

	/**
	 * @var RocketCDNFree
	 */
	private $driver;

	/**
	 * @var RocketCDNQuery
	 */
	private $query;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::installRocketCDNTable();
	}

	public static function tear_down_after_class() {
		self::uninstallRocketCDNTable();
		parent::tear_down_after_class();
	}

	/**
	 * Hostnames returned by the rocket_cdn_cnames filter.
	 *
	 * @var array
	 */
	private $cdn_urls = [];

	/**
	 * @var \WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * @var \WP_Rocket\Engine\License\API\User
	 */
	private $user;

	public function set_up() {
		parent::set_up();

		$container                     = apply_filters( 'rocket_container', null );
		$this->driver                  = $container->get( 'cdn_driver_free' );
		$this->query                   = $container->get( 'rocketcdn_query' );
		$this->subscription_controller = $container->get( 'rocketcdn_subscription_controller' );
		$this->user                    = $container->get( 'user' );

		// A valid-by-default licence, so the "not forced off" data sets exercise
		// Context::is_forced_off()'s real logic without tripping the "free plan with an
		// invalid licence" branch just because no real licence is configured in this test
		// environment (mirrors tests/Integration/inc/Engine/CDN/CdnStateBridge/resolveLive.php).
		$this->set_valid_licence();

		self::truncateRocketCDNTable();
		wp_cache_flush();
	}

	public function tear_down() {
		self::truncateRocketCDNTable();
		remove_filter( 'rocket_cdn_cnames', [ $this, 'filter_cdn_cnames' ] );
		delete_transient( 'rocketcdn_status' );

		// 'user' is a container singleton — restore it to its pre-test default so other
		// test files relying on the same "no licence configured" default aren't affected.
		$this->user->set_user( new \stdClass() );

		parent::tear_down();
	}

	/**
	 * Sets a currently-valid (not expired, not revoked) licence on the shared `user` service.
	 *
	 * @return void
	 */
	private function set_valid_licence(): void {
		$licence                            = new \stdClass();
		$licence->is_revoked                = false;
		$licence->plugin_updates_ban_reason = '';

		$user_data                     = new \stdClass();
		$user_data->licence_expiration = time() + YEAR_IN_SECONDS;
		$user_data->licence            = $licence;
		$user_data->is_reseller        = false;

		$this->user->set_user( $user_data );
	}

	/**
	 * Sets an expired licence on the shared `user` service, so Context::is_forced_off()'s
	 * "free plan with an invalid licence" branch resolves to true.
	 *
	 * @return void
	 */
	private function set_invalid_licence(): void {
		$licence                            = new \stdClass();
		$licence->is_revoked                = false;
		$licence->plugin_updates_ban_reason = '';

		$user_data                     = new \stdClass();
		$user_data->licence_expiration = time() - DAY_IN_SECONDS;
		$user_data->licence            = $licence;
		$user_data->is_reseller        = false;

		$this->user->set_user( $user_data );
	}

	/**
	 * @return array
	 */
	public function filter_cdn_cnames(): array {
		return $this->cdn_urls;
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, bool $expected ): void {
		// A free-plan subscription. Not cancelled, so only the "free plan with an invalid
		// licence" branch of Context::is_forced_off() is reachable from this data set — the
		// licence is flipped to invalid below for the forced-off case. Also reset the
		// controller's own per-request subscription-data memo — it's a container singleton,
		// so a stale value cached by an earlier data set/test would otherwise survive the
		// transient change above.
		set_transient(
			'rocketcdn_status',
			[
				'subscription_status' => 'running',
				'plan_type'           => 'free',
				'status_code'         => 200,
				'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			],
			HOUR_IN_SECONDS
		);
		$this->set_reflective_property( null, 'subscription', $this->subscription_controller );

		if ( ! empty( $config['is_forced_off'] ) ) {
			$this->set_invalid_licence();
		}

		if ( ! empty( $config['is_found'] ) ) {
			$this->query->add_item(
				[
					'url'           => untrailingslashit( $config['url'] ),
					'title'         => 'Test Page',
					'modified'      => current_time( 'mysql' ),
					'last_accessed' => current_time( 'mysql' ),
				]
			);
			wp_cache_flush();
		}

		$this->cdn_urls = $config['cdn_urls'] ?? [];
		add_filter( 'rocket_cdn_cnames', [ $this, 'filter_cdn_cnames' ] );

		// cdn_driver_free is a container singleton (addShared), so CdnHostnameTrait's
		// per-instance memoization would otherwise leak the first data set's hostname
		// answer into every subsequent one.
		$this->set_reflective_property( null, 'has_hostname_memo', $this->driver );
		$this->set_reflective_property( [], 'should_rewrite_memo', $this->driver );

		$this->assertSame( $expected, $this->driver->should_rewrite_url( $config['url'] ) );
	}
}
