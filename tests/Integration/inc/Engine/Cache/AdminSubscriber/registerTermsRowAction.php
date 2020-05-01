<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdminSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers WP_Rocket\Engine\Cache\AdminSubscriber::register_terms_row_action
 *
 * @group AdminOnly
 * @group Cache
 */
class Test_RegisterTermsRowAction extends TestCase {
	private static $container;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$container = apply_filters( 'rocket_container', '' );
	}

	public function testShouldAddCallbackForEachTerm() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );
		set_current_screen( 'edit-tags' );

		$taxonomies = get_taxonomies(
			[
				'public'             => true,
				'publicly_queryable' => true,
			]
		);

		$subscriber = self::$container->get( 'admin_cache_subscriber' );

		foreach( $taxonomies as $taxonomy ) {
			$this->assertTrue(
				has_action( "{$taxonomy}_row_actions", [ $subscriber, 'add_purge_term_link' ] )
			);
		}
	}
}
