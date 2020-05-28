<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\Admin;

use WP_Error;
use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\CriticalPath\Admin\Admin;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\AdminTrait;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Admin::cpcss_heartbeat
 *
 * @group  CriticalPath
 * @group  CriticalPathAdmin
 */
class Test_CpcssHeartbeat extends TestCase {
	use AdminTrait;

	protected static $mockCommonWpFunctionsInSetUp = true;
	protected        $wp_error;

	protected function setUp() {
		parent::setUp();

		Functions\expect( 'check_ajax_referer' )
				->once()
				->with( 'cpcss_heartbeat_nonce', '_nonce', true )
				->andReturn( true );

		$this->wp_error = Mockery::mock( WP_Error::class );

		$this->setUpMocks();

		$this->processor = Mockery::mock( ProcessorService::class );
		$this->admin     = new Admin(
			$this->options,
			$this->processor
		);
	}


	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRunCPCSSHeartbeat( $config, $expected ) {
		$expected['set_rocket_cpcss_generation_pending'] = isset( $expected['set_rocket_cpcss_generation_pending'] ) ? $expected['set_rocket_cpcss_generation_pending'] : [];

		$this->bailoutConditions( $config );
		$this->expectGetAndSetTransientPending( $config, $expected['set_rocket_cpcss_generation_pending'] );
		$this->expectProcessGenerate( $config );
		$this->expectHeartbeatNotice( $config );
		$this->expectGenerationComplete( $config, $expected );

		if ( $expected['bailout'] ) {
			$this->expectBailoutConditions( $expected );
		}

		$this->admin->cpcss_heartbeat();
	}

	private function bailoutConditions( $config ) {
		$this->options
				->shouldReceive( 'get' )
				->with( 'async_css', 0 )
				->andReturn( $config['options']['async_css'] );

		if ( isset( $config[ 'rocket_manage_options' ] ) ) {
			Functions\expect( 'current_user_can' )
					->once()
					->with( 'rocket_manage_options' )
					->andReturn( $config[ 'rocket_manage_options' ] );
		}

		if ( isset( $config[ 'rocket_regenerate_critical_css' ] ) ) {
			Functions\expect( 'current_user_can' )
					->once()
					->with( 'rocket_regenerate_critical_css' )
					->andReturn( $config[ 'rocket_regenerate_critical_css' ] );
		}
	}

	private function expectBailoutConditions( $expected ) {
		Functions\expect( 'get_transient' )
				->with( 'rocket_cpcss_generation_pending' )
				->never();
		Functions\expect( $expected[ 'json' ] )
				->once();
	}

	private function expectGetAndSetTransientPending( $config, $set_rocket_cpcss_generation_pending ) {
		if ( isset( $config['rocket_cpcss_generation_pending'] ) ) {
			Functions\expect( 'get_transient' )
				->once()
				->with( 'rocket_cpcss_generation_pending' )
				->andReturn( $config['rocket_cpcss_generation_pending'] );

			Functions\expect( 'set_transient' )
				->once()
				->with( 'rocket_cpcss_generation_pending', $set_rocket_cpcss_generation_pending, HOUR_IN_SECONDS );
		}
	}

	private function expectGenerationComplete( $config, $expected ) {
		if ( ! empty( $expected['generation_complete'] ) ) {
			if ( $config['rocket_critical_css_generation_process_running'] ) {
				$config['notice']['transient'] = isset( $config['notice']['transient'] ) ?
													$config['notice']['transient']
													:
													[
														'items'     => [],
														'generated' => 0,
														'total'     => count( $config['rocket_cpcss_generation_pending'] ),
													];
				Functions\expect( 'get_transient' )
					->with( 'rocket_critical_css_generation_process_running' )
					->andReturn( $config['notice']['transient'] );

				if ( ! isset( $expected['bailout_generation_complete'] ) ) {
					Functions\expect( 'do_action' )
						->once()
						->with( 'rocket_critical_css_generation_process_complete' );
					Functions\expect( 'rocket_clean_domain' )
						->once();
					Functions\expect( 'set_transient' )
						->once();
					Functions\expect( 'delete_transient' )
						->once()
						->with( 'rocket_critical_css_generation_process_running' );
					Functions\expect( 'delete_transient' )
						->once()
						->with( 'rocket_cpcss_generation_pending' );
				}

			} else {
				Functions\expect( 'get_transient' )
					->once()
					->with( 'rocket_critical_css_generation_process_running' )
					->andReturn( $config['rocket_critical_css_generation_process_running'] );
			}
			Functions\expect( $expected[ 'json' ] )
				->once()
				->with( $expected[ 'data' ] );
		}

		if ( isset( $expected['generation_complete'] ) && ! $expected['generation_complete'] ) {
			Functions\expect( $expected[ 'json' ] )
				->once()
				->with( $expected[ 'data' ] );
		}
	}

	private function expectProcessGenerate( $config ) {
		if ( isset( $config['process_generate'] ) ) {
			if ( ! empty( $config['process_generate']['is_wp_error'] ) ) {
				$this->processor->shouldReceive( 'process_generate' )->andReturn( $this->wp_error );
				Functions\expect( 'is_wp_error' )
					->andReturn( $config['process_generate']['is_wp_error'] );
			} else {
				$this->processor->shouldReceive( 'process_generate' )->andReturn( $config['process_generate'] );

				Functions\expect( 'is_wp_error' )
					->andReturn( false );
			}
		}
	}


	private function expectHeartbeatNotice( $config ) {
		if ( isset( $config['notice'] ) ) {
			Functions\expect( 'get_transient' )
				->once()
				->with( 'rocket_critical_css_generation_process_running' )
				->andReturn( [ 'total' => $config['notice']['transient']['total'], 'generated' => 0, 'items' => [] ] );

			if ( ! empty( $config['process_generate']['is_wp_error'] ) ) {
				$this->wp_error->shouldReceive( 'get_error_message' )->andReturn( $config['notice']['get_error_message'] );
			}
			Functions\expect( 'set_transient' )
				->once()
				->with( 'rocket_critical_css_generation_process_running', $config['notice']['transient'], HOUR_IN_SECONDS );
		}
	}
}
