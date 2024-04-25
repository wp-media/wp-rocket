<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\AJAX;

use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Common\Context\ContextInterface;

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
	 * @param ATFQuery         $query ATFQuery instance.
	 * @param ContextInterface $context Context interface.
	 */
	public function __construct( ATFQuery $query, ContextInterface $context ) {
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

		$url       = isset( $_POST['url'] ) ? untrailingslashit( esc_url_raw( wp_unslash( $_POST['url'] ) ) ) : '';
		$is_mobile = isset( $_POST['is_mobile'] ) ? filter_var( wp_unslash( $_POST['is_mobile'] ), FILTER_VALIDATE_BOOLEAN ) : false;
		$images    = isset( $_POST['images'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['images'] ) ) ) : [];
		$lcp       = 'not found';
		$viewport  = [];

		/**
		 * Filters the maximum number of ATF images being saved into the database.
		 *
		 * @param int $max_number Maximum number to allow.
		 * @param string $url Current page url.
		 * @param string[]|array $images Current list of ATF images.
		 */
		$max_atf_images_number = (int) apply_filters( 'rocket_atf_images_number', 20, $url, $images );
		if ( 0 >= $max_atf_images_number ) {
			$max_atf_images_number = 1;
		}

		foreach ( $images as $image ) {
			if ( 'lcp' === $image->label && 'not found' === $lcp ) {
				// We should only get one LCP from the beacon.
				$lcp = (object) [
					'type' => 'img',
					'src'  => esc_url_raw( $image->src ),
				];
			} elseif ( 'above-the-fold' === $image->label ) {
				if ( 0 === $max_atf_images_number ) {
					continue;
				}
				$viewport[] = (object) [
					'type' => 'img',
					'src'  => esc_url_raw( $image->src ),
				];
				$max_atf_images_number--;
			}
		}

		$row = $this->query->get_row( $url, $is_mobile );

		if ( ! empty( $row ) ) {
			wp_send_json_error( 'item already in the database' );
			return;
		}

		$item = [
			'url'           => $url,
			'is_mobile'     => $is_mobile,
			'status'        => 'completed',
			'lcp'           => wp_json_encode( $lcp ),
			'viewport'      => wp_json_encode( $viewport ),
			'last_accessed' => current_time( 'mysql', true ),
		];

		$result = $this->query->add_item( $item );

		if ( ! $result ) {
			wp_send_json_error( 'error when adding the entry to the database' );
			return;
		}

		wp_send_json_success( $item );
	}
}
