<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\Imagify_Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber::get_basename
 * @group  ThirdParty
 * @group  Webp
 */
class Test_GetBasename extends TestCase {

	public function testShouldReturnBasenameWhenImagifyNotEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
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

	public function testShouldReturnBasenameWhenImagifyIsEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
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
