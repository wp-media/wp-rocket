<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\ImagifySubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers Imagify_Subscriber::get_basename
 * @group ThirdParty
 * @group Webp
 */
class TestGetBasename extends TestCase {
	/**
	 * Test Imagify_Subscriber->get_basename() should return a plugin basename when Imagify not enabled.
	 */
	public function testShouldReturnBasenameWhenImagifyNotEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		$expected = 'imagify/imagify.php';
		$basename = $subscriber->get_basename();

		$this->assertSame( $expected, $basename );
	}

	/**
	 * Test Imagify_Subscriber->get_basename() should return a plugin basename when Imagify is enabled.
	 */
	public function testShouldReturnBasenameWhenImagifyIsEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );
		$expected    = 'imagify/imagify.php';

		define( 'IMAGIFY_FILE', '/path/to/' . $expected );

		Functions\expect( 'plugin_basename' )
			->once()
			->andReturn( $expected );

		$basename = $subscriber->get_basename();

		$this->assertSame( $expected, $basename );
	}
}
