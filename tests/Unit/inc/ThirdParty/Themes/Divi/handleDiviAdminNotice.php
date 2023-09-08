<?php
namespace WP_Rocket\tests\Unit\inc\ThirdParty\Themes\Divi;

use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Divi;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Divi::handle_divi_admin_notice
 *
 * @group  ThirdParty
 * @group  Divi
 */
class Test_HandleDiviAdminNotice extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testHandleAdminNotice( $config, $expected ) {
		$options_api  = Mockery::mock( 'WP_Rocket\Admin\Options' );
		$options      = Mockery::mock( 'WP_Rocket\Admin\Options_Data' );
		$delayjs_html = Mockery::mock( 'WP_Rocket\Engine\Optimization\DelayJS\HTML' );
		$used_css     = Mockery::mock( UsedCSS::class );

		if ( isset( $config['rucss_option'] ) ) {
			$options->shouldReceive( 'get' )
			        ->with( 'remove_unused_css', false )
			        ->andReturn( $config['rucss_option'] );
		}

		if ( isset( $config['capability'] ) ) {
			Functions\expect( 'current_user_can' )
				->once()
				->with( 'rocket_manage_options' )
				->andReturn( $config['capability'] );
		}

		if ( isset( $config['transient_return'] ) ) {
			Functions\expect( 'get_transient' )
				->once()
				->with( 'rocket_divi_notice' )
				->andReturn( $config['transient_return'] );
		}

		if ( $expected['notice_show'] ) {
			Functions\expect( 'rocket_notice_html' )->once()->with( $expected['notice_details'] )->andReturnNull();
		} else {
			Functions\expect( 'rocket_notice_html' )->never()->andReturnNull();
		}

		$divi = new Divi( $options_api, $options, $delayjs_html, $used_css );

		$divi->handle_divi_admin_notice();
	}
}
