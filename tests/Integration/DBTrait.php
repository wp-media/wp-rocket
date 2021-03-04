<?php

namespace WP_Rocket\Tests\Integration;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\Resources;

trait DBTrait {
	public static function uninstallDBTables() {
		global $wpdb;
		$table_name = $wpdb->base_prefix . 'wpr_rucss_resources';
		$query      = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

		if ( ! $wpdb->get_var( $query ) == $table_name ) {
			$rocket_rucss_resources_table = new Resources();
			$rocket_rucss_resources_table->uninstall();
		}
	}
}
