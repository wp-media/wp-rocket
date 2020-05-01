<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdminSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers WP_Rocket\Engine\Cache\AdminSubscriber::add_purge_term_link
 *
 * @group AdminOnly
 * @group Cache
 */
class Test_AddPurgeTermLink extends TestCase {
    /**
	 * @dataProvider providerTestData
	 */
    public function testShouldAddCallbackForEachTerm( $config, $expected ) {
        set_current_screen( 'edit-tags' );

        $term = (object) [
			'term_id'  => 1,
			'taxonomy' => 'post_tag'
		];

        if ( ! $config['cap'] ) {
            $this->assertArrayNotHasKey(
                'rocket_purge',
                apply_filters( 'post_tag_row_actions', [], $term )
            );
        } else {
            $admin = get_role( 'administrator' );
            $admin->add_cap( 'rocket_purge_terms' );

            $user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
            wp_set_current_user( $user_id );
            

            $actions = apply_filters( 'post_tag_row_actions', [], $term );

            $this->assertArrayHasKey(
                'rocket_purge',
                $actions
            );

            $this->assertContains(
                $expected,
                $actions
            );
        }
    }

    public function providerTestData() {
		return $this->getTestData( __DIR__, 'addPurgeTermLink' );
	}
}