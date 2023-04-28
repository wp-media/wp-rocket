<?php

namespace WP_Rocket\Engine\Logger;

class CacheCleanStreamHandler extends StreamHandler {

	/**
	 * Defines if the handler can handle the log.
	 *
	 * @param array $record log to handle.
	 * @return bool
	 */
	public function isHandling( array $record ): bool {
		$is_handling = parent::isHandling( $record );
		if ( ! $is_handling ) {
			return false;
		}

		if ( ! key_exists( 'context', $record ) || ! key_exists( 'type', $record['context'] ) ) {
			return true;
		}
		return 'cache-clearing' === $record['context']['type'];
	}
}
