<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Rows;

use WP_Rocket\Dependencies\BerlinDB\Database\Row;

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
	 * Is the request for mobile
	 *
	 * @var bool
	 */
	public $is_mobile;

	/**
	 * Test ID
	 *
	 * @var string
	 */
	public $test_id;

	/**
	 * Error message
	 *
	 * @var string
	 */
	public $error_message;

	/**
	 * Status
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Result of the test
	 *
	 * @var string
	 */
	public $data;

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
	 * Constructor
	 *
	 * @param mixed $item Object Row.
	 */
	public function __construct( $item ) {
		parent::__construct( $item );

		$this->id            = (int) $item->id;
		$this->url           = (string) $item->url;
		$this->is_mobile     = (bool) $item->is_mobile;
		$this->test_id       = (string) $item->test_id;
		$this->error_message = (string) $item->error_message;
		$this->status        = (string) $item->status;
		$this->data          = (string) $item->data;
		$this->modified      = (int) $item->modified;
		$this->last_accessed = (int) $item->last_accessed;
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
	 * Get the performance score from the stored data.
	 *
	 * @return int|null
	 */
	public function get_performance_score(): ?int {
		if ( empty( $this->data ) ) {
			return null;
		}

		$data = json_decode( $this->data, true );

		return isset( $data['performance_score'] ) ? (int) $data['performance_score'] : null;
	}

	/**
	 * Get the report URL from the stored data.
	 *
	 * @return string|null
	 */
	public function get_report_url(): ?string {
		if ( empty( $this->data ) ) {
			return null;
		}

		$data = json_decode( $this->data, true );

		return $data['report_url'] ?? null;
	}

	/**
	 * Check if test is still in progress.
	 *
	 * @return bool
	 */
	public function is_running(): bool {
		return in_array( $this->status, [ 'pending', 'running' ], true );
	}

	/**
	 * Check if test has failed.
	 *
	 * @return bool
	 */
	public function has_failed(): bool {
		return 'failed' === $this->status;
	}

	/**
	 * Get all core web vitals from the stored data.
	 *
	 * @return array
	 */
	public function get_core_web_vitals(): array {
		if ( empty( $this->data ) ) {
			return [];
		}

		$data = json_decode( $this->data, true );

		return [
			'largest_contentful_paint' => $data['largest_contentful_paint'] ?? null,
			'total_blocking_time'      => $data['total_blocking_time'] ?? null,
			'cumulative_layout_shift'  => $data['cumulative_layout_shift'] ?? null,
			'first_contentful_paint'   => $data['first_contentful_paint'] ?? null,
		];
	}
}
