<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdminSubscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering WP_Rocket\Engine\Cache\AdminSubscriber::register_terms_row_action
 *
 * @group AdminOnly
 * @group Cache
 */
class Test_RegisterTermsRowAction extends AdminTestCase {
	use HttpRequestTrait;

	private static $container;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::$container = apply_filters( 'rocket_container', '' );
	}

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		// The test only checks that the row action is registered, not the license/upgrade
		// flow rocket_upgrader() runs on admin_init when the 'version' option is missing.
		add_filter( 'pre_get_rocket_option_version', [ $this, 'mock_version' ] );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_version', [ $this, 'mock_version' ] );

		$this->tear_down_http();

		parent::tear_down();
	}

	/**
	 * Reports the running version as the installed one, so rocket_upgrader() takes
	 * neither the first-install nor the upgrade branch.
	 *
	 * @return string
	 */
	public function mock_version() {
		return WP_ROCKET_VERSION;
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
