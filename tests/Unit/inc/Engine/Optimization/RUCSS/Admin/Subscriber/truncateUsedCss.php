<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Brain\Monkey\{Actions,Functions};
use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\{Database,Settings,Subscriber};
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Common\JobManager\Queue\Queue;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::truncate_used_css
 *
 * @group RUCSS
 */
class Test_TruncateUsedCss extends TestCase {
	private $settings;
	private $database;
	private $usedCSS;
	private $subscriber;

	protected function setUp(): void {
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
		Functions\when('home_url')->justReturn($config['home']);
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
		Functions\expect( 'rocket_apply_filter_and_deprecated' )
			->with( 'rocket_saas_deletion_enabled', [ true ], '3.16', 'rocket_rucss_deletion_enabled' )
			->andReturn( $config['is_disabled'] );
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
		Actions\expectDone('rocket_after_clean_used_css');

		Functions\expect('set_transient')->with('rocket_rucss_processing', time() + 90, 1.5 * MINUTE_IN_SECONDS);

		Functions\expect('rocket_renew_box')->with('rucss_success_notice');
	}
}
