<?php
namespace WP_Rocket\Tests\Unit\inc\Addon\Webp\AdminSubscriber;

use Mockery;
use Brain\Monkey\Filters;
use WP_Rocket\Addon\Webp\AdminSubscriber;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Addon\Webp\AdminSubscriber::maybe_disable_setting_field
 * @group WebPValid
 */
class Test_MaybeDisableSettingField extends TestCase {
	private $subscriber;

	protected function setUp(): void {
		parent::setUp();

		$this->subscriber = new AdminSubscriber(
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Subscriber::class ),
			Mockery::mock( Beacon::class )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $filter, $field, $expected ) {
		Filters\expectApplied( 'rocket_disable_webp_cache' )
			->andReturn( $filter );

		$this->assertSame(
			$expected,
			$this->subscriber->maybe_disable_setting_field( $field )
		);
	}
}
