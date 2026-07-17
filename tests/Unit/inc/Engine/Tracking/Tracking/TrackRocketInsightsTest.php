<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Tracking\Tracking;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\Mixpanel\Optin;
use WPMedia\Mixpanel\TrackingPlugin as MixpanelTracking;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Tracking\Tracking;
use WP_Rocket\Tests\Unit\TestCase;
use stdClass;

/**
 * Test class covering the track_rocket_insights_test method.
 *
 * @group Tracking
 * @group RocketInsights
 */
class TrackRocketInsightsTest extends TestCase {
	/**
	 * Optin mock instance.
	 *
	 * @var Mockery\MockInterface
	 */
	private $optin;

	/**
	 * Mixpanel mock instance.
	 *
	 * @var Mockery\MockInterface
	 */
	private $mixpanel;

	/**
	 * Options mock instance.
	 *
	 * @var Mockery\MockInterface
	 */
	private $options;

	/**
	 * User mock instance.
	 *
	 * @var Mockery\MockInterface
	 */
	private $user;

	/**
	 * Tracking instance under test.
	 *
	 * @var Tracking
	 */
	private $tracking;

	/**
	 * Set up the test.
	 *
	 * @return void
	 */
	protected function set_up(): void {
		parent::set_up();

		$this->optin    = Mockery::mock( Optin::class );
		$this->mixpanel = Mockery::mock( MixpanelTracking::class );
		$this->options  = Mockery::mock( Options_Data::class );
		$this->user     = Mockery::mock( User::class );

		$this->options->shouldReceive( 'get' )
			->with( 'consumer_email', '' )
			->andReturn( '' );
		$this->mixpanel->shouldReceive( 'identify' )
			->once()
			->with( '' );
		$this->optin->shouldReceive( 'can_track' )
			->once()
			->andReturn( false );

		$this->tracking = new Tracking(
			$this->options,
			$this->optin,
			$this->mixpanel,
			$this->user,
			'path/to/templates'
		);
	}

	/**
	 * Test should do expected.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config Test configuration.
	 * @param array $expected Expected results.
	 *
	 * @return void
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		$this->optin->shouldReceive( 'can_track' )
			->once()
			->andReturn( $config['can_track'] );

		// Create a simple object to simulate RocketInsights row.
		$row_details         = new stdClass();
		$row_details->url    = $config['row_details']['url'];
		$row_details->status = $config['row_details']['status'];
		$row_details->score  = $config['row_details']['score'];
		$row_details->data   = $config['row_details']['data'];

		// Mock home_url() for Utils::is_home() check.
		if ( isset( $config['home_url'] ) ) {
			Functions\when( 'home_url' )->justReturn( $config['home_url'] );
		}

		if ( ! $expected['track_called'] ) {
			$this->mixpanel->shouldNotReceive( 'track_direct' );
		} else {
			$this->mixpanel->shouldReceive( 'track_direct' )
				->once()
				->with(
					'Rocket Insights Performance Test',
					Mockery::on(
						function ( $actual_data ) use ( $config, $expected ) {
							// Check all base fields.
							if ( 'wp_plugin' !== $actual_data['context'] ) {
								return false;
							}
							if ( $config['row_details']['status'] !== $actual_data['status'] ) {
								return false;
							}
							if ( $config['row_details']['score'] !== $actual_data['score'] ) {
								return false;
							}
							if ( $config['row_details']['data']['is_retest'] !== $actual_data['retest'] ) {
								return false;
							}
							if ( $config['plan'] !== $actual_data['plan_type'] ) {
								return false;
							}
							if ( $config['row_details']['data']['source'] !== $actual_data['source'] ) {
								return false;
							}

							// Check ri_page_identifier presence based on expectation.
				if ( isset( $expected['ri_page_identifier'] ) ) {
								if ( ! isset( $actual_data['ri_page_identifier'] ) || $expected['ri_page_identifier'] !== $actual_data['ri_page_identifier'] ) {
									return false;
								}
							} elseif ( isset( $actual_data['ri_page_identifier'] ) ) {
								// Should NOT have ri_page_identifier.
								return false;
							}

							return true;
						}
					)
				);
		}

		$this->tracking->track_rocket_insights_test(
			$row_details,
			$config['job_details'],
			$config['plan']
		);
	}
}
