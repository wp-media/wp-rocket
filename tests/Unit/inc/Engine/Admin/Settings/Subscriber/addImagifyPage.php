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

	public function setUp(): void {
		parent::setUp();
		Functions\stubTranslationFunctions();
		$this->saved_white_label = $this->white_label;
	}

	public function tearDown(): void {
		parent::tearDown();
		$this->white_label = $this->saved_white_label;
	}

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/Imagify_Partner.php';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddImagifyPage( $config, $expected ) {
		$this->constants['test_api_key'] = $config['license'];

		$page            = Mockery::mock( Page::class );
		$subscriber      = new Subscriber( $page );

		$this->white_label = $config['white_label'];

		$actual = $subscriber->add_imagify_page( [] );
		$this->assertSame( $expected, $actual );
	}

}
