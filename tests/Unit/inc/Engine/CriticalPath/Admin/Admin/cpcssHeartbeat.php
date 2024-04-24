<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\Admin;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use Mockery;
use WP_Error;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\CriticalPath\Admin\Admin;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\AdminTrait;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\Admin\Admin::cpcss_heartbeat
 *
 * @group  CriticalPath
 * @group  CriticalPathAdmin
 */
class Test_CpcssHeartbeat extends TestCase {
	use AdminTrait;

	private $admin;
	private $processor;
	private $wp_error;

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Error.php';
	}

	protected function setUp() : void {
		parent::setUp();

		Functions\stubTranslationFunctions();
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
		$expected['set_rocket_cpcss_generation_pending'] = isset( $expected['set_rocket_cpcss_generation_pending'] )
			? $expected['set_rocket_cpcss_generation_pending']
			: [];

		$this->bailoutConditions( $config );
		$this->expectTransientPending( $config, $expected );
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

		if ( isset( $config['rocket_manage_options'] ) ) {
			Functions\expect( 'current_user_can' )
				->once()
				->with( 'rocket_manage_options' )
				->andReturn( $config['rocket_manage_options'] );
		}

		if ( isset( $config['rocket_regenerate_critical_css'] ) ) {
			Functions\expect( 'current_user_can' )
				->once()
				->with( 'rocket_regenerate_critical_css' )
				->andReturn( $config['rocket_regenerate_critical_css'] );
		}
	}

	private function expectBailoutConditions( $expected ) {
		Functions\expect( 'get_transient' )
			->with( 'rocket_cpcss_generation_pending' )
			->never();
		Functions\expect( $expected['json'] )
			->once();
	}

	private function expectTransientPending( $config, $expected ) {
		if ( ! isset( $config['rocket_cpcss_generation_pending'] ) ) {
			return;
		}

		$pending_queue = $config['rocket_cpcss_generation_pending'];

		Functions\expect( 'get_transient' )
			->once()
			->with( 'rocket_cpcss_generation_pending' )
			->andReturn( $pending_queue );

		if (
			(
				empty( $pending_queue )
				&&
				false !== $pending_queue
			)
			||
			(
				isset( $config['rocket_cpcss_generation_pending_after'] )
				&&
				empty( $config['rocket_cpcss_generation_pending_after'] )
			)
		) {

			Functions\expect( 'delete_transient' )
				->once()
				->with( 'rocket_cpcss_generation_pending' )
				->andReturnNull();
		}

		if ( ! isset( $expected['set_rocket_cpcss_generation_pending'] ) ) {
			return;
		}

		$set_transient = $expected['set_rocket_cpcss_generation_pending'];
		if ( ! empty( $set_transient ) ) {
			Functions\expect( 'set_transient' )
				->once()
				->with( 'rocket_cpcss_generation_pending', $set_transient, HOUR_IN_SECONDS )
				->andReturnNull();
		}
	}

	private function expectGenerationComplete( $config, $expected ) {
		if ( ! empty( $expected['generation_complete'] ) ) {
			$this->assertGenerationCompleteWhenNotSet( $config, $expected );
		}

		if ( isset( $expected['generation_complete'] ) && ! $expected['generation_complete'] ) {
			Functions\expect( $expected['json'] )
				->once()
				->with( $expected['data'] );
		}
	}

	private function assertGenerationCompleteWhenNotSet( $config, $expected ) {
		Functions\expect( $expected['json'] )
			->once()
			->with( $expected['data'] );

		if ( ! $config['rocket_critical_css_generation_process_running'] ) {
			Functions\expect( 'get_transient' )
				->once()
				->with( 'rocket_critical_css_generation_process_running' )
				->andReturn( $config['rocket_critical_css_generation_process_running'] );

			return;
		}

		$config['notice']['transient'] = isset( $config['notice']['transient'] )
			? $config['notice']['transient']
			: [
				'items' => [],
				'total' => count( $config['rocket_cpcss_generation_pending'] ),
			];

		Functions\expect( 'get_transient' )
			->with( 'rocket_critical_css_generation_process_running' )
			->andReturn( $config['notice']['transient'] );

		if ( isset( $expected['bailout_generation_complete'] ) ) {
			return;
		}

		Actions\expectDone( 'rocket_critical_css_generation_process_complete' )->once();
		Functions\expect( 'rocket_clean_domain' )->once()->andReturnNull();
		Functions\expect( 'set_transient' )->once()->andReturnNull();
		Functions\expect( 'delete_transient' )
			->once()
			->with( 'rocket_critical_css_generation_process_running' )
			->andReturnNull();
	}

	private function expectProcessGenerate( $config ) {
		if ( ! isset( $config['process_generate'] ) ) {
			return;
		}

		if ( ! empty( $config['process_generate']['is_wp_error'] ) ) {
			$this->processor->shouldReceive( 'process_generate' )->andReturn( $this->wp_error );
			Functions\expect( 'is_wp_error' )
				->andReturn( $config['process_generate']['is_wp_error'] );
		} else {
			$this->processor->shouldReceive( 'process_generate' )->andReturn( $config['process_generate'] );

			Functions\expect( 'is_wp_error' )->andReturn( false );
		}
	}

	private function expectHeartbeatNotice( $config ) {
		if ( ! isset( $config['notice'] ) ) {
			return;
		}

		Functions\expect( 'get_transient' )
			->once()
			->with( 'rocket_critical_css_generation_process_running' )
			->andReturn(
				[
					'total' => $config['notice']['transient']['total'],
					'items' => [],
				]
			);

		if ( ! empty( $config['process_generate']['is_wp_error'] ) ) {
			$this->wp_error
				->shouldReceive( 'get_error_message' )
				->andReturn( $config['notice']['get_error_message'] );
		}

		Functions\expect( 'set_transient' )
			->once()
			->with( 'rocket_critical_css_generation_process_running', $config['notice']['transient'], HOUR_IN_SECONDS );
	}
}
