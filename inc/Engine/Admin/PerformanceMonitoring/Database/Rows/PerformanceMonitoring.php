<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Rows;

use WP_Rocket\Dependencies\BerlinDB\Database\Row;
use WP_Rocket\Engine\Common\Utils;

class PerformanceMonitoring extends Row {
	/**
	 * Row ID
	 *
	 * @var int
	 */
	public $id;

	/**
	 * URL
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Error code
	 *
	 * @var string
	 */
	public $error_code;

	/**
	 * Error message
	 *
	 * @var string
	 */
	public $error_message;

	/**
	 * Number of retries
	 *
	 * @var int
	 */
	public $retries;

	/**
	 * Is CSS for mobile
	 *
	 * @var bool
	 */
	public $is_mobile;

	/**
	 * Job ID
	 *
	 * @var string
	 */
	public $job_id;

	/**
	 * Job queue name
	 *
	 * @var string
	 */
	public $queue_name;

	/**
	 * Status
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Last modified time
	 *
	 * @var int
	 */
	public $modified;

	/**
	 * Last accessed time
	 *
	 * @var int
	 */
	public $last_accessed;

	/**
	 * Submitted date
	 *
	 * @var int
	 */
	public $submitted_at;

	/**
	 * Tells when the retry has to be processed
	 *
	 * @var int
	 */
	public $next_retry_time;

	/**
	 * Data column.
	 *
	 * @var array|mixed
	 */
	public $data;

	/**
	 * Score column.
	 *
	 * @var int
	 */
	public $score;

	/**
	 * Report URL column.
	 *
	 * @var string
	 */
	public $report_url;

	/**
	 * Is Blurred column.
	 *
	 * @var bool
	 */
	public $is_blurred;

	/**
	 * Constructor
	 *
	 * @param mixed $item Object Row.
	 */
	public function __construct( $item ) {
		parent::__construct( $item );

		$this->id              = (int) $item->id;
		$this->url             = (string) $item->url;
		$this->title           = (string) $item->title;
		$this->is_mobile       = (bool) $item->is_mobile;
		$this->job_id          = (string) $item->job_id;
		$this->queue_name      = (string) $item->queue_name;
		$this->retries         = (int) $this->retries;
		$this->status          = (string) $this->status;
		$this->data            = ! empty( $item->data ) ? json_decode( $item->data, true ) : [];
		$this->modified        = empty( $this->modified ) ? 0 : strtotime( (string) $this->modified );
		$this->last_accessed   = empty( $this->last_accessed ) ? 0 : strtotime( (string) $this->last_accessed );
		$this->submitted_at    = empty( $this->submitted_at ) ? 0 : strtotime( (string) $this->submitted_at );
		$this->next_retry_time = empty( $this->next_retry_time ) ? 0 : strtotime( (string) $this->next_retry_time );
		$this->score           = (int) $this->score;
		$this->report_url      = (string) $this->report_url;
		$this->is_blurred      = (bool) $this->is_blurred;
		$this->error_code      = (string) $this->error_code;
		$this->error_message   = (string) $this->error_message;
	}

	/**
	 * Checks if the object has a valid result value.
	 *
	 * @return bool Returns true if the object's status is 'completed' and the result is not empty, false otherwise
	 */
	public function has_result(): bool {
		if ( 'completed' !== $this->status ) {
			return false;
		}

		return ! empty( $this->data );
	}

	/**
	 * Check if test is still in progress.
	 *
	 * @return bool
	 */
	public function is_running(): bool {
		return in_array( $this->status, [ 'to-submit', 'pending', 'in-progress' ], true );
	}

	/**
	 * Check if test has failed.
	 *
	 * @return bool
	 */
	public function is_failed(): bool {
		return 'failed' === $this->status;
	}

	/**
	 * Get the delete url.
	 *
	 * @return string
	 */
	public function delete_url() {
		return Utils::get_nonce_post_url( 'delete_pm', [ 'id' => $this->id ] );
	}

	/**
	 * Check if the report can be accessed.
	 *
	 * @return bool
	 */
	public function can_access_report(): bool {
		return ! empty( $this->report_url ) && ! $this->is_blurred;
	}

	/**
	 * Check if the report can be accessed.
	 *
	 * @return bool
	 */
	public function can_re_test(): bool {
		return ! $this->is_running() && ! $this->is_blurred;
	}
}
