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

		Functions\expect( 'rocket_has_constant' )
			->once()
			->with( 'IMAGIFY_FILE' )
			->andReturn( false );
		Functions\expect( 'rocket_get_constant' )
			->with( 'IMAGIFY_FILE' )
			->never();

		$this->assertSame( 'imagify/imagify.php', $subscriber->get_basename() );
	}

	/**
	 * Test Imagify_Subscriber->get_basename() should return a plugin basename when Imagify is enabled.
	 */
	public function testShouldReturnBasenameWhenImagifyIsEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );
		$expected    = 'some-file.php';

		Functions\expect( 'rocket_has_constant' )
			->once()
			->with( 'IMAGIFY_FILE' )
			->andReturn( true );
		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'IMAGIFY_FILE' )
			->andReturn( "/path/to/{$expected}" );

		Functions\expect( 'plugin_basename' )
			->once()
			->andReturn( $expected );

		$this->assertSame( $expected, $subscriber->get_basename() );
	}
}
