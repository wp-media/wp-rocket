<?php


namespace Action_Scheduler\Migration;

/**
 * Class DryRun_LogMigrator
 *
 * @package Action_Scheduler\Migration
 *
 * @codeCoverageIgnore
 */
class DryRun_LogMigrator extends LogMigrator {
	public function migrate( $source_action_id, $destination_action_id ) {
		// no-op
	}

}