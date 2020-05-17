<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::display_rocketcdn_status
 * @uses \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 * @uses ::rocket_is_live_site
 * @uses ::rocket_get_constant
 * @uses \WP_Rocket\Abstract_Render::generate
 * @uses ::rocket_direct_filesystem
 *
 * @group  RocketCDN
 * @group  AdminOnly
 * @group  RocketCDNAdminPage
 */
class Test_DisplayRocketcdnStatus extends TestCase {
	protected $path_to_test_data = '/inc/Engine/CDN/RocketCDN/AdminPageSubscriber/displayRocketcdnStatus.php';

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		update_option( 'date_format', 'Y-m-d' );
	}

	public function setUp() {
		parent::setUp();

		add_filter( 'home_url', [ $this, 'home_url_cb' ] );
	}

	public function tearDown() {
		parent::tearDown();

		delete_transient( 'rocketcdn_status' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayExpected( $rocketcdn_status, $expected, $config ) {
		$this->home_url = $config['home_url'];
		set_transient( 'rocketcdn_status', $rocketcdn_status, MINUTE_IN_SECONDS );

		ob_start();
		do_action( 'rocket_dashboard_after_account_data' );
		$actual = ob_get_clean();

		$this->assertSame(
			$this->format_the_html( $expected['integration'] ),
			$this->format_the_html( $actual )
		);
	}
}
