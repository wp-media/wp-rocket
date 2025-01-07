<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\EwwwSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber::get_basename
 * @group  ThirdParty
 * @group  Webp
 */
class Test_GetBasename extends TestCase {

	private function getSubscriber() {
		$optionsData = Mockery::mock( Options_Data::class );

		return new EWWW_Subscriber( $optionsData );
	}

	public function testShouldReturnBasenameWhenEwwwNotEnabled() {
		$subscriber = $this->getSubscriber();

		Functions\expect( 'rocket_has_constant' )
			->once()
			->with( 'EWWW_IMAGE_OPTIMIZER_PLUGIN_FILE' )
			->andReturn( false );
		Functions\expect( 'rocket_get_constant' )
			->with( 'EWWW_IMAGE_OPTIMIZER_PLUGIN_FILE' )
			->never();

		$this->assertSame( 'ewww-image-optimizer/ewww-image-optimizer.php', $subscriber->get_basename() );
	}

	/**
	 * Test EWWW_Subscriber->get_basename() should return a plugin basename when EWWW is enabled.
	 */
	public function testShouldReturnBasenameWhenEwwwIsEnabled() {
		$subscriber = $this->getSubscriber();
		$expected   = 'some-basename.php';

		Functions\expect( 'rocket_has_constant' )
			->once()
			->with( 'EWWW_IMAGE_OPTIMIZER_PLUGIN_FILE' )
			->andReturn( true );
		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'EWWW_IMAGE_OPTIMIZER_PLUGIN_FILE' )
			->andReturn( "/path/to/{$expected}" );

		Functions\expect( 'plugin_basename' )
			->once()
			->andReturn( $expected );

		$this->assertSame( $expected, $subscriber->get_basename() );
	}
}
