<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Cookie;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Plugins\Cookie\Termly;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Cookies\Termly::clean_domain
 *
 * @group Plugins
 */
class Test_ExcludeDelayJs extends TestCase {

	private $event;
	private $subscriber;

	protected $path_to_test_data = '/inc/ThirdParty/Plugins/Cookie/excludeDelayJs.php';

	public function set_up() {
		parent::set_up();

		if ( ! defined( 'TERMLY_VERSION' ) ) {
			define( 'TERMLY_VERSION', '1.0' );
		}

		$container = wpm_apply_filters_typed('object', 'rocket_container', '' );

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
		$this->subscriber = new Termly();
		$this->event->add_subscriber( $this->subscriber );

		Functions\expect( 'get_option' )
			->twice()
			->andReturn( $config['termly_display_auto_blocker'] );

		$this->assertSame(
			$expected,
			wpm_apply_filters_typed( 'array', 'rocket_delay_js_exclusions', $config['excluded'] )
		);

		$this->assertSame(
			$expected,
			wpm_apply_filters_typed( 'array', 'rocket_exclude_defer_js', $config['excluded'] )
		);
	}
}
