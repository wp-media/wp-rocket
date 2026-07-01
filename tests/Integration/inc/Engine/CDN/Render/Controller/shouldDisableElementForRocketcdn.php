<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Render\Controller;

use ReflectionProperty;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController\AbstractSubscriptionControllerTestCase;

/**
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_ShouldDisableElementForRocketcdn extends AbstractSubscriptionControllerTestCase {

	private $render_controller;

	public function set_up() {
		parent::set_up();

		$container = apply_filters( 'rocket_container', null );
		$this->render_controller = $container->get( 'cdn_render_controller' );

		$prop = new ReflectionProperty( $this->render_controller, 'options' );
		$prop->setAccessible( true );
		$prop->getValue( $this->render_controller )->set_values( [ 
			'cdn'      => 1, 
			'cdn_type' => 'rocketcdn' 
			] 
		);

		$this->update_rocketcdn_settings( [ 'cdn' => 1, 'cdn_type' => 'rocketcdn' ] );
		$this->set_rocketcdn_user_token();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, bool $expected ): void {
		if ( $config['forced_pause_state'] ) {
			update_option( 'rocket_rocketcdn_forced_pause_state', [ 'persistent' => true ] );
		}

		$this->mock_api( $config );

		$this->assertSame( $expected, $this->render_controller->should_disable_element_for_rocketcdn() );
	}
}
