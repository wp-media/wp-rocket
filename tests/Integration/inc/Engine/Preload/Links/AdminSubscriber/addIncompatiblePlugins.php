<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Links\AdminSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Links\AdminSubscriber::add_incompatible_plugins
 *
 * @group  PreloadLinks
 */
class Test_AddIncompatiblePlugins extends TestCase {
	private $preload_value;

    public function tear_down() {
        parent::tear_down();

        remove_filter( 'pre_get_rocket_option_preload_links', [ $this, 'set_preload_value' ] );

		$this->restoreWpFilter( 'rocket_plugins_to_deactivate' );
    }

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $option, $plugins, $expected ) {
        $this->preload_value = $option;

		$this->unregisterAllCallbacksExcept( 'rocket_plugins_to_deactivate', 'add_incompatible_plugins' );

        add_filter( 'pre_get_rocket_option_preload_links', [ $this, 'set_preload_value' ] );

		$this->assertSame( $expected, apply_filters( 'rocket_plugins_to_deactivate', $plugins ) );
    }

    public function set_preload_value() {
        return $this->preload_value;
    }
}
