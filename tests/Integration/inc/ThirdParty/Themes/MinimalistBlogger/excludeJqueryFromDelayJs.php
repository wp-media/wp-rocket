<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\MinimalistBlogger;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Themes\MinimalistBlogger;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\MinimalistBlogger::exclude_jquery_from_delay_js
 *
 * @group Themes
 */
class Test_excludeJqueryFromDelayJs extends TestCase {
	private $event;
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$container = apply_filters( 'rocket_container', '' );

		$this->event = $container->get( 'event_manager' );
	}

	public function tear_down() {
		$this->event->remove_subscriber( $this->subscriber );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->subscriber = new MinimalistBlogger();

		$this->event->add_subscriber( $this->subscriber );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_delay_js_exclusions', $config['excluded'] )
		);
	}
}
