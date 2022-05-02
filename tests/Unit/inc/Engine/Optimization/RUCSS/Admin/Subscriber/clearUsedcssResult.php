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
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::clear_usedcss_result
 *
 * @group  RUCSS
 */
class Test_ClearUsedcssResult extends TestCase {

	private $settings;
	private $database;
	private $usedCSS;
	private $subscriber;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Subscriber/clearUsedcssResult.php';

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
	public function testShouldDoExpected( $input, $expected ) {
		if ( isset( $input['cap'] ) ) {
			Functions\expect( 'current_user_can' )
				->once()
				->with( 'rocket_remove_unused_css' )
				->andReturn( $input['cap'] );
		}

		$this->settings->shouldReceive( 'is_enabled' )
			->andReturn( $input['enabled'] );

		if ( isset( $input['transient'] ) ) {
			Functions\expect( 'get_transient' )
				->once()
				->with( 'rocket_clear_usedcss_response' )
				->andReturn( $input['transient'] );
		}

		if ( $expected['show_notice'] ) {
			Functions\expect( 'delete_transient' )
				->once()
				->with( 'rocket_clear_usedcss_response' );

			Functions\expect( 'rocket_notice_html' )
				->once()
				->with( $input['transient'] );
		}


		$this->subscriber->clear_usedcss_result();
	}
}
