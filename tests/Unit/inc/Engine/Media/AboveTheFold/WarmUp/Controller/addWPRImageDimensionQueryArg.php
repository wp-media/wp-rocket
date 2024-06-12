<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Media\AboveTheFold\WarmUp\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\{APIClient, Controller, Queue};
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\AboveTheFold\WarmUp\Controller::add_wpr_imagedimensions_query_arg
 *
 * @group Media
 * @group AboveTheFold
 */
class Test_AddWPRImageDimensionQueryArg extends TestCase {
	private $user;
	private $controller;
	private $context;
	private $queue;

	protected function setUp(): void {
		parent::setUp();

		$this->context    = Mockery::mock( ContextInterface::class );
		$options          = Mockery::mock( Options_Data::class );
		$api_client       = Mockery::mock( APIClient::class );
		$this->user       = Mockery::mock( User::class );
		$this->queue      = Mockery::mock( Queue::class );
		$this->controller = new Controller( $this->context, $options, $api_client, $this->user, $this->queue );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->context->shouldReceive( 'is_allowed' )
			->atMost()
			->once()
			->andReturn( $config['filter'] );

		Functions\expect( 'add_query_arg' )
			->with(
				[ 'wpr_imagedimensions' => 1 ]
			)
			->andReturn( $expected );

		$this->assertSame(
			$expected,
			$this->controller->add_wpr_imagedimensions_query_arg( $config['url'] )
		);
	}
}
