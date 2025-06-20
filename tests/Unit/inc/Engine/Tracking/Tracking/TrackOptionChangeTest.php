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
 * @group Tracking
 */
class TrackOptionChangeTest extends TestCase {
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

		$this->stubWpParseUrl();
	}

	/**
	 * @dataProvider configTestData
	 *
	 * @return void
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		$this->optin->shouldReceive( 'is_enabled' )
			->once()
			->andReturn( $config['optin_enabled'] );

		Functions\when( 'get_site_url' )->justReturn( 'http://example.org' );

		if ( ! $expected ) {
			$this->mixpanel->shouldNotReceive( 'track' );
		} else {
			$this->mixpanel->shouldReceive( 'track' )
				->once()
				->with(
					'WPM Option Changed',
					[
						'brand'          => 'WP Media',
						'product'        => 'WP Rocket',
						'context'        => 'wp_plugin',
						'option_name'    => 'auto_preload_fonts',
						'previous_value' => $config['old_value']['auto_preload_fonts'],
						'new_value'      => $config['value']['auto_preload_fonts'],
					]
				);
		}

		$this->tracking->track_option_change( $config['old_value'], $config['value'] );
	}
}
