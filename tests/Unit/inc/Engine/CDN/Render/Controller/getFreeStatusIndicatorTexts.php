<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Render\Controller;

use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\Render\Controller;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Render\Controller::get_free_status_indicator_texts
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::get_free_status_indicator_texts
 * @group  CDN
 * @group  RocketCDN
 */
class Test_GetFreeStatusIndicatorTexts extends TestCase {

	/**
	 * @var Mockery\MockInterface|Beacon
	 */
	private $beacon;

	/**
	 * @var Mockery\MockInterface|Context
	 */
	private $context;

	/**
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

	/**
	 * @var Mockery\MockInterface|Options
	 */
	private $options_api;

	/**
	 * @var \PHPUnit\Framework\MockObject\MockObject|RocketCDNQuery
	 */
	private $cdn_query;

	/**
	 * @var Mockery\MockInterface|SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * @var Mockery\MockInterface|User
	 */
	private $user;

	/**
	 * @var Mockery\MockInterface|Cache
	 */
	private $cache;

	public function set_up(): void {
		parent::set_up();

		$this->stubTranslationFunctions();

		$this->beacon                  = Mockery::mock( Beacon::class );
		$this->context                 = Mockery::mock( Context::class );
		$this->options                 = Mockery::mock( Options_Data::class );
		$this->options_api             = Mockery::mock( Options::class );
		$this->cdn_query               = $this->createMock( RocketCDNQuery::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->user                    = Mockery::mock( User::class );
		$this->cache                   = Mockery::mock( Cache::class );
	}

	private function get_controller(): Controller {
		return new Controller(
			$this->beacon,
			'',
			$this->context,
			$this->options,
			$this->options_api,
			$this->cdn_query,
			$this->subscription_controller,
			$this->user,
			$this->cache
		);
	}

	private function default_texts(): array {
		return [
			'status_text'         => '',
			'details'             => 'Start with your homepage.',
			'class'               => '',
			'no_status_indicator' => false,
		];
	}

	/**
	 * @dataProvider configTestData
	 *
	 * @param array $config   Scenario inputs and mock return values.
	 * @param array $expected Assertions to make against the returned texts.
	 */
	public function testShouldReturnExpectedTexts( array $config, array $expected ): void {
		if ( $config['free'] ) {
			$this->context->shouldReceive( 'get_applied_cdn_state' )
				->andReturn( $config['applied_cdn_state'] );

			// has_active_subscription is only called when CDN is paused (short-circuit &&).
			if ( Context::CDN_STATE_NOTHING === $config['applied_cdn_state'] ) {
				$this->subscription_controller->shouldReceive( 'has_active_subscription' )
					->andReturn( $config['has_active_subscription'] ?? false );
			}

			$this->subscription_controller->shouldReceive( 'is_license_invalid' )
				->andReturn( $config['is_license_invalid'] );

			$this->user->shouldReceive( 'is_reseller_license_banned' )
				->andReturn( $config['is_reseller_license_banned'] );
		}

		$result = $this->get_controller()->get_free_status_indicator_texts(
			$this->default_texts(),
			$config['pages_count'],
			$config['is_loading'],
			$config['free']
		);

		if ( ! empty( $expected['same_as_input'] ) ) {
			$this->assertSame( $this->default_texts(), $result );
			return;
		}

		if ( isset( $expected['details'] ) ) {
			$this->assertSame( $expected['details'], $result['details'] );
		}

		if ( isset( $expected['no_status_indicator'] ) ) {
			$this->assertSame( $expected['no_status_indicator'], $result['no_status_indicator'] );
		}

		if ( isset( $expected['class_contains'] ) ) {
			$this->assertStringContainsString( $expected['class_contains'], $result['class'] );
		}
	}
}
