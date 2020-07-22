<?php

use WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Settings;
use WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Subscriber;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Subscriber::enable_google_fonts
 *
 * @group  GoogleFonts
 */
class Test_EnableGoogleFonts extends TestCase {

	public function testShouldCallEnableGoogleFonts() {
		$settings   = Mockery::mock( Settings::class );
		$settings->shouldReceive('enable_google_fonts')->once();

		$subscriber = new Subscriber( $settings );
		$subscriber->enable_google_fonts();
	}
}
