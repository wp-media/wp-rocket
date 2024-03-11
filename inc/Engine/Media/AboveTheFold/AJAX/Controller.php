<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\AJAX;

use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;

class Controller {
	/**
	 * ATFQuery instance
	 *
	 * @var ATFQuery
	 */
	private $query;

	/**
	 * LCP Context.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * Constructor
	 *
	 * @param ATFQuery $query ATFQuery instance.
	 * @param Context  $context Context interface.
	 */
	public function __construct( ATFQuery $query, Context $context ) {
		$this->query   = $query;
		$this->context = $context;
	}

	/**
	 * Add LCP data to the database
	 *
	 * @return bool
	 */
	public function add_lcp_data() {
		check_ajax_referer( 'rocket_lcp' );

		if ( ! $this->context->is_allowed() ) {
			return;
		}

		$item = [
			'url'           => untrailingslashit( $url ),
			'is_mobile'     => $is_mobile,
			'status'        => $status,
			'lcp'           => $lcp,
			'viewport'      => $viewport,
			'last_accessed' => current_time( 'mysql', true ),
		];

		return $this->query->add_item( $item );
	}
}
