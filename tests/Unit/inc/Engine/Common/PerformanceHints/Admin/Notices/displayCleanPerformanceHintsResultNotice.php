<?php

namespace WP_Rocket\tests\Unit\inc\Engine\Common\PerformanceHints\Admin\Notices;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Admin\Notices;

/**
 * Test class covering \WP_Rocket\Engine\Common\PerformanceHints\Admin\Notices::clean_performance_hint_result
 */
class Test_DisplayCleanPerformanceHintsResultNotice extends TestCase {
	protected $atf_context;
	private $notices;

	public function setUp(): void {
		parent::setUp();

		$this->atf_context = Mockery::mock( ContextInterface::class );
		$this->notices     = new Notices( $this->atf_context );

		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_manage_options' )
			->andReturn( $config['capability'] );

		Functions\when('get_transient')->alias(function ($name) use ($config) {
			return $config['transient'];
		});

		$this->atf_context->shouldReceive( 'is_allowed' )
			->andReturn( $config['atf_context'] );

		if ( $expected ) {
			Functions\expect( 'rocket_notice_html' )
				->once();
			Functions\expect('delete_transient')->with('rocket_performance_hints_clear_message');
		} else {
			Functions\expect( 'rocket_notice_html' )->never();
		}

		$this->notices->clean_performance_hint_result();
	}
}
