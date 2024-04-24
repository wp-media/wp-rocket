<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Subscriber::exclude_private_post_uri
 *
 * @group  Preload
 */
class Test_ExcludePrivatePostUri extends TestCase {
    public function set_up() {
        parent::set_up();
        $this->set_permalink_structure( "/%postname%/" );

		$this->unregisterAllCallbacksExcept( 'rocket_preload_exclude_urls', 'exclude_private_post_uri', 10 );
	}

    public function tear_down() {
		$this->restoreWpHook( 'rocket_preload_exclude_urls' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
        if ( $config['have_posts'] ) {
	        $this->factory->post->create( [
		        'post_title' => 'test 4',
		        'post_status' => 'private',
		        'post_type' => 'post'
	        ] );
        }

        $this->assertSame( $expected, apply_filters( 'rocket_preload_exclude_urls', $config['regex'], $config['url'] ) );
	}
}
