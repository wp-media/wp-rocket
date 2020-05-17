<?php

namespace WP_Rocket\Tests\Integration;

trait CapTrait {
	public static $had_cap = false;

	public static function hasAdminCapBeforeClass() {
		$admin           = get_role( 'administrator' );
		static::$had_cap = $admin->has_cap( 'rocket_manage_options' );
	}

	public static function setAdminCap() {
		$admin = get_role( 'administrator' );
		if ( ! static::$had_cap ) {
			$admin->add_cap( 'rocket_manage_options' );
		}
	}

	public static function resetAdminCap() {
		$admin = get_role( 'administrator' );
		if ( ! static::$had_cap ) {
			$admin->remove_cap( 'rocket_manage_options' );
		}
	}
}
