<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Subscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Admin\Settings\Page;
use WP_Rocket\Engine\Admin\Settings\Subscriber;
use WP_Rocket\Tests\StubTrait;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Settings\Settings;
use WP_Rocket\Tests\Unit\TestCase;
use \Imagify_Partner;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Subscriber::add_imagify_page
 * @group  Admin
 * @group  Settings
 */
class Test_AddImagifyPage extends TestCase {
	use StubTrait;

	private $saved_white_label;

	/*$this->options = Mockery::mock( Options_Data::class );
	$this->options->shouldReceive( 'get' )
	->withAnyArgs();*/

	public function setUp(): void {
		parent::setUp();
		$this->saved_white_label = $this->white_label;
	}

	public function tearDown(): void {
		parent::tearDown();
		$this->white_label = $this->saved_white_label;
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddImagifyPage( $config, $expected ) {
		$page            = Mockery::mock( Page::class );
		$subscriber      = new Subscriber( $page );
		$imagify_partner = Mockery::mock( Imagify_Partner::class );

		Functions\expect( 'get_imagify_option' )->with( 'api_key' )->andReturn( $config['license'] );

		/*$imagify_partner->shouldReceive( 'has_imagify_api_key' )
		              ->andReturn($config['license']);*/
		$this->white_label = $config['white_label'];

		$actual = $subscriber->add_imagify_page( [] );
		$this->assertSame( $expected, $actual );
	}

}
