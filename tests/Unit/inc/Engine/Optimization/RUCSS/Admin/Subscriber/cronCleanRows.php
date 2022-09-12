<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Queue;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::cron_clean_rows
 *
 * @group  RUCSS
 */
class Test_CronCleanRows extends \WP_Rocket\Tests\Unit\TestCase {

	private $settings;
	private $database;
	private $usedCSS;
	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->settings   = Mockery::mock( Settings::class );
		$this->database   = Mockery::mock( Database::class );
		$this->usedCSS    = Mockery::mock( UsedCSS::class );
		$this->subscriber = new Subscriber( $this->settings, $this->database, $this->usedCSS, Mockery::mock( Queue::class ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config) {
		$this->settings
			->shouldReceive( 'is_enabled' )
			->once()
			->andReturn( $config['remove_unused_css'] );
		$this->configureHook($config);
		$this->configureDeleteTables($config);
		$this->subscriber->cron_clean_rows();
	}

	protected function configureHook($config) {
		if(! array_key_exists('has_delay', $config)) {
			return;
		}
		Functions\expect('apply_filters')->with( 'rocket_rucss_css_delete_delay', 1 )->andReturn($config['delay']);
	}

	protected function configureDeleteTables($config) {
		if(! array_key_exists('deletion_activated', $config)) {
			return;
		}
		$this->database->expects()->delete_old_used_css($config['delay']);
		$this->database->expects()->delete_old_resources($config['delay']);
	}
}
