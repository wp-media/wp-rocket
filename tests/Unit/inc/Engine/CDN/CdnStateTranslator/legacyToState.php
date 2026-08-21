<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CdnStateTranslator;

use Mockery;
use WP_Rocket\Engine\CDN\CdnStateTranslator;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Tests\Unit\TestCase;

class Test_LegacyToState extends TestCase {
	/**
	 * @var Mockery\MockInterface|SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * @var CdnStateTranslator
	 */
	private $translator;

	public function set_up() {
		parent::set_up();

		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->translator               = new CdnStateTranslator( $this->subscription_controller );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedState( array $settings, array $config, string $expected ) {
		if ( ! empty( $settings['cdn'] ) && 'rocketcdn' === ( $settings['cdn_type'] ?? 'rocketcdn' ) ) {
			$this->subscription_controller->shouldReceive( 'has_active_subscription' )
				->andReturn( $config['has_active_subscription'] ?? false );

			if ( ! ( $config['has_active_subscription'] ?? false ) ) {
				$this->subscription_controller->shouldReceive( 'is_cancelled_outside_grace_period' )
					->andReturn( $config['is_cancelled_outside_grace_period'] ?? false );
			}

			if ( ( $config['has_active_subscription'] ?? false ) || ! ( $config['is_cancelled_outside_grace_period'] ?? false ) ) {
				$this->subscription_controller->shouldReceive( 'is_paid' )
					->andReturn( $config['is_paid'] ?? false );
			}
		}

		$this->assertSame( $expected, $this->translator->legacy_to_state( $settings ) );
	}
}
