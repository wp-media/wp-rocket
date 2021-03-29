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
		DBTrait::removeDBHooks();

		$GLOBALS['wp'] = (object) [
			'query_vars' => [],
			'request'    => 'http://example.org',
			'public_query_vars' => [
				'embed',
			],
		];

		parent::setUp();
	}

	public function tearDown() {
		unset( $GLOBALS['wp'] );

		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ){

		$this->donotrocketoptimize = isset( $input['DONOTROCKETOPTIMIZE'] ) ? $input['DONOTROCKETOPTIMIZE'] : false;

		$this->input = $input;

		if ( isset( $input['rocket_bypass'] ) ) {
			$GLOBALS['wp']->query_vars['nowprocket'] = $input['rocket_bypass'];
		}

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		$this->assertSame( $input['html'], apply_filters( 'rocket_buffer', $input['html'] ) );
		$this->assertTrue(false);

	}

	public function set_rucss_option() {
		return $this->input['remove_unused_css'] ?? false;
	}
}
