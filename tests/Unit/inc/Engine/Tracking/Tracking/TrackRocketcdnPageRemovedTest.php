<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Tracking\Tracking;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\Mixpanel\Optin;
use WPMedia\Mixpanel\TrackingPlugin as MixpanelTracking;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Tracking\Tracking;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Tracking\Tracking::track_rocketcdn_page_removed
 * @group  Tracking
 * @group  RocketCDN
 */
class TrackRocketcdnPageRemovedTest extends TestCase {
	private $optin;
	private $mixpanel;
	private $options;
	private $tracking;

	protected function set_up(): void {
		parent::set_up();

		$this->optin    = Mockery::mock( Optin::class );
		$this->mixpanel = Mockery::mock( MixpanelTracking::class );
		$this->options  = Mockery::mock( Options_Data::class );

		$this->options->shouldReceive( 'get' )
			->with( 'consumer_email', '' )
			->andReturn( '' );
		$this->mixpanel->shouldReceive( 'identify' )
			->once()
			->with( '' );

		$this->tracking = new Tracking(
			$this->options,
			$this->optin,
			$this->mixpanel,
			'path/to/templates'
		);

		Functions\when( 'wp_parse_args' )->alias(
			function ( $args, $defaults = [] ) {
				return array_merge( (array) $defaults, (array) $args );
			}
		);
		Functions\when( 'rocket_apply_filter_and_deprecated' )->alias(
			function ( $new_hook, $args, $version, $old_hook ) {
				return $args[0];
			}
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		$this->optin->shouldReceive( 'can_track' )
			->times( $expected['can_track_count'] )
			->andReturn( $config['can_track'] );

		if ( isset( $config['home_url'] ) ) {
			Functions\when( 'home_url' )->justReturn( $config['home_url'] );
		}

		if ( ! $expected['track_called'] ) {
			$this->mixpanel->shouldNotReceive( 'track' );
		} else {
			$this->mixpanel->shouldReceive( 'track' )
				->once()
				->with(
					'Button Clicked',
					[
						'context'     => 'wp_plugin',
						'button'      => 'rocket cdn remove page',
						'is_homepage' => $expected['is_homepage'],
						'pages_count' => $config['pages_count'],
					]
				);
		}

		$this->tracking->track_rocketcdn_page_removed( $config['url'], $config['pages_count'] );
	}
}
