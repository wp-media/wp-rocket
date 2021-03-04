<?php

namespace WP_Rocket\Tests\Integration;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\Resources;

trait DBTrait {
	public static function uninstallDBTables() {
		$rocket_rucss_resources_table = new Resources();
		$rocket_rucss_resources_table->uninstall();
	}
}
