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
			wp_send_json_error( 'not allowed' );
			return;
		}

		$url       = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		$is_mobile = isset( $_POST['is_mobile'] ) ? wp_unslash( (bool) $_POST['is_mobile'] ) : false;
		$status    = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
		$lcp       = isset( $_POST['lcp'] ) ? wp_json_encode( wp_unslash( $_POST['lcp'] ), JSON_UNESCAPED_SLASHES ) : 'not found';
		$viewport  = isset( $_POST['viewport'] ) ? wp_json_encode( wp_unslash( $_POST['viewport'] ), JSON_UNESCAPED_SLASHES ) : 'not found';

		$item = [
			'url'           => untrailingslashit( $url ),
			'is_mobile'     => $is_mobile,
			'status'        => $status,
			'lcp'           => $lcp,
			'viewport'      => $viewport,
			'last_accessed' => current_time( 'mysql', true ),
		];

		$insert = $this->query->add_item( $item );

		if ( ! $insert ) {
			wp_send_json_error( 'error when inserting the item in the database' );
			return;
		}

		wp_send_json_success( 'item added to the database' );
	}
}
