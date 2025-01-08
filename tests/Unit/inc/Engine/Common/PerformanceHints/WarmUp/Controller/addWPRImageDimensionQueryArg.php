<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Common\PerformanceHints\WarmUp\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Common\PerformanceHints\WarmUp\{APIClient, Controller, Queue};
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Common\PerformanceHints\WarmUp\Controller::add_wpr_imagedimensions_query_arg
 *
 * @group PerformanceHints
 */
class Test_AddWPRImageDimensionQueryArg extends TestCase {
    private $options;
    private $api_client;
	private $user;
    private $queue;
	private $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->api_client = Mockery::mock( APIClient::class );
		$this->user       = Mockery::mock( User::class );
		$this->queue      = Mockery::mock( Queue::class );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
        $this->controller = new Controller( $config['filter'], $this->options, $this->api_client, $this->user, $this->queue );

		$this->options->shouldReceive('get')
			->with( 'remove_unused_css', 0 )
			->andReturn( $config['remove_unused_css'] ?? 0 );

		Functions\expect( 'add_query_arg' )
			->with(
				[ 'wpr_imagedimensions' => 1 ],
				$config['url']
			)
			->andReturn( $config['url'] . '/?wpr_imagedimensions=1' );

		$this->assertSame(
			$expected,
			$this->controller->add_wpr_imagedimensions_query_arg( $config['url'] )
		);
	}
}
