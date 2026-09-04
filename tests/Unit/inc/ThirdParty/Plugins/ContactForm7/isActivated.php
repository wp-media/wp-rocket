<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\ContactForm7;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\ContactForm7;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\ContactForm7::is_activated
 *
 * @group ContactForm7
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests ContactForm7::is_activated() against WPCF7_VERSION presence/version.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['wpcf7_version'] ) {
			define( 'WPCF7_VERSION', $config['wpcf7_version'] );
		}

		$this->assertSame( $expected, ContactForm7::is_activated() );
	}
}
