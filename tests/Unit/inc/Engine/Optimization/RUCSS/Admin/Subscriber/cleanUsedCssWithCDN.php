<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Queue;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::clean_used_css_with_cdn
 *
 * @uses   ::rocket_clean_domain
 *
 * @group  RUCSS
 */
class Test_CleanUsedCssWithCDN extends TestCase {

	private $settings;
	private $database;
	private $usedCSS;
	private $subscriber;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Subscriber/cleanUsedCssWithCDN.php';

	public function setUp() : void {
		parent::setUp();

		$this->settings    = Mockery::mock( Settings::class );
		$this->database    = Mockery::mock( Database::class );
		$this->usedCSS     = Mockery::mock( UsedCSS::class );
		$this->subscriber  = new Subscriber( $this->settings, $this->database, $this->usedCSS, Mockery::mock( Queue::class ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ) {
		if ( $expected['truncated'] ) {
			$this->usedCSS->shouldReceive( 'get_not_completed_count' )->once()->andReturn( $expected['not_completed_count'] );

			if ( $expected['not_completed_count'] > 0 ) {
				$this->usedCSS->shouldReceive( 'remove_all_completed_rows' )->once();
			} else {
				$this->database->shouldReceive( 'truncate_used_css_table' )->once();
			}

			Functions\expect( 'set_transient' )
				->once()
				->with(
					'rocket_rucss_processing',
					Mockery::type( 'int' ),
					60
				);

			Functions\expect( 'rocket_renew_box' )
				->once()
				->with( 'rucss_success_notice' );
		} else {
			$this->database
				->shouldReceive( 'truncate_used_css_table' )
				->never();

			Functions\expect( 'set_transient' )
				->never();
		}

		$this->subscriber->clean_used_css_with_cdn( $input['old_settings'], $input['settings'] );
	}
}
