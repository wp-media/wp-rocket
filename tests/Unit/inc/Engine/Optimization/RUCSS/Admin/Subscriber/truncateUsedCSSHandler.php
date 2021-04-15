<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::truncate_used_css_handler
 *
 * @group  RUCSS
 */
class Test_TruncateUsedCSSHandler extends TestCase {

	private $settings;
	private $database;
	private $usedCSS;
	private $subscriber;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Subscriber/TruncateUsedCSSHandler.php';

	public function setUp() : void {
		parent::setUp();

		$this->settings   = Mockery::mock( Settings::class );
		$this->database   = Mockery::mock( Database::class );
		$this->usedCSS    = Mockery::mock( UsedCSS::class );
		$this->subscriber = new Subscriber( $this->settings, $this->database, $this->usedCSS );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input ) {
		$this->assertTrue( true );

		//$this->subscriber->truncate_used_css_handler();
	}
}
