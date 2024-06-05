<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\AboveTheFold\WarmUp\Subscriber;

use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\Queue;
use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\APIClient;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\Controller;

use Mockery;



/**
 * Test class covering \WP_Rocket\Engine\Media\AboveTheFold\WarmUp\Subscriber::warm_up
 *
 * @group AboveTheFold
 */
class Test_WarmUp extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Media/AboveTheFold/WarmUp/Subscriber/warmUp.php';

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * Test should do expected.
	 *
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->config = $config;
		$context = Mockery::mock(ContextInterface::class);
		$options = Mockery::mock(Options_Data::class);
		$api_client = Mockery::mock(APIClient::class);
		$user = Mockery::mock(User::class);
		$queue = Mockery::mock(Queue::class);

		$controller = Mockery::mock(Controller::class, [$context, $options, $api_client, $user, $queue])->makePartial();

		$options->shouldReceive('get')
			->with('cache_mobile', 0)
			->andReturn(0);

		$context->shouldReceive('is_allowed')
			->andReturn($config['is_allowed']);

		$controller->shouldReceive('fetch_links')
			->andReturn($config['links']);


		Functions\expect( 'wp_get_environment_type' )->once()->andReturn($config['wp_env']);

		if ( 'local' !== $config['wp_env'] ) {
			$options->shouldReceive('get')
				->with('remove_unused_css', 0)
				->andReturn($config['remove_unused_css']);
		}

		$queue->shouldReceive('add_job_warmup_url')
			->times($expected);

		$controller->warm_up();
	}
}
