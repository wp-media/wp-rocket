<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\CdnStateBridge;
use WP_Rocket\Engine\CDN\CNAMEValidator;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::maybe_clear_cname_cache
 *
 * @group CDN
 */
class Test_MaybeClearCnameCache extends TestCase {

	private $subscriber;
	private $cname_validator;

	public function setUp(): void {
		parent::setUp();

		$this->cname_validator = Mockery::mock( CNAMEValidator::class );

		$this->subscriber = new Subscriber(
			Mockery::mock( Options_Data::class ),
			Mockery::mock( CDN::class ),
			Mockery::mock( Options::class ),
			Mockery::mock( SubscriptionController::class ),
			Mockery::mock( Cache::class ),
			$this->createMock( RocketCDN::class ),
			Mockery::mock( CdnStateBridge::class ),
			null,
			$this->cname_validator
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldClearExpectedCnames( array $config, array $expected ): void {
		$this->cname_validator->expects()
			->clear_validation_cache( $expected['cleared_cnames'] );

		$this->subscriber->maybe_clear_cname_cache( $config['old_value'], $config['new_value'] );
	}
}
