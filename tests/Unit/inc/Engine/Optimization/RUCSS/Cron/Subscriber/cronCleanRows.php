<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Cron\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Cron\Subscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Cron\Subscriber::cron_clean_rows
 *
 * @group  RUCSS
 */
class Test_CronCleanRows extends TestCase {
	private $database;
	private $usedCSS;
	private $subscriber;

	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		Functions\when('current_time')->justReturn('current_date');
	}

	public function setUp() : void {
		parent::setUp();

		$this->database   = Mockery::mock( Database::class );
		$this->usedCSS    = Mockery::mock( UsedCSS::class );
		$this->subscriber = new Subscriber( $this->usedCSS, $this->database );


	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config ) {
		Functions\expect('apply_filters')
			->with( 'rocket_rucss_deletion_enabled', true )
			->andReturn( $config['deletion_activated'] );

		if ( $config['deletion_activated'] ) {
			$this->database->expects()->delete_old_used_css()->once();
		} else {
			$this->database->expects()->delete_old_used_css()->never();
		}

		$this->subscriber->cron_clean_rows();
	}
}
