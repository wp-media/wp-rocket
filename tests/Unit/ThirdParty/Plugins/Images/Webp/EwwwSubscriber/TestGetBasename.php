<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\EwwwSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers EWWW_Subscriber::get_basename
 * @group ThirdParty
 * @group Webp
 */
class TestGetBasename extends TestCase {
	/**
	 * Test EWWW_Subscriber->get_basename() should return a plugin basename when EWWW not enabled.
	 */
	public function testShouldReturnBasenameWhenEwwwNotEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new EWWW_Subscriber( $optionsData );

		$expected = 'ewww-image-optimizer/ewww-image-optimizer.php';
		$basename = $subscriber->get_basename();

		$this->assertSame( $expected, $basename );
	}

	/**
	 * Test EWWW_Subscriber->get_basename() should return a plugin basename when EWWW is enabled.
	 */
	public function testShouldReturnBasenameWhenEwwwIsEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new EWWW_Subscriber( $optionsData );
		$expected    = 'ewww-image-optimizer/ewww-image-optimizer.php';

		define( 'EWWW_IMAGE_OPTIMIZER_PLUGIN_FILE', '/path/to/' . $expected );

		Functions\expect( 'plugin_basename' )
			->once()
			->andReturn( $expected );

		$basename = $subscriber->get_basename();

		$this->assertSame( $expected, $basename );
	}
}
