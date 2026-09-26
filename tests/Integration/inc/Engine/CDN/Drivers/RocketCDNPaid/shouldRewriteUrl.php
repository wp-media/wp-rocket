<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Drivers\RocketCDNPaid;

use WP_Rocket\Engine\CDN\Drivers\RocketCDNPaid;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Drivers\RocketCDNPaid::should_rewrite_url
 * @group  CDN
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_ShouldRewriteUrl extends TestCase {

	/**
	 * @var RocketCDNPaid
	 */
	private $driver;

	/**
	 * Excluded pages value returned by the pre_get_rocket_option_cdn_reject_pages filter.
	 *
	 * @var array
	 */
	private $cdn_reject_pages_value = [];

	/**
	 * Hostnames returned by the rocket_cdn_cnames filter.
	 *
	 * @var array
	 */
	private $cdn_urls = [];

	public function set_up() {
		parent::set_up();

		$container    = apply_filters( 'rocket_container', null );
		$this->driver = $container->get( 'cdn_driver_paid' );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_cdn_reject_pages', [ $this, 'cdn_reject_pages_cb' ] );
		remove_filter( 'rocket_cdn_cnames', [ $this, 'filter_cdn_cnames' ] );
		$this->cdn_reject_pages_value = [];
		delete_transient( 'rocketcdn_status' );

		parent::tear_down();
	}

	public function cdn_reject_pages_cb(): array {
		return $this->cdn_reject_pages_value;
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
		// A paid subscription. Cancelled + outside the grace period (website no longer
		// pending deletion) for the forced-off data set — one of Context::is_forced_off()'s
		// four branches, reachable without touching licence data — running/active otherwise.
		$is_forced_off = ! empty( $config['is_forced_off'] );

		set_transient(
			'rocketcdn_status',
			[
				'subscription_status' => $is_forced_off ? 'cancelled' : 'running',
				'plan_type'           => 'paid',
				'status_code'         => 200,
				'website_status'      => $is_forced_off ? 'active' : '',
				'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			],
			HOUR_IN_SECONDS
		);

		if ( ! empty( $config['excluded_pages'] ) ) {
			$this->cdn_reject_pages_value = $config['excluded_pages'];
			add_filter( 'pre_get_rocket_option_cdn_reject_pages', [ $this, 'cdn_reject_pages_cb' ] );
		}

		$this->cdn_urls = $config['cdn_urls'] ?? [];
		add_filter( 'rocket_cdn_cnames', [ $this, 'filter_cdn_cnames' ] );

		// cdn_driver_paid is a container singleton (addShared), so CdnHostnameTrait's
		// per-instance memoization would otherwise leak the first data set's hostname
		// answer into every subsequent one.
		$this->set_reflective_property( null, 'has_hostname_memo', $this->driver );
		$this->set_reflective_property( [], 'should_rewrite_memo', $this->driver );

		$this->assertSame( $expected, $this->driver->should_rewrite_url( $config['url'] ) );
	}
}
