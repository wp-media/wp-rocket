<?php
/**
 * MCP Observability Handler.
 *
 * Bridges the MCP adapter's observability interface with WP Rocket's existing
 * McpLogger, providing structured request/event metrics without external
 * dependencies. Implements the single-method McpObservabilityHandlerInterface.
 */

declare( strict_types=1 );

namespace WP_Rocket\Engine\MCP\Transport;

use WP\MCP\Infrastructure\Observability\Contracts\McpObservabilityHandlerInterface;
use WP\MCP\Infrastructure\Observability\McpObservabilityHelperTrait;
use WP_Rocket\Engine\MCP\Auth\McpLogger;

/**
 * Observability handler that writes MCP events to the WP Rocket MCP log.
 */
class McpObservabilityHandler implements McpObservabilityHandlerInterface {
	use McpObservabilityHelperTrait;

	/**
	 * Record an MCP event, optionally with timing data.
	 *
	 * Events are written via McpLogger under the 'OBSERVABILITY' scope.
	 * Debug-only logging is used for high-frequency request events to avoid
	 * polluting production logs; all error/failure events are always logged.
	 *
	 * @param string     $event       Event name (e.g. 'mcp.request', 'mcp.component.registration').
	 * @param array      $tags        Key-value context tags (status, method, tool_name, etc.).
	 * @param float|null $duration_ms Optional request duration in milliseconds.
	 * @return void
	 */
	public function record_event( string $event, array $tags = [], ?float $duration_ms = null ): void {
		$formatted_event = self::format_metric_name( $event );
		$merged_tags     = self::merge_tags( $tags );

		if ( null !== $duration_ms ) {
			$merged_tags['duration_ms'] = round( $duration_ms, 2 );
		}

		// Log failures unconditionally; debug-only for successful/routine events.
		$is_error   = isset( $merged_tags['status'] ) && 'error' === $merged_tags['status'];
		$debug_only = ! $is_error;

		McpLogger::log( 'OBSERVABILITY', $formatted_event, $merged_tags, $debug_only );
	}
}
