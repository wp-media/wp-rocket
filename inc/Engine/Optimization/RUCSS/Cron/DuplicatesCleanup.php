<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Cron;

use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Common\Clock\ClockInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS as UsedCSS_Table;

/**
 * One-time removal of the older duplicate (url, is_mobile) rows of the used CSS table.
 *
 * It waits for the unique key: without it, new duplicates could still appear after the cleanup.
 */
class DuplicatesCleanup {
	/**
	 * Cron event running the cleanup.
	 */
	const HOOK = 'rocket_rucss_delete_duplicate_rows';

	/**
	 * Transient limiting the cost of overlapping runs.
	 */
	const LOCK = 'rocket_rucss_delete_duplicate_rows_lock';

	/**
	 * Maximum number of rows deleted per query.
	 */
	const BATCH = 100;

	/**
	 * Time budget of one run, in seconds.
	 */
	const TIME_BUDGET = 20;

	/**
	 * Used CSS query instance.
	 *
	 * @var UsedCSS_Query
	 */
	private $query;

	/**
	 * Used CSS table instance.
	 *
	 * @var UsedCSS_Table
	 */
	private $table;

	/**
	 * Options API instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Clock instance.
	 *
	 * @var ClockInterface
	 */
	private $clock;

	/**
	 * Instantiate the class.
	 *
	 * @param UsedCSS_Query  $query       Used CSS query instance.
	 * @param UsedCSS_Table  $table       Used CSS table instance.
	 * @param Options        $options_api Options API instance.
	 * @param ClockInterface $clock       Clock instance.
	 */
	public function __construct( UsedCSS_Query $query, UsedCSS_Table $table, Options $options_api, ClockInterface $clock ) {
		$this->query       = $query;
		$this->table       = $table;
		$this->options_api = $options_api;
		$this->clock       = $clock;
	}

	/**
	 * Checks if the duplicates were already removed.
	 *
	 * @return bool
	 */
	public function is_done(): bool {
		return (bool) $this->options_api->get( UsedCSS_Table::DEDUPED_OPTION, false );
	}

	/**
	 * Removes duplicates within the time budget.
	 *
	 * @return string One of 'done', 'locked', 'not_ready', 'partial' or 'error'.
	 */
	public function run(): string {
		if ( $this->is_done() ) {
			return 'done';
		}

		if ( get_transient( self::LOCK ) ) {
			return 'locked';
		}

		if ( ! $this->table->index_exists( UsedCSS_Table::UNIQUE_KEY ) ) {
			return 'not_ready';
		}

		// The lock only limits cost: the deletions are idempotent, so overlapping runs are safe.
		set_transient( self::LOCK, time(), 5 * MINUTE_IN_SECONDS );

		$deadline = (int) $this->clock->current_time( 'timestamp', true ) + self::TIME_BUDGET;
		$result   = 'partial';

		do {
			$deleted = $this->query->delete_older_duplicates( self::BATCH );

			if ( false === $deleted ) {
				$result = 'error';
				break;
			}

			if ( 0 === $deleted ) {
				$this->complete();
				$result = 'done';
				break;
			}
		} while ( (int) $this->clock->current_time( 'timestamp', true ) < $deadline );

		delete_transient( self::LOCK );

		return $result;
	}

	/**
	 * Flags the cleanup as done and drops the stale job_id cache entries.
	 *
	 * @return void
	 */
	private function complete(): void {
		$this->options_api->set( UsedCSS_Table::DEDUPED_OPTION, 1 );

		if ( function_exists( 'wp_cache_supports' ) && wp_cache_supports( 'flush_group' ) ) {
			wp_cache_flush_group( 'wpr_used_css-by-job_id' );
		}
	}
}
