<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimus;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Optimus_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Optimus_Subscriber::is_activated
 *
 * @group Optimus
 * @group ThirdParty
 * @group Webp
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests Optimus_Subscriber::is_activated() against the presence/absence of OPTIMUS_FILE.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( $config['define_optimus_file'] ) {
			define( 'OPTIMUS_FILE', '/optimus/optimus.php' );
		}

		$this->assertSame( $expected, Optimus_Subscriber::is_activated() );
	}
}
