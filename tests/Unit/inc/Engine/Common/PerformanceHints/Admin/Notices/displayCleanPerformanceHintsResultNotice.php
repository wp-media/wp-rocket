<?php

namespace WP_Rocket\tests\Unit\inc\Engine\Common\PerformanceHints\Admin\Notices;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Media\AboveTheFold\Factory as ATFFactory;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Factory;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Admin\Notices;

/**
 * Test class covering \WP_Rocket\Engine\Common\PerformanceHints\Admin\Notices::clean_performance_hint_result
 */
class Test_DisplayCleanPerformanceHintsResultNotice extends TestCase {
	private $factories;

	public function setUp(): void {
		parent::setUp();

		$atf_factory = $this->createMock(ATFFactory::class);
		$lrc_factory = $this->createMock(Factory::class);
		$this->factories   = [
			$atf_factory,
			$lrc_factory
		];

		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$notices = new Notices( $config['factories'] ? $this->factories : [] );

		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_manage_options' )
			->andReturn( $config['capability'] );

		Functions\when('get_transient')->alias(function ($name) use ($config) {
			return $config['transient'];
		});

		if ( $expected ) {
			Functions\expect( 'rocket_notice_html' )
				->once();
			Functions\expect('delete_transient')->with('rocket_performance_hints_clear_message');
		} else {
			Functions\expect( 'rocket_notice_html' )->never();
		}

		$notices->clean_performance_hint_result();
	}
}
