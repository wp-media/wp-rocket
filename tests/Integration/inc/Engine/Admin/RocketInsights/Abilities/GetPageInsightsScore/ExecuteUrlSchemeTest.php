<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Abilities\GetPageInsightsScore;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering wp-rocket/get-page-insights-score URL scheme handling through the Abilities API.
 *
 * @group RocketInsights
 * @group Abilities
 * @group AdminOnly
 */
class ExecuteUrlSchemeTest extends TestCase {
	use DBTrait;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installPerformanceMonitoringTable();
	}

	public static function tear_down_after_class() {
		self::uninstallPerformanceMonitoringTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		if ( version_compare( $GLOBALS['wp_version'], '6.9', '<' ) ) {
			$this->markTestSkipped( 'WordPress 6.9+ required for Abilities API' );
		}

		self::truncatePerformanceMonitoringTable();

		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_manage_options' );
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
	}

	public function tear_down() {
		self::truncatePerformanceMonitoringTable();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldFindStoredRowWhateverTheInputScheme( $home_scheme, $stored_url, $input_url, $exists ) {
		add_filter(
			'home_url',
			function ( $url ) use ( $home_scheme ) {
				return set_url_scheme( $url, $home_scheme );
			}
		);

		$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );

		self::addPerformanceMonitoring(
			[
				'url'       => str_replace( '{host}', $host, $stored_url ),
				'title'     => 'Page',
				'is_mobile' => false,
				'job_id'    => 'test_scheme',
				'status'    => 'completed',
				'score'     => 85,
				'data'      => '{"status":"complete","data":{"data":{"performance_score":85}}}',
			]
		);

		$ability = wp_get_ability( 'wp-rocket/get-page-insights-score' );

		$this->assertNotNull( $ability, 'Ability should be registered' );

		$result = $ability->execute( [ 'url' => str_replace( '{host}', $host, $input_url ) ] );

		$this->assertSame( $exists, $result['exists'] );
	}
}
