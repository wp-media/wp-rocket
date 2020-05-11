<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdminSubscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * @covers WP_Rocket\Engine\Cache\AdminSubscriber::register_terms_row_action
 *
 * @group AdminOnly
 * @group Cache
 */
class Test_RegisterTermsRowAction extends AdminTestCase {
	private static $container;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$container = apply_filters( 'rocket_container', '' );
	}

	public function testShouldAddCallbackForEachTerm() {
		$this->setRoleCap( 'administrator', 'rocket_purge_terms' );
		$this->setCurrentUser( 'administrator' );
		$this->setEditTagsAsCurrentScreen( 'post_tag' );
		$this->fireAdminInit();

		$taxonomies = get_taxonomies(
			[
				'public'             => true,
				'publicly_queryable' => true,
			]
		);

		$subscriber = self::$container->get( 'admin_cache_subscriber' );

		foreach( $taxonomies as $taxonomy ) {
			$this->assertSame(
				10,
				has_action( "{$taxonomy}_row_actions", [ $subscriber, 'add_purge_term_link' ] )
			);
		}
	}
}
