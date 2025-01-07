<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\HTML;

use Mockery;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DelayJS\HTML::delay_js
 * @group  Optimize
 * @group  DelayJS
 *
 * @uses   ::rocket_get_constant()
 */
class Test_DelayJs extends TestCase {
	private $options;
	private $data_manager;
	protected $logger;

	public function setUp() : void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->data_manager = Mockery::mock( DataManager::class );
		$this->logger = Mockery::mock(Logger::class);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldProcessScriptHTML( $config, $html, $expected ) {
		$this->donotrocketoptimize = $config['donotoptimize'];

		$this->logger->allows()->debug(Mockery::any());

		Filters\expectApplied( 'rocket_disable_meta_generator' )
			->atMost()
			->once()
			->andReturn( true );

		Functions\expect( 'rocket_bypass' )
			->atMost()
			->once()
			->andReturn( $config['bypass'] );

		Functions\when( 'is_rocket_post_excluded_option' )->justReturn( $config['post-excluded'] );

		if ( $this->donotrocketoptimize || $config['bypass'] || $config['post-excluded'] ) {
			$this->options->shouldReceive( 'get' )
				->with( 'delay_js', 0 )
				->never();
		} else {
			$this->options->shouldReceive( 'get' )
				->with( 'delay_js', 0 )
				->once()
				->andReturn( $config['delay_js'] );

			$this->options->shouldReceive( 'get' )
				->with( 'delay_js_exclusions', [] )
				->atMost()
				->once()
				->andReturn( $config['delay_js_exclusions'] );

			$this->options->shouldReceive( 'get' )
				->with( 'delay_js_exclusions_selected_exclusions', [] )
				->atMost()
				->once()
				->andReturn( [] );
			$this->options->shouldReceive('get')
				->with('delay_js_execution_safe_mode', 0)
				->atMost()
				->once()
				->andReturn($config['delay_js_safe_mode']);

			$this->data_manager->shouldReceive( 'get_lists' )
				->atMost()
				->once()
				->andReturn( $config['exclusions_list'] );
		}

		$delay_js_html = new HTML( $this->options, $this->data_manager, $this->logger );

		$this->assertSame(
			$expected,
			$delay_js_html->delay_js( $html )
		);
	}
}
