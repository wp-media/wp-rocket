<?php

use Action_Scheduler\Migration\ActionScheduler_MigrationConfig;

/**
 * Class ActionScheduler_MigrationConfig_Test
 * @group migration
 */
class ActionScheduler_MigrationConfig_Test extends ActionScheduler_UnitTestCase {
	public function test_source_store_required() {
		$config = new ActionScheduler_MigrationConfig();
		$this->expectException( \RuntimeException::class );
		$config->get_source_store();
	}

	public function test_source_logger_required() {
		$config = new ActionScheduler_MigrationConfig();
		$this->expectException( \RuntimeException::class );
		$config->get_source_logger();
	}

	public function test_destination_store_required() {
		$config = new ActionScheduler_MigrationConfig();
		$this->expectException( \RuntimeException::class );
		$config->get_destination_store();
	}

	public function test_destination_logger_required() {
		$config = new ActionScheduler_MigrationConfig();
		$this->expectException( \RuntimeException::class );
		$config->get_destination_logger();
	}
}