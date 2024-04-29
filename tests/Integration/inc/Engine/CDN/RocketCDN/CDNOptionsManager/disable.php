<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\CDNOptionsManager;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager::disable
 * @uses   \WP_Rocket\Admin\Options_Data::set
 * @uses   \WP_Rocket\Admin\Options::set
 * @uses   \WP_Rocket\Admin\Options::get_option_name
 * @uses   ::rocket_clean_domain
 * @uses  ::get_rocket_i18n_home_url
 * @uses  ::get_rocket_i18n_to_preserve
 * @uses  ::get_rocket_i18n_uri
 * @uses  ::get_rocket_parse_url
 * @uses  ::rocket_get_constant
 * @uses  ::rocket_rrmdir
 * @uses  ::_rocket_get_cache_dirs
 *
 * @group  RocketCDN
 * @group  CDNOptionsManager
 */
class Test_Disable extends TestCase {
	protected $path_to_test_data = '/inc/Engine/CDN/RocketCDN/CDNOptionsManager/disable.php';

	public function set_up() {
		parent::set_up();

		add_option( 'rocketcdn_user_token', '123456' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDisableCDNOptions( $expected ) {
		$this->dumpResults = isset( $expected['dump_results'] ) ? $expected['dump_results'] : false;
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		// Run it.
		$this->getCDNOptionsManager()->disable();

		// Check the settings.
		$this->assertSettings( $expected );

		// Check the option and transient are deleted.
		$this->assertFalse( get_option( 'rocketcdn_user_token' ) );
		$this->assertFalse( get_transient( 'rocketcdn_status' ) );

		// Check the cache.
		$this->assertCacheDeleted( $expected );
	}
}
