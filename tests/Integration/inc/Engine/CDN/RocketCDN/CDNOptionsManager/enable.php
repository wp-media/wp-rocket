<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\CDNOptionsManager;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager::enable
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
class Test_Enable extends TestCase {
	protected $path_to_test_data = '/inc/Engine/CDN/RocketCDN/CDNOptionsManager/enable.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldEnableCDNOptions( $expected ) {
		$this->dumpResults = isset( $expected['dump_results'] ) ? $expected['dump_results'] : false;
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		// Run it.
		$this->getCDNOptionsManager()->enable( 'https://rocketcdn.me' );

		// Check the settings.
		$this->assertSettings( $expected );

		// Check the transient was deleted.
		$this->assertFalse( get_transient( 'rocketcdn_status' ) );

		// Check the cache.
		$this->assertCacheDeleted( $expected );
	}
}
