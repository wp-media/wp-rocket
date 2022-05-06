<?php

namespace WP_Rocket\Tests\Integration;

use WP_Rocket\Tests\StubTrait;
use WP_Rocket\Tests\Integration\TestCase as BaseTestCase;

abstract class AdminTestCase extends BaseTestCase {
	use StubTrait;
	use DBTrait;

	protected $error_level;
	protected $user_id = 0;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		remove_action( 'admin_init', '_maybe_update_core' );
		remove_action( 'admin_init', '_maybe_update_plugins' );
		remove_action( 'admin_init', '_maybe_update_themes' );
		remove_action( 'admin_init', array( 'WP_Privacy_Policy_Content', 'add_suggested_content' ), 1 );
	}

	public function set_up() {
		parent::set_up();

		DBTrait::removeDBHooks();

		$this->stubRocketGetConstant();

		// Suppress warnings from "Cannot modify header information - headers already sent by".
		$this->error_level = error_reporting();
		error_reporting( $this->error_level & ~E_WARNING );
	}

	public function tear_down(): void {
		$_POST = [];
		$_GET  = [];
		unset( $GLOBALS['post'], $GLOBALS['comment'] );

		$this->resetStubProperties();

		error_reporting( $this->error_level );
		set_current_screen( 'front' );
		if ( $this->user_id > 0 ) {
			wp_delete_user( $this->user_id );
		}

		parent::tear_down();
	}

	protected function setRoleCap( $role_type, $cap ) {
		$role = get_role( $role_type );
		$role->add_cap( $cap );
	}

	protected function removeRoleCap( $role_type, $cap ) {
		$role = get_role( $role_type );
		$role->remove_cap( $cap );
	}

	protected function setCurrentUser( $role ) {
		$this->user_id = $this->factory->user->create( [ 'role' => $role ] );
		wp_set_current_user( $this->user_id );
	}

	protected function fireAdminInit() {
		do_action( 'admin_init' );
	}

	protected function hasCallbackRegistered( $event, $class, $method, $priority = 10 ) {
		global $wp_filter;

		$this->assertArrayHasKey( $event, $wp_filter );
		$this->assertArrayHasKey( $priority, $wp_filter[ $event ]->callbacks );

		$object = null;
		foreach ( $wp_filter['post_tag_row_actions']->callbacks[ $priority ] as $key => $callback ) {
			if ( isset( $callback['function'][1] ) && $method === $callback['function'][1] ) {
				$object = $callback['function'][0];
				break;
			}
		}
		$this->assertInstanceOf( $class, $object );
	}

	protected function setEditTagsAsCurrentScreen( $tax = 'category' ) {
		set_current_screen( "edit-tags.php?taxonomy={$tax}" );
	}
}
