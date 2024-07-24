<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Media\AboveTheFold\WarmUp\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\AboveTheFold\WarmUp\Subscriber::add_wpr_imagedimensions_query_arg
 *
 * @group Media
 * @group AboveTheFold
 */
class Test_AddWPRImageDimensionQueryArg extends TestCase {
	private $subscriber;
	private $context;

	protected function setUp(): void {
		parent::setUp();

		$this->context    = Mockery::mock( ContextInterface::class );
		$this->subscriber = new Subscriber( $this->context );
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
			$this->subscriber->add_wpr_imagedimensions_query_arg( $config['url'] )
		);
	}
}
