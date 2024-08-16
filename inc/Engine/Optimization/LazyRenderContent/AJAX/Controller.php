<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\AJAX;

use WP_Rocket\Engine\Common\PerformanceHints\AJAX\AbstractAJAXController;
use WP_Rocket\Engine\Optimization\UrlTrait;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\ControllerInterface;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Queries\LazyRenderContent as LRCQuery;

class Controller extends AbstractAJAXController implements ControllerInterface {
	use UrlTrait;

	/**
	 * LRCQuery instance
	 *
	 * @var LRCQuery
	 */
	private $query;

	/**
	 * Constructor
	 *
	 * @param LRCQuery $query LRCQuery instance.
	 */
	public function __construct( LRCQuery $query ) {
		$this->query = $query;
	}

	/**
	 * Add LRC data to the database
	 *
	 * @return void
	 */
	public function add_data(): void {
		check_ajax_referer( 'rocket_beacon', 'rocket_beacon_nonce' );

		$url            = isset( $_POST['url'] ) ? untrailingslashit( esc_url_raw( wp_unslash( $_POST['url'] ) ) ) : '';
		$is_mobile      = isset( $_POST['is_mobile'] ) ? filter_var( wp_unslash( $_POST['is_mobile'] ), FILTER_VALIDATE_BOOLEAN ) : false;
		$results        = isset( $_POST['results'] ) ? json_decode( wp_unslash( $_POST['results'] ) ) : (object) [ 'lrc' => [] ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$hashes         = $results->lrc ?? [];
		$below_the_fold = [];

		/**
		 * Filters the maximum number of LRC hashes being saved into the database.
		 *
		 * @param int $max_number Maximum number to allow.
		 * @param string $url Current page url.
		 * @param string[]|array $hashes Current list of LRC hashes.
		 */
		$max_lrc_hashes_number = (int) apply_filters( 'rocket_lrc_hashes_number', 20, $url, $hashes );
		if ( 0 >= $max_lrc_hashes_number ) {
			$max_lrc_hashes_number = 1;
		}

		foreach ( (array) $hashes as $hash ) {
			$below_the_fold[] = sanitize_text_field( wp_unslash( $hash ) );
			--$max_lrc_hashes_number;
		}

		$row = $this->query->get_row( $url, $is_mobile );
		if ( ! empty( $row ) ) {
			wp_send_json_error( 'item already in the database' );
			return;
		}

		$status                               = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
		list( $status_code, $status_message ) = $this->get_status_code_message( $status );

		$item = [
			'url'            => $url,
			'is_mobile'      => $is_mobile,
			'status'         => $status_code,
			'error_message'  => $status_message,
			'below_the_fold' => ( is_array( $below_the_fold ) || is_object( $below_the_fold ) ) ? wp_json_encode( $below_the_fold ) : $below_the_fold,
			'last_accessed'  => current_time( 'mysql', true ),
			'created_at'     => current_time( 'mysql', true ),
		];

		$result = $this->query->add_item( $item );

		if ( ! $result ) {
			wp_send_json_error( 'error when adding the entry to the database' );
			return;
		}

		wp_send_json_success( $item );
	}

	/**
	 * Checks if there is existing data for the current URL and device type from the beacon script.
	 *
	 * This method is called via AJAX. It checks if there is existing LRC data for the current URL and device type.
	 * If the data exists, it returns a JSON success response with true. If the data does not exist, it returns a JSON success response with false.
	 * If the context is not allowed, it returns a JSON error response with false.
	 *
	 * @return void
	 */
	public function check_data(): void {
		check_ajax_referer( 'rocket_beacon', 'rocket_beacon_nonce' );

		$url       = isset( $_POST['url'] ) ? untrailingslashit( esc_url_raw( wp_unslash( $_POST['url'] ) ) ) : '';
		$is_mobile = isset( $_POST['is_mobile'] ) ? filter_var( wp_unslash( $_POST['is_mobile'] ), FILTER_VALIDATE_BOOLEAN ) : false;

		$row = $this->query->get_row( $url, $is_mobile );

		if ( ! empty( $row ) ) {
			wp_send_json_success( 'data already exists' );
			return;
		}

		wp_send_json_error( 'data does not exist' );
	}
}
