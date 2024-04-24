<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DynamicLists\Subscriber::add_dynamic_lists_script
 *
 * @group  DynamicLists
 */
class Test_AddDynamicListsScripts extends TestCase {
	public function testShouldReturnExpected() {
		$this->set_permalink_structure( "/%postname%/" );

		$result = apply_filters( 'rocket_localize_admin_script', [] );

		$this->assertArrayHasKey( 'rest_url', $result );
		$this->assertArrayHasKey( 'rest_nonce', $result );
		$this->assertContains( 'http://example.org/wp-json/wp-rocket/v1/dynamic_lists/update/', $result );
	}
}
