<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Warmup\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;


/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Warmup\Subscriber::collect_resources
 *
 * @group  RUCSS
 */
class Test_CollectResources extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Warmup/Subscriber/collectResources.php';

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ){



	}
}
