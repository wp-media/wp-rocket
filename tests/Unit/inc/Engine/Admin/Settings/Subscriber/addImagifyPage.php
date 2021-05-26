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

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Subscriber::add_imagify_page
 * @group  Admin
 * @group  Settings
 */
class Test_AddImagifyPage extends TestCase {
	use StubTrait;

	private $options;
	private $settings;

	/*$this->options = Mockery::mock( Options_Data::class );
	$this->options->shouldReceive( 'get' )
	->withAnyArgs();*/

	public function setUp(): void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddImagifyPage( $config, $expected ) {
		$page       = Mockery::mock( Page::class );
		$subscriber = new Subscriber( $page );
		$actual     = $subscriber->add_imagify_page( [] );
		$this->assertSame($expected,$actual);
	}

}
