<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Warmup\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;


/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Warmup\Subscriber::collect_resources
 *
 * @group  RUCSS
 */
class Test_CollectResources extends TestCase {
	use DBTrait;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Warmup/Subscriber/collectResources.php';

	private $input = [];

	public function setUp(): void {
		parent::setUp();

		DBTrait::removeDBHooks();
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ){

		$this->donotrocketoptimize = isset( $input['DONOTROCKETOPTIMIZE'] ) ? $input['DONOTROCKETOPTIMIZE'] : false;

		$this->input = $input;

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		$this->assertSame( $input['html'], apply_filters( 'rocket_buffer', $input['html'] ) );
		$this->assertTrue(false);

	}

	public function set_rucss_option() {
		return $this->input['remove_unused_css'] ?? false;
	}
}
