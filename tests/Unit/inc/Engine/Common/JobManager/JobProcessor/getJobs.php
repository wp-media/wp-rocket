<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\JobManager\JobProcessor;

use Mockery;
use WP_Rocket\Engine\Common\Clock\WPRClock;
use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractAPIClient;
use WP_Rocket\Engine\Common\JobManager\AbstractFactory\SaasFactory;
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;
use WP_Rocket\Engine\Common\JobManager\Queue\Queue;
use WP_Rocket\Engine\Common\JobManager\Strategy\Factory\StrategyFactory;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\JobProcessor::get_jobs
 *
 * @group JobManager
 */
class Test_GetJobs extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$factories = [];

		foreach ( $config['factories'] as $name => $factory_config ) {
			$factories[ $name ] = $this->mockFactory( $config['type'], $factory_config );
		}

		$processor = new JobProcessor(
			$factories,
			Mockery::mock( Queue::class ),
			Mockery::mock( StrategyFactory::class ),
			Mockery::mock( WPRClock::class )
		);

		$this->assertSame( $expected, $processor->get_jobs( 10, $config['type'] ) );
	}

	private function mockFactory( string $type, array $config ) {
		$method  = 'submit' === $type ? 'get_on_submit_jobs' : 'get_pending_jobs';
		$manager = Mockery::mock( ManagerInterface::class );
		$api     = Mockery::mock( AbstractAPIClient::class );
		$factory = Mockery::mock( SaasFactory::class );

		$manager->shouldReceive( 'is_allowed' )->andReturn( true );
		$api->shouldReceive( 'is_feature_disabled' )->andReturn( $config['blocked'] );
		$factory->shouldReceive( 'manager' )->andReturn( $manager );
		$factory->shouldReceive( 'api' )->andReturn( $api );

		if ( $config['blocked'] && 'submit' === $type ) {
			$manager->shouldReceive( $method )->never();
		} else {
			$manager->shouldReceive( $method )->once()->with( 10 )->andReturn( $config['rows'] );
		}

		return $factory;
	}
}
