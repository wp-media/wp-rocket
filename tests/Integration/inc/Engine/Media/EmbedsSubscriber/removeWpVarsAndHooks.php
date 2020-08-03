<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\EmbedsSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\EmbedsSubscriber::remove_wp_vars_and_hooks
 *
 * @group Media
 * @group Embeds
 */
class RemoveWpVarsAndHooks extends TestCase {
	public function tearDown() {
        parent::tearDown();

        unset( $GLOBALS['wp'] );
		remove_filter( 'pre_get_rocket_option_embeds', [ $this, 'set_embeds_value' ] );
		$this->restoreWpFilter( 'init' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$GLOBALS['wp'] = (object) [
            'query_vars' => [],
			'request'    => 'http://example.org',
			'public_query_vars' => [
				'embed',
			],
        ];

        if ( $config['bypass'] ) {
            $GLOBALS['wp']->query_vars['nowprocket'] = 1;
		}

		$this->embeds_option = $config['options']['embeds'];

		add_filter( 'pre_get_rocket_option_embeds', [ $this, 'set_embeds_value' ] );

		$this->unregisterAllCallbacksExcept( 'init', 'remove_wp_vars_and_hooks', 9999 );

		do_action( 'init' );

		if ( $expected ) {
			$this->assertFalse( has_filter( 'oembed_dataparse', 'wp_filter_oembed_result' ) );
			$this->assertFalse( has_action( 'wp_head', 'wp_oembed_add_discovery_links' ) );
			$this->assertFalse( has_action( 'wp_head', 'wp_oembed_add_host_js' ) );
			$this->assertFalse( has_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result' ) );
		} else {
			$this->assertNotFalse( has_filter( 'oembed_dataparse', 'wp_filter_oembed_result' ) );
			$this->assertNotFalse( has_action( 'wp_head', 'wp_oembed_add_discovery_links' ) );
			$this->assertNotFalse( has_action( 'wp_head', 'wp_oembed_add_host_js' ) );
			$this->assertNotFalse( has_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result' ) );
		}
	}

	public function set_embeds_value() {
        return $this->embeds_option;
    }
}
