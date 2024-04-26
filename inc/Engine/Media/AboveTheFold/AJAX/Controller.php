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
		$images    = isset( $_POST['images'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['images'] ) ) ) : '';
		$lcp       = 'not found';
		$viewport  = [];

		$keys = [ 'bg_set', 'src' ];

		foreach ( $images as $image ) {
			if ( 'lcp' === $image->label && 'not found' === $lcp ) {
				$lcp = $this->create_object( $image, $keys );
			} elseif ( 'above-the-fold' === $image->label ) {
				$viewport_image = $this->create_object( $image, $keys );
				if ( null !== $viewport_image ) {
					$viewport[] = $viewport_image;
				}
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
			'lcp'           => ( is_array( $lcp ) || is_object( $lcp ) ) ? wp_json_encode( $lcp ) : $lcp,
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

	/**
	 * Creates an object with the 'type' property and the first key that exists in the image object.
	 *
	 * @param object $image The image object.
	 * @param array  $keys  An array of keys in the order of their priority.
	 *
	 * @return object|null Returns an object with the 'type' property and the first key that exists in the image object. If none of the keys exist in the image object, it returns null.
	 */
	private function create_object( $image, $keys ) {
		// Bail out if type doesn't exist, it would mean no lcp has been found
		if ( ! isset( $image->type ) ) {
			return null;
		}

		$object       = new \stdClass();
		$object->type = $image->type;

		switch ( $image->type ) {
			case 'img-srcset':
				// If the type is 'img-srcset', add all the required parameters to the object.
				$object->src    = $image->src;
				$object->srcset = $image->srcset;
				$object->sizes  = $image->sizes;
				break;
			case 'picture':
				$object->src     = $image->src;
				$object->sources = $image->sources;
				break;
			default:
				// For other types, add the first non-empty key to the object.
				foreach ( $keys as $key ) {
					if ( isset( $image->$key ) && ! empty( $image->$key ) ) {
						$object->$key = $image->$key;
						break;
					}
				}
				break;
		}

		// If none of the keys exist in the image object, return null.
		if ( count( (array) $object ) <= 1 ) {
			return null;
		}

		return $object;
	}

	/**
	 * Checks if there is existing LCP data for the current URL and device type.
	 *
	 * This method is called via AJAX. It checks if there is existing LCP data for the current URL and device type.
	 * If the data exists, it returns a JSON success response with true. If the data does not exist, it returns a JSON success response with false.
	 * If the context is not allowed, it returns a JSON error response with false.
	 *
	 * @return void
	 */
	public function check_lcp_data() {
		check_ajax_referer( 'rocket_lcp', 'rocket_lcp_nonce' );

		if ( ! $this->context->is_allowed() ) {
			wp_send_json_error( false );
			return;
		}

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
