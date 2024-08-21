<?php

namespace WP_Rocket\tests\Integration\inc\Engine\Common\PerformanceHints\WarmUp\Subscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\PerformanceHints\WarmUp\APIClient;
use WP_Rocket\Engine\Common\PerformanceHints\WarmUp\Controller;
use WP_Rocket\Engine\Common\PerformanceHints\WarmUp\Queue;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\PerformanceHints\WarmUp\Subscriber::warm_up_home
 *
 * @group PerformanceHints
 * @group WarmUp
 */
class Test_warmUpHome extends TestCase {
	/**
	 * Test should do expected.
	 *
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$options = Mockery::mock(Options_Data::class);
		$api_client = Mockery::mock(APIClient::class);
		$user = Mockery::mock(User::class);
		$queue = Mockery::mock(Queue::class);
		$controller = Mockery::mock(Controller::class, [$config['factories'], $options, $api_client, $user, $queue])->makePartial();

		$controller->shouldReceive('send_to_saas')
			->andReturn($config['home_url']);

		Functions\expect( 'wp_get_environment_type' )->andReturn($config['wp_env']);

		$queue->shouldReceive('add_job_warmup')
			->times($expected);

		if ( 'local' !== $config['wp_env'] ) {
			$options->shouldReceive('get')
				->with('remove_unused_css', 0)
				->andReturn($config['remove_unused_css']);

			$user->shouldReceive( 'is_license_expired_grace_period' )
				->once()
				->andReturn( $config['license_expired'] );
		}

		add_action('rocket_after_clear_performance_hints_data', [$controller, 'warm_up_home']);

		do_action('rocket_after_clear_performance_hints_data');

		remove_action('rocket_after_clear_performance_hints_data', [$controller, 'warm_up_home']);
	}
}
