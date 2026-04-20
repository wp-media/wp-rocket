<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN\Database\Rows;

use WP_Rocket\Dependencies\BerlinDB\Database\Row;

/**
 * RocketCDN Row class.
 */
class RocketCDN extends Row {
	/**
	 * Row ID.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Page title.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Page URL.
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Last modified time.
	 *
	 * @var string
	 */
	public $modified;

	/**
	 * Last accessed time.
	 *
	 * @var string
	 */
	public $last_accessed;

	/**
	 * Constructor.
	 *
	 * @param mixed $item Object row from the database.
	 */
	public function __construct( $item ) {
		parent::__construct( $item );

		$this->id            = (int) $this->id;
		$this->title         = (string) $this->title;
		$this->url           = (string) $this->url;
		$this->modified      = (string) $this->modified;
		$this->last_accessed = (string) $this->last_accessed;
	}
}