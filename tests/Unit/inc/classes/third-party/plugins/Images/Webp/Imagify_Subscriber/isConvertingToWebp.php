<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\Imagify_Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber::is_converting_to_webp
 * @group  ThirdParty
 * @group  Webp
 */
class Test_IsConvertingToWebp extends TestCase {

	public function testShouldReturnFalseWhenImagifyOptionNotEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		$this->assertFalse( $subscriber->is_converting_to_webp() );

		Functions\expect( 'get_imagify_option' )
			->twice()
			->andReturnValues( [ true, false ] );

		$this->assertFalse( $subscriber->is_converting_to_webp() );
	}

	public function testShouldReturnTrueWhenImagifyOptionIsEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		Functions\expect( 'get_imagify_option' )
			->once()
			->andReturn( true );

		$this->assertTrue( $subscriber->is_converting_to_webp() );
	}
}
