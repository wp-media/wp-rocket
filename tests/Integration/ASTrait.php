<?php

namespace WP_Rocket\Tests\Integration;

use ActionScheduler_DBStore;

trait ASTrait {
	public static function countTasks( string $hook ) {
		return ActionScheduler_DBStore::instance()->query_actions( ['hook' => $hook], 'count' );
	}

	public static function taskExist( string $hook, array $args = [] ): bool {
		return count( ActionScheduler_DBStore::instance()->query_actions( ['hook' => $hook, 'args' => $args] ) ) > 0;
	}
}
