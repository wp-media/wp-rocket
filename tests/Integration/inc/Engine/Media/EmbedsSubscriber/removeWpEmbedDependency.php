<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\EmbedsSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\EmbedsSubscriber::remove_wp_embed_dependency
 *
 * @group Media
 * @group Embeds
 */
class RemoveWpEmbedDependency extends TestCase {
	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['wp'] );
		remove_filter( 'pre_get_rocket_option_embeds', [ $this, 'set_embeds_value' ] );
		$this->restoreWpFilter( 'wp_default_scripts' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $scripts, $expected ) {
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

		$this->unregisterAllCallbacksExcept( 'wp_default_scripts', 'remove_wp_embed_dependency' );

		do_action( 'wp_default_scripts', $scripts );

		if ( $expected ) {
			$this->assertTrue( in_array( 'wp-embed', $scripts->registered['wp-edit-post']->deps ) );
		} else {
			$this->assertFalse( in_array( 'wp-embed', $scripts->registered['wp-edit-post']->deps ) );
		}
	}

	public function set_embeds_value() {
		return $this->embeds_option;
	}
}
