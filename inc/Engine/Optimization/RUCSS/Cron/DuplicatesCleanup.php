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
 * The table is walked by bounded ID ranges up to the highest ID known when the job started:
 * the unique key prevents new duplicates, so rows above it do not need scanning.
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
	 * Option (unprefixed) holding the progress of the walk: the last scanned ID and the ID to stop at.
	 */
	const PROGRESS_OPTION = 'rucss_used_css_dedupe_progress';

	/**
	 * Maximum number of rows deleted per query.
	 */
	const BATCH = 100;

	/**
	 * Number of IDs scanned per query.
	 */
	const CHUNK = 5000;

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
	 * @param Options        $options_api Options API instance.
	 * @param ClockInterface $clock       Clock instance.
	 */
	public function __construct( UsedCSS_Query $query, Options $options_api, ClockInterface $clock ) {
		$this->query       = $query;
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
	 * @return string One of 'done', 'locked', 'partial' or 'error'.
	 */
	public function run(): string {
		if ( $this->is_done() ) {
			return 'done';
		}

		if ( get_transient( self::LOCK ) ) {
			return 'locked';
		}

		$minute = (int) rocket_get_constant( 'MINUTE_IN_SECONDS', 60 );

		// The lock only limits cost: the deletions are idempotent, so overlapping runs are safe.
		set_transient( self::LOCK, time(), 5 * $minute );

		$progress = $this->get_progress();

		if ( false === $progress ) {
			delete_transient( self::LOCK );

			return 'error';
		}

		$deadline = (int) $this->clock->current_time( 'timestamp', true ) + self::TIME_BUDGET;

		while ( $progress['cursor'] < $progress['max'] ) {
			$to_id   = min( $progress['cursor'] + self::CHUNK, $progress['max'] );
			$deleted = $this->query->delete_older_duplicates( $progress['cursor'], $to_id, self::BATCH );

			if ( false === $deleted ) {
				delete_transient( self::LOCK );

				return 'error';
			}

			// A full batch may leave more duplicates in this range: scan it again.
			if ( $deleted < self::BATCH ) {
				$progress['cursor'] = $to_id;
				$this->options_api->set( self::PROGRESS_OPTION, $progress );
			}

			if ( (int) $this->clock->current_time( 'timestamp', true ) >= $deadline ) {
				break;
			}
		}

		if ( $progress['cursor'] < $progress['max'] ) {
			delete_transient( self::LOCK );

			return 'partial';
		}

		$this->complete();

		delete_transient( self::LOCK );

		return 'done';
	}

	/**
	 * Gets the saved progress, or starts a new walk bounded by the current highest ID.
	 *
	 * @return array{cursor: int, max: int}|false False when the highest ID cannot be read.
	 */
	private function get_progress() {
		$progress = $this->options_api->get( self::PROGRESS_OPTION, [] );

		if ( isset( $progress['cursor'], $progress['max'] ) ) {
			return [
				'cursor' => (int) $progress['cursor'],
				'max'    => (int) $progress['max'],
			];
		}

		$max_id = $this->query->get_max_id();

		if ( false === $max_id ) {
			return false;
		}

		$progress = [
			'cursor' => 0,
			'max'    => $max_id,
		];

		// Created without autoload: it is only read by the cron job. Later writes keep that setting.
		add_option( 'wp_rocket_' . self::PROGRESS_OPTION, $progress, '', false );

		return $progress;
	}

	/**
	 * Flags the cleanup as done and drops the stale job_id cache entries.
	 *
	 * @return void
	 */
	private function complete(): void {
		$this->options_api->set( UsedCSS_Table::DEDUPED_OPTION, 1 );
		$this->options_api->delete( self::PROGRESS_OPTION );

		if ( function_exists( 'wp_cache_supports' ) && wp_cache_supports( 'flush_group' ) ) {
			wp_cache_flush_group( 'wpr_used_css-by-job_id' );
		}
	}
}
