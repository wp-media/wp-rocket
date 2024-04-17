<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Shoptimizer;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;
use WP_Rocket\ThirdParty\Themes\Shoptimizer;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Shoptimizer::exclude_jquery_deferjs_with_cart_drawer
 *
 * @group Themes
 */
class Test_excludeJqueryDeferjsWithCartDrawer extends TestCase {
	private $container;
	private $event;
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->container = apply_filters( 'rocket_container', '' );
		$this->event = $this->container->get( 'event_manager' );
	}

	public function tear_down() {
		$this->event->remove_subscriber( $this->subscriber );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->subscriber = new Shoptimizer( $this->container->get( 'options' ) );
		$this->event->add_subscriber( $this->subscriber );

		Functions\when( 'shoptimizer_get_option' )->justReturn( $config['option'] );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_exclude_defer_js', $config['exclusions'] )
		);
	}
}
