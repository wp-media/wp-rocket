<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\GoogleFonts;

use Mockery;
use WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Settings;
use WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Subscriber;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Subscriber::display_google_fonts_enabler
 *
 * @group  GoogleFonts
 */
class Test_DisplayGoogleFontsEnabler extends TestCase {

	public function testShouldCallSettingsFontsEnabler() {
		$settings   = Mockery::mock( Settings::class );
		$settings->shouldReceive('display_google_fonts_enabler')->once();

		$subscriber = new Subscriber( $settings );
		$subscriber->display_google_fonts_enabler();
	}
}
