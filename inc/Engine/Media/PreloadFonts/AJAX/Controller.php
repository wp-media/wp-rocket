<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreloadFonts\AJAX;

use WP_Rocket\Engine\Media\PreloadFonts\Context\Context;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\AJAXControllerTrait;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\ControllerInterface;
use WP_Rocket\Engine\Media\PreloadFonts\Database\Queries\PreloadFonts as PreloadFontsQuery;
use WP_Rocket\Engine\Optimization\UrlTrait;

class Controller implements ControllerInterface {
	use AJAXControllerTrait;

	/**
	 * PLFQuery instance
	 *
	 * @var PreloadFontsQuery
	 */
	private $query;

	/**
	 * PreloadFonts Context.
	 *
	 * @var Context
	 */
	protected $context;

	/**
	 * Constructor
	 *
	 * @param PreloadFontsQuery $query   PLFQuery instance.
	 * @param Context           $context Context instance.
	 */
	public function __construct( PreloadFontsQuery $query, Context $context ) {
		$this->query   = $query;
		$this->context = $context;
	}


	/**
	 * Add Preload fonts data to the database
	 *
	 * @return array
	 */
	public function add_data(): array {
		check_ajax_referer( 'rocket_beacon', 'rocket_beacon_nonce' );
		$payload = [];

		if ( ! $this->context->is_allowed() ) {
			$payload['preload_fonts'] = 'not allowed';

			return $payload;
		}

		$url       = isset( $_POST['url'] ) ? untrailingslashit( esc_url_raw( wp_unslash( $_POST['url'] ) ) ) : '';
		$is_mobile = isset( $_POST['is_mobile'] ) ? filter_var( wp_unslash( $_POST['is_mobile'] ), FILTER_VALIDATE_BOOLEAN ) : false;
		$results   = isset( $_POST['results'] ) ? json_decode( wp_unslash( $_POST['results'] ) ) : (object) [ 'preload_fonts' => [] ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$fonts     = $results->preload_fonts ?? [];

		$preload_fonts = [];

		/**
		 * Filters the maximum number of fonts being saved into the database.
		 *
		 * @param int $max_number Maximum number to allow.
		 * @param string $url Current page url.
		 * @param string[]|array $hashes Current list of preload fonts.
		 */
		$max_preload_fonts_number = wpm_apply_filters_typed( 'integer', 'rocket_preload_fonts_number', 20, $url, $fonts );
		if ( 0 >= $max_preload_fonts_number ) {
			$max_preload_fonts_number = 1;
		}

		$fonts = $this->filter_fonts( $fonts, $this->context->get_exclusions() );

		foreach ( (array) $fonts as $index => $font ) {
			$preload_fonts[ $index ] = sanitize_url( wp_unslash( $font ) );
			--$max_preload_fonts_number;
		}

		$row = $this->query->get_row( $url, $is_mobile );
		if ( ! empty( $row ) ) {
			$payload['preload_fonts'] = 'item already in the database';

			return $payload;
		}

		$status                               = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
		list( $status_code, $status_message ) = $this->get_status_code_message( $status );

		$item = [
			'url'           => $url,
			'is_mobile'     => $is_mobile,
			'status'        => $status_code,
			'error_message' => $status_message,
			'fonts'         => wp_json_encode( $preload_fonts ),
			'created_at'    => current_time( 'mysql', true ),
			'last_accessed' => current_time( 'mysql', true ),
		];

		$result = $this->query->add_item( $item );

		if ( ! $result ) {
			$payload['preload_fonts'] = 'error when adding the entry to the database';

			return $payload;
		}

		$payload['preload_fonts'] = $item;

		return $payload;
	}

	/**
	 * Checks if there is existing data for the current URL and device type from the beacon script.
	 *
	 * This method is called via AJAX. It checks if there is existing fonts data for the current URL and device type.
	 * If the data exists, it returns a JSON success response with true. If the data does not exist, it returns a JSON success response with false.
	 * If the context is not allowed, it returns a JSON error response with false.
	 *
	 * @return array
	 */
	public function check_data(): array {
		$payload = [
			'preload_fonts' => false,
		];

		check_ajax_referer( 'rocket_beacon', 'rocket_beacon_nonce' );

		if ( ! $this->context->is_allowed() ) {
			$payload['preload_fonts'] = true;

			return $payload;
		}

		$is_mobile = isset( $_POST['is_mobile'] ) ? filter_var( wp_unslash( $_POST['is_mobile'] ), FILTER_VALIDATE_BOOLEAN ) : false;
		$url       = isset( $_POST['url'] ) ? untrailingslashit( esc_url_raw( wp_unslash( $_POST['url'] ) ) ) : '';

		$row = $this->query->get_row( $url, $is_mobile );

		if ( ! empty( $row ) ) {
			$payload['preload_fonts'] = true;
		}

		return $payload;
	}

	/**
	 * Filter font urls before saving into DB by checking exclusions list and extensions.
	 *
	 * @param array $fonts Array of fonts to be preloaded.
	 * @param array $exclusions Array of fonts to be excluded.
	 *
	 * @return array Filtered array of fonts, excluding those specified in the exclusion list.
	 */
	private function filter_fonts( array $fonts, array $exclusions ): array {
		if ( empty( $exclusions ) ) {
			return $fonts;
		}

		/**
		 * Create a single regex pattern from all exclusions.
		 * Use a different delimiter (#) to avoid issues with URLs containing slashes.
		 */
		$pattern    = '#(' . implode( '|', array_map( 'preg_quote', $exclusions ) ) . ')#i';
		$extensions = $this->context->get_extensions();

		// Filter out fonts that match the pattern.
		$filtered_fonts = array_filter(
			$fonts,
			function ( $font ) use ( $pattern, $exclusions, $extensions ) {
				if ( ! in_array( pathinfo( $font, PATHINFO_EXTENSION ), $extensions, true ) ) {
					return false;
				}

				// Check exact match ( Mainly url match ).
				if ( in_array( $font, $exclusions, true ) ) {
					return false;
				}

				// Check for substring match using regex.
				return ! preg_match( $pattern, $font );
			}
		);

		return array_values( $filtered_fonts );
	}
}
