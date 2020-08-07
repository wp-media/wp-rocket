<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\EmbedsSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\EmbedsSubscriber::enqueue_disable_embeds_script
 *
 * @group Media
 * @group Embeds
 */
class EnqueueDisableEmbedsScript extends TestCase {
	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['wp'] );
		remove_filter( 'pre_get_rocket_option_embeds', [ $this, 'set_embeds_value' ] );
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

		do_action( 'enqueue_block_editor_assets' );

		if ( $expected ) {
			$this->assertTrue( wp_script_is( 'rocket-disable-embeds' ) );
		} else {
			$this->assertFalse( wp_script_is( 'rocket-disable-embeds' ) );
		}
	}

	public function set_embeds_value() {
		return $this->embeds_option;
	}
}
