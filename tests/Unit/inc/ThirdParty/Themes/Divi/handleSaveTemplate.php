<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Divi;

use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Divi;
use Brain\Monkey\Functions;
use wpdb;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Divi::handle_save_template
 *
 * @group  ThirdParty
 * @group  Divi
 */
class Test_HandleSaveTemplate extends TestCase {

	private $wpdb;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/wpdb.php';
	}

	protected function setUp(): void {
		parent::setUp();

		$GLOBALS['wpdb'] = $this->wpdb = new wpdb();
	}

	protected function tearDown(): void {
		unset( $GLOBALS['wpdb'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testHandleSaveTemplate( $config, $expected ) {
		$options_api = Mockery::mock( 'WP_Rocket\Admin\Options' );
		$options     = Mockery::mock( 'WP_Rocket\Admin\Options_Data' );
		$delayjs_html     = Mockery::mock( 'WP_Rocket\Engine\Optimization\DelayJS\HTML' );

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

		if ( isset( $config['template_post'] ) ) {
			Functions\when( 'get_post_type' )
				->justReturn( $config['template_post']['post_type'] );
		}

		if ( isset( $config['layout_post'] ) ) {
			$this->wpdb->setTableRows( [ 2 ] );

			if ( isset( $config['layout_post']['post_status'] ) ) {
				Functions\when( 'get_post_status' )
					->justReturn( $config['layout_post']['post_status'] );
			}

			Functions\expect( 'get_post_meta' )
				->once()
				->andReturn( $config['layout_post']['metas'] ?? [] );

		}

		if ( $expected['transient_set'] && ! $config['transient_return'] ) {
			Functions\expect( 'set_transient' )->once()->with( 'rocket_divi_notice', true )->andReturnNull();
		} else {
			Functions\expect( 'set_transient' )->with( 'rocket_divi_notice', true )->never();
		}

		$divi = new Divi( $options_api, $options, $delayjs_html );

		$divi->handle_save_template( 1 );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'handleSaveTemplate' )['test_data'];
	}
}
