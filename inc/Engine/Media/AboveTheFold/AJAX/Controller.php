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
		check_ajax_referer( 'rocket_lcp', 'rocket_lcp_nonce' );

		if ( ! $this->context->is_allowed() ) {
			wp_send_json_error( 'not allowed' );
			return;
		}

		$url       = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		$is_mobile = isset( $_POST['is_mobile'] ) ? wp_unslash( (bool) $_POST['is_mobile'] ) : false;
		$images    = isset( $_POST['images'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['images'] ) ) ) : '';
		$lcp       = [];
		$viewport  = [];

		foreach ( $images as $image ) {
			if ( 'lcp' === $image->label ) {
				$lcp[] = (object) [
					'type' => 'img',
					'src'  => $image,
				];
			} elseif ( 'above-the-fold' === $image->label ) {
				$viewport[] = (object) [
					'type' => 'img',
					'src'  => $image,
				];
			}
		}

		$item = [
			'url'           => untrailingslashit( $url ),
			'is_mobile'     => $is_mobile,
			'status'        => 'completed',
			'lcp'           => $lcp,
			'viewport'      => $viewport,
			'last_accessed' => current_time( 'mysql', true ),
		];

		$this->query->add_item( $item );

		wp_send_json_success( $item );
	}
}
