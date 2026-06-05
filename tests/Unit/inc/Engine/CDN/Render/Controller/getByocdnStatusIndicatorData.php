<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Render\Controller;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\Render\Controller;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::get_byocdn_status_indicator_data
 * @group  CDN
 */
class Test_GetByocdnStatusIndicatorData extends TestCase {

	private $context;
	private $controller;

	public function set_up(): void {
		parent::set_up();

			$this->stubTranslationFunctions();

		$this->context = Mockery::mock( Context::class );

		$this->controller = new Controller(
			Mockery::mock( Beacon::class ),
			'',
			$this->context,
			Mockery::mock( Options_Data::class ),
			$this->createMock( RocketCDNQuery::class ),
			Mockery::mock( SubscriptionController::class ),
			Mockery::mock( User::class )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedData( array $config, array $expected ): void {
		$this->context->shouldReceive( 'is_byocdn_paused' )
			->andReturn( $config['is_byocdn_paused'] );

		$result = $this->controller->get_byocdn_status_indicator_data();

		$this->assertSame( $expected['is_active'], $result['is_active'] );
		$this->assertSame( $expected['is_paused'], $result['is_paused'] );
		$this->assertSame( $expected['hide_pause_btn'], $result['hide_pause_btn'] );
		$this->assertSame( $expected['cdn_type'], $result['cdn_type'] );
		$this->assertSame( $expected['class'], $result['class'] );
		$this->assertSame( $expected['status_text'], $result['status_text'] );
	}
}
