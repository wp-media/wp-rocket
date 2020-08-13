<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Links\AdminSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Links\AdminSubscriber::add_incompatible_plugins
 *
 * @group  PreloadLinks
 */
class Test_AddIncompatiblePlugins extends TestCase {
    public function tearDown() {
        parent::tearDown();

        remove_filter( 'pre_get_rocket_option_preload_links', [ $this, 'set_preload_value' ] );
    }

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $option, $plugins, $expected ) {
        $this->preload_value = $option;

        add_filter( 'pre_get_rocket_option_preload_links', [ $this, 'set_preload_value' ] );

		$this->assertSame( $expected, apply_filters( 'rocket_plugins_to_deactivate', $plugins ) );
    }

    public function set_preload_value() {
        return $this->preload_value;
    }
}
