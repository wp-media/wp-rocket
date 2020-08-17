<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\EmbedsSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\EmbedsSubscriber::remove_embeds_rewrite_rules
 *
 * @group Media
 * @group Embeds
 */
class RemoveEmbedsRewriteRules extends TestCase {
	public function tearDown() {
        parent::tearDown();

        unset( $GLOBALS['wp'] );
		remove_filter( 'pre_get_rocket_option_embeds', [ $this, 'set_embeds_value' ] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $rules, $expected ) {
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

		$this->assertSame(
			array_values( $expected ),
			array_values( apply_filters( 'rewrite_rules_array', $rules ) )
		);
	}

	public function set_embeds_value() {
        return $this->embeds_option;
    }
}
