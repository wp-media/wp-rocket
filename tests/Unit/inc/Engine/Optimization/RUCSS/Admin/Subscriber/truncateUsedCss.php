<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Queue;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::truncate_used_css
 *
 * @group  RUCSS
 */
class Test_TruncateUsedCss extends TestCase {

	private $settings;
	private $database;
	private $usedCSS;
	private $subscriber;

	protected function setUp(): void
	{
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
		$this->configureDeleteUsedCssRow($config);

		$this->subscriber->truncate_used_css();
	}

	protected function configureHook($config) {
		if(! array_key_exists('is_disabled', $config)) {
			return;
		}
		Functions\expect('apply_filters')->with( 'rocket_rucss_deletion_activated' )->andReturn($config['is_disabled']);
	}

	protected function configureDeleteUsedCssRow($config) {
		if(! array_key_exists('delete_used_css_row', $config)) {
			return;
		}
		$this->usedCSS->expects()->delete_all_used_css();

		$this->usedCSS->expects()->get_not_completed_count()->andReturn($config['used_css_count']);
		if(0 < $config['used_css_count']) {
			$this->database->expects()->remove_all_completed_rows();
		} else {
			$this->database->expects()->truncate_used_css_table();
		}

		Functions\expect('do_action')->with('rocket_after_clean_used_css');

		Functions\expect('set_transient')->with('rocket_rucss_processing', time() + 90, 1.5 * MINUTE_IN_SECONDS);

		Functions\expect('rocket_renew_box')->with('rucss_success_notice');

	}
}
