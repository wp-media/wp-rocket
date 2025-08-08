<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\PerformanceMonitoring\Database\Rows;

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
	 * @param mixed $item
	 */
	public function __construct( $item ) {
		parent::__construct( $item );

		$this->id			 = (int) $item->id;
		$this->url			 = (string) $item->url;
		$this->is_mobile	 = (bool) $item->is_mobile;
		$this->test_id		 = (string) $item->test_id;
		$this->error_message  = (string) $item->error_message;
		$this->status		 = (string) $item->status;
		$this->data			 = (string) $item->data;
		$this->modified		 = (int) $item->modified;
		$this->last_accessed	 = (int) $item->last_accessed;
	}

	/**
	 * Checks if the object has a valid result value.
	 * 
	 * @return bool Returns true if the object's status is 'completed' and the result is not empty, false otherwise
	 */
	public function has_result() : bool {
		if ( 'completed' !== $this->status ) {
			return false;
		}
		
		return ! empty( $this->data );
	}

}
