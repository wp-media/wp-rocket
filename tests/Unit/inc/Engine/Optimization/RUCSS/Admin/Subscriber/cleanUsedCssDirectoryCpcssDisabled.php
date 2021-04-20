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
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::clean_used_css_directory_cpcss_disabled
 *
 * @group  RUCSS
 */
class Test_CleanUsedCssDirectoryCpcssDisabled extends TestCase {

	private $settings;
	private $database;
	private $usedCSS;
	private $subscriber;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Subscriber/cleanUsedCssDirectoryCpcssDisabled.php';

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
	public function testShouldDoExpected( $input, $expected ) {
		if ( isset( $input['cap'] ) ) {
			Functions\expect( 'current_user_can' )
				->once()
				->with( 'rocket_manage_options' )
				->andReturn( $input['cap'] );
		}

		if ( isset( $input['remove_unused_css'] ) ) {
			$this->settings->shouldReceive( 'is_enabled' )->once()->andReturn( $input['remove_unused_css'] );
		}

		if ( $expected['cleaned'] ) {
			$this->usedCSS->shouldReceive( 'delete_all_used_css_files' )->once();
		}else{
			$this->usedCSS->shouldReceive( 'delete_all_used_css_files' )->never();
		}

		$this->subscriber->clean_used_css_directory_cpcss_disabled( $input['old_value'], $input['new_value'] );
	}
}
