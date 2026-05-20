<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Context;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Tests\Unit\TestCase;

class Test_CanApplyCdn extends TestCase {
	/**
	 * @var mixed
	 */
	private $options;

	/**
	 * @var Context
	 */
	private $context;

	public function set_up() {
		parent::set_up();

		$this->options = Mockery::mock( Options_Data::class );
		$this->context = new Context( $this->options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedEligibility( array $config, bool $expected ) {
		/* @phpstan-ignore-next-line */
		$this->options->shouldReceive( 'get' )
			->with( 'cdn_type', Context::ROCKETCDN_TYPE )
			->andReturn( $config['cdn_type'] );

		Functions\when( 'get_transient' )->justReturn(
			[
				'subscription_status' => $config['subscription_status'],
			]
		);

		$this->assertSame( $expected, $this->context->can_apply_cdn() );
	}

	public function configTestData(): array {
		return [
			'shouldAllowByocdnRegardlessOfSubscriptionStatus' => [
				'config'   => [
					'cdn_type'            => Context::BYOCDN_TYPE,
					'subscription_status' => 'cancelled',
				],
				'expected' => true,
			],
			'shouldAllowRocketCdnWhenStatusIsRunning'         => [
				'config'   => [
					'cdn_type'            => Context::ROCKETCDN_TYPE,
					'subscription_status' => 'running',
				],
				'expected' => true,
			],
			'shouldNotAllowRocketCdnWhenStatusIsCancelled'    => [
				'config'   => [
					'cdn_type'            => Context::ROCKETCDN_FREE_TYPE,
					'subscription_status' => 'cancelled',
				],
				'expected' => false,
			],
			'shouldNotAllowRocketCdnWhenStatusPendingCancellation' => [
				'config'   => [
					'cdn_type'            => Context::ROCKETCDN_TYPE,
					'subscription_status' => 'pending_cancellation',
				],
				'expected' => false,
			],
		];
	}
}
