<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::rocketcdn_field
 *
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 * @uses   \WP_Rocket\Admin\Options_Data::get
 * @uses   ::rocket_has_constant
 * @uses   \WP_Rocket\Engine\Admin\Beacon\Beacon::get_suggest
 *
 * @group  RocketCDN
 * @group  AdminOnly
 * @group  RocketCDNAdminPage
 */
class Test_RocketcdnField extends TestCase {

	public function setUp() {
		parent::setUp();

		add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'cdn_names_cb' ] );
	}

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'cdn_names_cb' ] );

		parent::tearDown();

		delete_transient( 'rocketcdn_status' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddRocketCdnFields( $cdn_names, $rocketcdn_status, $expected_cdn_cnames ) {
		$this->cdn_names = $cdn_names;

		set_transient( 'rocketcdn_status', $rocketcdn_status, MINUTE_IN_SECONDS );

		$expected              = $this->config['fields'];
		$expected['cdn_cnames'] = $expected_cdn_cnames;

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_cdn_settings_fields', $this->config['fields'] )
		);
	}
}
