<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreconnectExternalDomains\AJAX;

use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\AJAXControllerTrait;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\ControllerInterface;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Queries\PreconnectExternalDomains as PreconnectQuery;

class Controller implements ControllerInterface {
	use AJAXControllerTrait;

	/**
	 * Preconnect external domain instance
	 *
	 * @var PreconnectQuery
	 */
	private $query;

	/**
	 * PreconnectExternalDomains Context.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * Constructor
	 *
	 * @param PreconnectQuery  $query   Preconnect External Domains Query instance.
	 * @param ContextInterface $context Context interface.
	 */
	public function __construct( PreconnectQuery $query, ContextInterface $context ) {
		$this->query   = $query;
		$this->context = $context;
	}

	/**
	 * Add Preconnect external domains data to the database
	 *
	 * @return array
	 */
	public function add_data(): array {
		check_ajax_referer( 'rocket_beacon', 'rocket_beacon_nonce' );
		$payload = [];

		if ( ! $this->context->is_allowed() ) {
			$payload['preconnect_external_domains'] = 'not allowed';

			return $payload;
		}

		$url       = isset( $_POST['url'] ) ? untrailingslashit( esc_url_raw( wp_unslash( $_POST['url'] ) ) ) : '';
		$is_mobile = isset( $_POST['is_mobile'] ) ? filter_var( wp_unslash( $_POST['is_mobile'] ), FILTER_VALIDATE_BOOLEAN ) : false;
		$results   = isset( $_POST['results'] ) ? json_decode( wp_unslash( $_POST['results'] ) ) : (object) [ 'preconnect_external_domain' => [] ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$domains   = $results->preconnect_external_domain ?? [];

		$preconnect_domains = [];

		/**
		 * Filters the maximum number of preconnect external domains being saved into the database.
		 *
		 * @param int $max_number Maximum number to allow.
		 * @param string $url Current page url.
		 * @param string[]|array $hashes Current list of preconnect external domains.
		 */
		$max_preconnect_domains_number = wpm_apply_filters_typed( 'integer', 'rocket_preconnect_external_domains_number', 20, $url, $domains );
		if ( 0 >= $max_preconnect_domains_number ) {
			$max_preconnect_domains_number = 1;
		}

		foreach ( (array) $domains as $index => $domain ) {
			$preconnect_domains[ $index ] = sanitize_url( wp_unslash( $domain ) );
			--$max_preconnect_domains_number;
		}

		$row = $this->query->get_row( $url, $is_mobile );
		if ( ! empty( $row ) ) {
			$payload['preconnect_external_domains'] = 'item already in the database';

			return $payload;
		}

		$status                               = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
		list( $status_code, $status_message ) = $this->get_status_code_message( $status );

		$item = [
			'url'           => $url,
			'is_mobile'     => $is_mobile,
			'status'        => $status_code,
			'error_message' => $status_message,
			'domains'       => wp_json_encode( $preconnect_domains ),
			'created_at'    => current_time( 'mysql', true ),
			'last_accessed' => current_time( 'mysql', true ),
		];

		$result = $this->query->add_item( $item );

		if ( ! $result ) {
			$payload['preconnect_external_domains'] = 'error when adding the entry to the database';

			return $payload;
		}

		$payload['preconnect_external_domains'] = $item;

		return $payload;
	}

	/**
	 * Checks if there is existing data for the current URL and device type from the beacon script.
	 *
	 * This method is called via AJAX. It checks if there is existing preconnect domains data for the current URL and device type.
	 * If the data exists, it returns a JSON success response with true. If the data does not exist, it returns a JSON success response with false.
	 * If the context is not allowed, it returns a JSON error response with false.
	 *
	 * @return array
	 */
	public function check_data(): array {
		$payload = [
			'preconnect_external_domain' => false,
		];

		check_ajax_referer( 'rocket_beacon', 'rocket_beacon_nonce' );

		if ( ! $this->context->is_allowed() ) {
			$payload['preconnect_external_domain'] = true;

			return $payload;
		}

		$is_mobile = isset( $_POST['is_mobile'] ) ? filter_var( wp_unslash( $_POST['is_mobile'] ), FILTER_VALIDATE_BOOLEAN ) : false;
		$url       = isset( $_POST['url'] ) ? untrailingslashit( esc_url_raw( wp_unslash( $_POST['url'] ) ) ) : '';

		$row = $this->query->get_row( $url, $is_mobile );

		if ( ! empty( $row ) ) {
			$payload['preconnect_external_domain'] = true;
		}

		return $payload;
	}
}
