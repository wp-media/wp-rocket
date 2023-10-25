<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Admin;

use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;

class Controller {
	/**
	 * ATF Query instance
	 *
	 * @var ATFQuery
	 */
	private $query;

	/**
	 * Constructor
	 *
	 * @param ATFQuery $query ATF Query instance.
	 */
	public function __construct( ATFQuery $query ) {
		$this->query = $query;
	}
}
