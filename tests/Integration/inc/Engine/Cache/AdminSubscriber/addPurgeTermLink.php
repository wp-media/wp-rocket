<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdminSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdminSubscriber;
use WP_Rocket\Tests\Integration\AdminTestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering WP_Rocket\Engine\Cache\AdminSubscriber::add_purge_term_link
 *
 * @group AdminOnly
 * @group Cache
 */
class AddPurgeTermLinkTest extends AdminTestCase {
	use HttpRequestTrait;

	private $tag;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::removeDBHooks();
	}

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		// The test only checks that the row action is registered, not the license/upgrade
		// flow rocket_upgrader() runs on admin_init when the 'version' option is missing.
		add_filter( 'pre_get_rocket_option_version', [ $this, 'mock_version' ] );

		self::installAtfTable();
		self::installLrcTable();
		self::installPreloadFontsTable();
		self::installPreconnectExternalDomainsTable();

		$existing_tag = get_term_by( 'name', 'Ipseum', 'post_tag' );
		if ( $existing_tag instanceof \WP_Term ) {
			wp_delete_term( $existing_tag->term_id, 'post_tag' );
		}
	}

	public function tear_down() {
		if ( $this->tag instanceof \WP_Term ) {
			wp_delete_term( $this->tag->term_id, 'post_tag' );
		}

		self::uninstallAtfTable();
		self::uninstallLrcTable();
		self::uninstallPreloadFontsTable();
		self::uninstallPreconnectDomainsTable();

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

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddCallbackForEachTerm( $config, $expected ) {
		$this->tag = self::factory()->tag->create_and_get( [ 'name' => 'Ipseum' ] );

		if ( $config['cap'] ) {
			$this->setRoleCap( 'administrator', 'rocket_purge_terms' );
			$this->setCurrentUser( 'administrator' );
			Functions\expect( 'wp_create_nonce' )
				->once()
				->with( "purge_cache_term-{$this->tag->term_id}" )
				->andReturn( $config['nonce'] );
		}
		$this->setEditTagsAsCurrentScreen( 'post_tag' );

		$this->fireAdminInit();

		$this->hasCallbackRegistered( 'post_tag_row_actions', AdminSubscriber::class, 'add_purge_term_link' );

		$actions = apply_filters( 'post_tag_row_actions', [], $this->tag );

		if ( $config['cap'] ) {
			$this->assertArrayHasKey( 'rocket_purge', $actions );

			// Populate the term's ID.
			$expected = str_replace( 'term-1', "term-{$this->tag->term_id}", $expected );
			$this->assertSame( $expected, $actions['rocket_purge'] );
		} else {
			$this->assertArrayNotHasKey( 'rocket_purge', $actions );
		}
	}
}
