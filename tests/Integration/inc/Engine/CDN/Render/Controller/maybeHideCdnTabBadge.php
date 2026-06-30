<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Render\Controller;

use WP_Rocket\Engine\CDN\Render\Controller;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::maybe_hide_cdn_tab_badge
 * @group  CDN
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_MaybeHideCdnTabBadge extends TestCase {

	/**
	 * @var Controller
	 */
	private $controller;

	public function set_up() {
		parent::set_up();

		$container        = apply_filters( 'rocket_container', null );
		$this->controller = $container->get( 'cdn_render_controller' );

		delete_transient( 'rocketcdn_status' );
	}

	public function tear_down() {
		delete_transient( 'rocketcdn_status' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, string $expected ): void {
		if ( $config['is_paid'] ) {
			set_transient(
				'rocketcdn_status',
				[
					'subscription_status' => 'running',
					'plan_type'           => 'paid',
					'status_code'         => 200,
					'cdn_url'             => 'https://test.delivery.rocketcdn.me',
				],
				HOUR_IN_SECONDS
			);
		}

		$this->assertSame( $expected, $this->controller->maybe_hide_cdn_tab_badge( $config['badge'] ) );
	}
}
