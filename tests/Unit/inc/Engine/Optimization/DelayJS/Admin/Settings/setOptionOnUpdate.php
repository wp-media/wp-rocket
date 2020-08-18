<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings::set_option_on_update
 *
 * @group  DelayJS
 */
class Test_SetOptionOnUpdate extends TestCase{

	private $defaults = ['getbutton.io',
		'//a.omappapi.com/app/js/api.min.js',
		'feedbackcompany.com/includes/widgets/feedback-company-widget.min.js',
		'snap.licdn.com/li.lms-analytics/insight.min.js',
		'static.ads-twitter.com/uwt.js',
		'platform.twitter.com/widgets.js',
		'connect.facebook.net/en_GB/sdk.js',
		'connect.facebook.net/en_US/sdk.js',
		'static.leadpages.net/leadbars/current/embed.js',
		'translate.google.com/translate_a/element.js',
		'widget.manychat.com',
		'google.com/recaptcha/api.js',
		'xfbml.customerchat.js',
		'static.hotjar.com/c/hotjar-',
		'smartsuppchat.com/loader.js',
		'grecaptcha.execute',
		'Tawk_API',
		'shareaholic',
		'sharethis',
		'simple-share-buttons-adder',
		'addtoany',
		'font-awesome',
		'wpdiscuz',
		'cookie-law-info',
		'cookie-notice',
		'pinit.js',
		'gtag',
		'gtm',
		'fbevents.js',
		'fbq(',
		];

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $old_version, $valid_version ) {
		$options_data = Mockery::mock( Options_Data::class );
		$settings     = new Settings( $options_data );
		$options      = [ 'delay_js' => 0 ];

		if ( $valid_version ) {
			$options_data->shouldReceive( 'set' )
			             ->with( 'delay_js', 0 )
			             ->once();
			$options_data->shouldReceive( 'set' )
			             ->with( 'delay_js_scripts', $this->defaults )
			             ->once();
			$options_data->shouldReceive( 'get_options' )
				->once()
				->andReturn( $options );
			Functions\expect( 'update_option' )
				->with( 'wp_rocket_settings', $options )
				->once();
		} else {
			$options_data->shouldReceive( 'set' )
			             ->with( 'delay_js', 0 )
			             ->never();
			$options_data->shouldReceive( 'set' )
			             ->with( 'delay_js_scripts', $this->defaults )
			             ->never();
			$options_data->shouldReceive( 'get_options' )
				->never();
			Functions\expect( 'update_option' )
				->with( 'wp_rocket_settings', $options )
				->never();
		}

		$settings->set_option_on_update( $old_version );

	}

}
