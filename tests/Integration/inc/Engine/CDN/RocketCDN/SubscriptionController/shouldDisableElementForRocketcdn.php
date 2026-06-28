<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

/**
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_ShouldDisableElementForRocketcdn extends AbstractSubscriptionControllerTestCase {

	private $render_controller;

	public function set_up() {
		parent::set_up();

		$this->render_controller = $this->getRocketContainer()->get( 'cdn_render_controller' );

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

		$this->assertSame( $expected, $this->render_controller->should_disable_element_for_rocketcdn() );
	}
}
