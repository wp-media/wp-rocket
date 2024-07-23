<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Admin;

use WP_Rocket\Engine\Common\PerformanceHints\Admin\ControllerInterface;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold as ATFTable;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;

class Controller implements ControllerInterface {
	/**
	 * ATF Table instance
	 *
	 * @var ATFTable
	 */
	private $table;

	/**
	 * ATF Query instance
	 *
	 * @var ATFQuery
	 */
	private $query;

	/**
	 * Context instance
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor
	 *
	 * @param ATFTable $table Table instance.
	 * @param ATFQuery $query ATF Query instance.
	 * @param Context  $context Context instance.
	 */
	public function __construct( ATFTable $table, ATFQuery $query, Context $context ) {
		$this->table   = $table;
		$this->query   = $query;
		$this->context = $context;
	}


	/**
	 * Cleans rows for the current URL.
	 *
	 * @return void
	 */
	public function clean_url(): void {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_nonce_ays( '' );
		}

		$url = wp_get_referer();

		if ( 0 !== strpos( $url, 'http' ) ) {
			$parse_url = get_rocket_parse_url( untrailingslashit( home_url() ) );
			$url       = $parse_url['scheme'] . '://' . $parse_url['host'] . $url;
		}

		$this->query->delete_by_url( untrailingslashit( $url ) );
	}
}
