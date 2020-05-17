<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

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
class Test_DisplayRocketcdnStatus extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CDN/RocketCDN/AdminPageSubscriber/displayRocketcdnStatus.php';

	protected $cdn_names;
	protected $home_url = 'http://example.org';

	protected static $transients = [
		'rocketcdn_status' => null,
	];

	public static function setUpBeforeClass() {
		static::$use_settings_trait = true;
		parent::setUpBeforeClass();

		update_option( 'date_format', 'Y-m-d' );
	}

	public function setUp() {
		parent::setUp();

		set_current_screen( 'settings_page_wprocket' );
		add_filter( 'home_url', [ $this, 'home_url_cb' ] );
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'home_url', [ $this, 'home_url_cb' ] );
		set_current_screen( 'front' );
		delete_transient( 'rocketcdn_status' );
	}

	/**
	 * @dataProvider providerTestData
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

	public function home_url_cb() {
		return $this->home_url;
	}

	public function cdn_names_cb() {
		return $this->cdn_names;
	}

	public function return_empty_string() {
		return '';
	}
}
