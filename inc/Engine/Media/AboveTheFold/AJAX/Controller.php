<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\AJAX;

use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Optimization\UrlTrait;

class Controller {
	use UrlTrait;

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
		$images    = isset( $_POST['images'] ) ? json_decode( wp_unslash( $_POST['images'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
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

		$keys = [ 'bg_set', 'src' ];

		foreach ( (array) $images as $image ) {
			if ( empty( $image->type ) ) {
				continue;
			}

			$image_object = $this->create_object( $image, $keys );

			if ( ! $image_object || ! $this->validate_image( $image_object ) ) {
				continue;
			}

			if ( 'lcp' === $image->label ) {
				$lcp = $image_object;
				continue;
			}

			if ( 'above-the-fold' === $image->label && 0 < $max_atf_images_number ) {
				$viewport[] = $image_object;

				--$max_atf_images_number;
			}
		}

		$row = $this->query->get_row( $url, $is_mobile );

		if ( ! empty( $row ) ) {
			wp_send_json_error( 'item already in the database' );
			return;
		}

		$status                               = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
		list( $status_code, $status_message ) = $this->get_status_code_message( $status );

		$item = [
			'url'           => $url,
			'is_mobile'     => $is_mobile,
			'status'        => $status_code,
			'error_message' => $status_message,
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
	 * Get status code and message to be saved into the database
	 *
	 * @param string $status Current status code from $_POST.
	 * @return array
	 */
	private function get_status_code_message( $status ) {
		$status_code    = 'success' !== $status ? 'failed' : 'completed';
		$status_message = '';

		switch ( $status ) {
			case 'script_error':
				$status_message = esc_html__( 'Script error', 'rocket' );
				break;
			case 'timeout':
				$status_message = esc_html__( 'Script timeout', 'rocket' );
				break;
		}

		return [
			$status_code,
			$status_message,
		];
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
		$object       = new \stdClass();
		$object->type = $image->type ?? 'img';

		switch ( $object->type ) {
			case 'img-srcset':
				// If the type is 'img-srcset', add all the required parameters to the object.
				$object->srcset = $image->srcset;
				$object->sizes  = $image->sizes;
				break;
			case 'picture':
				$object->sources = $image->sources;
				break;
			default:
				// For other types, add the first non-empty key to the object.
				foreach ( $keys as $key ) {
					if ( isset( $image->$key ) && ! empty( $image->$key ) ) {
						if ( is_array( $image->$key ) ) {
							$sanitized_array = array_map(
								function ( $item ) {
									if ( ! empty( $item->src ) ) {
										$item->src = $this->sanitize_image_url( $item->src );
									}
									return $item;
								},
								$image->$key
							);

							$object->$key = $sanitized_array;

						} else {
							$object->$key = $this->sanitize_image_url( $image->$key );
						}
						break;
					}
				}
				break;
		}

		// If none of the keys exist in the image object, return null.
		if ( count( (array) $object ) <= 1 ) {
			return null;
		}

		// Returned objects must always have a src for front-end optimization.
		// Except bg-img and bg-img-set for which we use bg_set only.
		// To keep it simple and safe for now, we enforce src for all, pending a refactor.
		if ( ! isset( $object->src ) ) {
			$object->src = '';
		}

		return $object;
	}

	/**
	 * Sanitize image url before saving them into database.
	 *
	 * @param string $url The image url.
	 * @return string
	 */
	private function sanitize_image_url( string $url ) {
		$sanitize_url = esc_url_raw( $url );
		if ( $this->is_relative( $url ) && strpos( $url, '/' ) !== 0 ) {
			$sanitize_url = esc_url_raw( '/' . $url );
			$sanitize_url = substr( $sanitize_url, 1 );
		}

		return $sanitize_url;
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

	/**
	 * Validate image object.
	 *
	 * @param object $image_object Image full object.
	 * @return bool
	 */
	private function validate_image( $image_object ) {
		$valid_image = $this->validate_image_src( $image_object->src ?? '' );

		/**
		 * Filters If the image src is a valid image or not.
		 *
		 * @param bool   $valid_image Valid image or not.
		 * @param string $image_src_url Image src url.
		 * @param object $image_object Image object with full details.
		 */
		return (bool) apply_filters( 'rocket_atf_valid_image', $valid_image, $image_object->src, $image_object );
	}

	/**
	 * Make sure that this url is valid image without loading the image itself.
	 *
	 * @param string $image_src Image src url.
	 * @return bool
	 */
	private function validate_image_src( $image_src ) {
		if ( empty( $image_src ) ) {
			return false;
		}

		// Here we get the url PATH part only to strip all query strings.
		$image_src_path = wp_parse_url( $image_src, PHP_URL_PATH );
		if ( empty( $image_src_path ) ) {
			return false;
		}

		$image_src_filetype_array = wp_check_filetype( $image_src_path );
		return ! empty( $image_src_filetype_array['type'] ) && str_starts_with( $image_src_filetype_array['type'], 'image/' );
	}
}
