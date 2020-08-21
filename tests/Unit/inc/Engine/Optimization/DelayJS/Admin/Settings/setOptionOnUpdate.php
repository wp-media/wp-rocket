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
	private $defaults = [
		'getbutton.io',
		'//a.omappapi.com/app/js/api.min.js',
		'feedbackcompany.com/includes/widgets/feedback-company-widget.min.js',
		'snap.licdn.com/li.lms-analytics/insight.min.js',
		'static.ads-twitter.com/uwt.js',
		'platform.twitter.com/widgets.js',
		'twq(',
		'/sdk.js#xfbml',
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
		'pinit.js',
		'/gtag/js',
		'gtag(',
		'/gtm.js',
		'/gtm-',
		'fbevents.js',
		'fbq(',
		'google-analytics.com/analytics.js',
		'ga( \'',
		'ga(\'',
		'adsbygoogle',
		'ShopifyBuy',
		'widget.trustpilot.com',
		'ft.sdk.min.js',
		'apps.elfsight.com/p/platform.js',
		'livechatinc.com/tracking.js',
		'LiveChatWidget',
		'/busting/facebook-tracking/',
	];

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $old_version, $valid_version ) {
		$options_data = Mockery::mock( Options_Data::class );
		$settings     = new Settings( $options_data );
		$options      = [
			'delay_js'         => 0,
			'delay_js_scripts' => $this->defaults,
		];

		if ( $valid_version ) {
			Functions\when( 'get_option' )->justReturn( [] );
			Functions\expect( 'update_option' )
				->with( 'wp_rocket_settings', $options )
				->once();
		} else {
			Functions\expect( 'update_option' )
				->with( 'wp_rocket_settings', $options )
				->never();
		}

		$settings->set_option_on_update( $old_version );

	}

}
