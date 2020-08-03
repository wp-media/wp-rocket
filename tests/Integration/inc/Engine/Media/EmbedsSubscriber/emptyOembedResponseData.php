<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\EmbedsSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\EmbedsSubscriber::empty_oembed_response_data
 *
 * @group Media
 * @group Embeds
 */
class EmptyOembedResponseData extends TestCase {
	public function tearDown() {
        parent::tearDown();

        unset( $GLOBALS['wp'] );
        remove_filter( 'pre_get_rocket_option_embeds', [ $this, 'set_embeds_value' ] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $data, $expected ) {
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

        if ( $config['rest_request'] ) {
            $this->constants['REST_REQUEST'] = true;
        }

		$this->embeds_option = $config['options']['embeds'];

		add_filter( 'pre_get_rocket_option_embeds', [ $this, 'set_embeds_value' ] );

        $post = (object) [
            'ID' => 1,
        ];

        $response = apply_filters( 'oembed_response_data', $data, $post, 100, 100 );

        if ( $expected ) {
            $this->assertArrayHasKey( 'test', $response );
        } else {
            $this->assertArrayNotHasKey( 'test', $response );
        }
	}

	public function set_embeds_value() {
        return $this->embeds_option;
    }
}
