<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS::add_item
 *
 * @group RUCSS
 */
class Test_AddItem extends TestCase {
	private $query;

	public function set_up() {
		parent::set_up();

		self::installUsedCssTable();

		$this->query = apply_filters( 'rocket_container', null )->get( 'rucss_used_css_query' );
	}

	public function tear_down() {
		self::uninstallUsedCssTable();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldStoreTheHashAndEnforceTheUniqueKey( $config, $expected ) {
		global $wpdb;

		$results  = [];
		$previous = $wpdb->suppress_errors( true );

		foreach ( $config['items'] as list( $url, $is_mobile ) ) {
			$results[] = (bool) $this->query->add_item(
				[
					'url'       => $url,
					'is_mobile' => $is_mobile,
					'status'    => 'to-submit',
				]
			);
		}

		$wpdb->suppress_errors( $previous );

		$this->assertSame( $expected['results'], $results );
		$this->assertSame( $expected['rows'], (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wpr_rucss_used_css" ) );

		foreach ( $wpdb->get_results( "SELECT url, url_hash FROM {$wpdb->prefix}wpr_rucss_used_css" ) as $row ) {
			$this->assertSame( md5( $row->url ), $row->url_hash );
		}

		if ( null !== $config['job_id'] ) {
			$this->query->make_status_pending( $config['items'][0][0], $config['job_id'], 'EU', $config['items'][0][1] );

			$this->assertFalse( wp_cache_get( $config['job_id'], 'wpr_used_css-by-job_id' ) );
		}
	}
}
