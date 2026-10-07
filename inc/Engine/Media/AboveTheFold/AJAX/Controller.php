<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\AJAX;

use WP_Rocket\Engine\Common\PerformanceHints\AJAX\AJAXControllerTrait;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Optimization\UrlTrait;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\ControllerInterface;

class Controller implements ControllerInterface {
	use UrlTrait;
	use AJAXControllerTrait;

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
	 * An array of unsupported atf schemes.
	 *
	 * @var array
	 */
	private $invalid_schemes = [
		'chrome-[^:]+://',
	];

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
	 * @return array
	 */
	public function add_data(): array {
		$payload = [
			'lcp' => '',
		];

		check_ajax_referer( 'rocket_beacon', 'rocket_beacon_nonce' );

		if ( ! $this->context->is_allowed() ) {
			$payload['lcp'] = 'not allowed';

			return $payload;
		}

		$url       = isset( $_POST['url'] ) ? untrailingslashit( esc_url_raw( wp_unslash( $_POST['url'] ) ) ) : '';
		$is_mobile = isset( $_POST['is_mobile'] ) ? filter_var( wp_unslash( $_POST['is_mobile'] ), FILTER_VALIDATE_BOOLEAN ) : false;
		$results   = isset( $_POST['results'] ) ? json_decode( wp_unslash( $_POST['results'] ) ) : (object) [ 'lcp' => [] ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$images    = $results->lcp ?? [];
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

			if ( isset( $image->label ) && 'lcp' === $image->label ) {
				$lcp = $image_object;
				continue;
			}

			if ( isset( $image->label ) && 'above-the-fold' === $image->label && 0 < $max_atf_images_number ) {
				$viewport[] = $image_object;

				--$max_atf_images_number;
			}
		}

		$row = $this->query->get_row( $url, $is_mobile );

		if ( ! empty( $row ) ) {
			$payload['lcp'] = 'item already in the database';

			return $payload;
		}

		$status                               = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
		list( $status_code, $status_message ) = $this->get_status_code_message( $status );

		$item = [
			'url'           => $url,
			'is_mobile'     => $is_mobile,
			'status'        => $status_code,
			'error_message' => $status_message,
			'lcp'           => is_object( $lcp ) ? wp_json_encode( $lcp ) : $lcp,
			'viewport'      => wp_json_encode( $viewport ),
			'last_accessed' => current_time( 'mysql', true ),
		];

		$result = $this->query->add_item( $item );

		if ( ! $result ) {
			$payload['lcp'] = 'error when adding the entry to the database';

			return $payload;
		}

		$payload['lcp'] = $item;
		return $payload;
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

		// Every branch below stores raw string data supplied via the unauthenticated
		// `rocket_beacon` AJAX action. It must be sanitized/validated at storage time,
		// independent of any render-time escaping — a future new `case` must not skip this.
		switch ( $object->type ) {
			case 'img-srcset':
				// If the type is 'img-srcset', add all the required parameters to the object.
				if ( isset( $image->src ) && ! empty( $image->src ) && is_string( $image->src ) ) {
					$object->src = $this->sanitize_image_url( $image->src );
				}

				$raw_srcset       = $this->get_string_prop( $image, 'srcset' );
				$sanitized_srcset = $this->sanitize_srcset( $raw_srcset );

				if ( empty( $sanitized_srcset ) ) {
					// No srcset candidate survived: fall back to a src-only `img` object rather
					// than losing the LCP. Never store an empty srcset. The src is still checked
					// by validate_image(), and an object without a src is rejected below.
					$object->type = 'img';
					break;
				}

				$object->srcset = $sanitized_srcset;

				$raw_sizes     = $this->get_string_prop( $image, 'sizes' );
				$object->sizes = ! empty( $raw_sizes ) ? $this->sanitize_sizes( $raw_sizes ) : '';
				break;
			case 'picture':
				if ( isset( $image->src ) && ! empty( $image->src ) && is_string( $image->src ) ) {
					$object->src = $this->sanitize_image_url( $image->src );
				}
				$object->sources = array_map(
					function ( $source ) {
						if ( empty( $source->type ) ) {
							Logger::notice( 'The source type is missing in the image object.', [ 'source' => $source ] );
						}

						return $this->validate_source_object( $source );
					},
					$image->sources
				);
				$object->sources = array_filter( $object->sources ); // Remove invalid sources.
				break;
			default:
				// For other types, add the first non-empty key to the object.
				foreach ( $keys as $key ) {
					if ( isset( $image->$key ) && ! empty( $image->$key ) ) {
						if ( is_array( $image->$key ) ) {
							$sanitized_array = array_map(
								function ( $item ) {
									// Rebuild each item from a whitelist rather than mutating and
									// returning the attacker-supplied object as-is, so unrecognized
									// properties never ride through to the stored JSON.
									$sanitized_item = new \stdClass();

									$item_src            = is_object( $item ) ? $this->get_string_prop( $item, 'src' ) : '';
									$sanitized_item->src = '' !== $item_src ? $this->sanitize_image_url( $item_src ) : '';

									return $sanitized_item;
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
	 * Safely read a string property from an untrusted, attacker-supplied object.
	 *
	 * @param object $data The object to read the property from.
	 * @param string $key  The property name to read.
	 * @return string The property value if it's set and is a string, empty string otherwise.
	 */
	private function get_string_prop( $data, string $key ): string {
		return isset( $data->$key ) && is_string( $data->$key ) ? $data->$key : '';
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
	 * Checks if there is existing data for the current URL and device type from the beacon script.
	 *
	 * This method is called via AJAX. It checks if there is existing LCP data for the current URL and device type.
	 * If the data exists, it returns a JSON success response with true. If the data does not exist, it returns a JSON success response with false.
	 * If the context is not allowed, it returns a JSON error response with false.
	 *
	 * @return array
	 */
	public function check_data(): array {
		$payload = [
			'lcp' => false,
		];

		check_ajax_referer( 'rocket_beacon', 'rocket_beacon_nonce' );

		if ( ! $this->context->is_allowed() ) {
			$payload['lcp'] = true;
			return $payload;
		}

		$url       = isset( $_POST['url'] ) ? untrailingslashit( esc_url_raw( wp_unslash( $_POST['url'] ) ) ) : '';
		$is_mobile = isset( $_POST['is_mobile'] ) ? filter_var( wp_unslash( $_POST['is_mobile'] ), FILTER_VALIDATE_BOOLEAN ) : false;

		$row = $this->query->get_row( $url, $is_mobile );

		if ( ! empty( $row ) ) {
			$payload['lcp'] = true;
			return $payload;
		}

		return $payload;
	}

	/**
	 * Validate image object.
	 *
	 * @param object $image_object Image full object.
	 * @return bool
	 */
	private function validate_image( $image_object ): bool {
		$valid_image = ! empty( $image_object->src ) ? $this->validate_image_src( $image_object->src ?? '' ) : true;

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
	private function validate_image_src( string $image_src ): bool {
		if ( empty( $image_src ) ) {
			return false;
		}

		/**
		 * Filters the supported schemes of LCP/ATF images.
		 *
		 * @param array  $invalid_schemes Array of invalid schemes.
		 */
		$invalid_schemes = wpm_apply_filters_typed( 'array', 'rocket_atf_invalid_schemes', $this->invalid_schemes );

		$invalid_schemes = implode( '|', $invalid_schemes );

		if ( preg_match( '#^' . $invalid_schemes . '#', $image_src ) ) {
			return false;
		}

		// Here we get the url PATH part only to strip all query strings.
		$image_src_path = wp_parse_url( $image_src, PHP_URL_PATH );
		if ( empty( $image_src_path ) ) {
			return false;
		}

		// Add svg to allowed mime types.
		$allowed_mime_types        = get_allowed_mime_types();
		$allowed_mime_types['svg'] = 'image/svg+xml';
		$image_src_filetype_array  = wp_check_filetype( $image_src_path, $allowed_mime_types );

		return ! empty( $image_src_filetype_array['type'] ) && str_starts_with( $image_src_filetype_array['type'], 'image/' );
	}

	/**
	 * Validate and sanitize a picture source object
	 *
	 * @param object|array $source Raw source data from user input.
	 * @return array|null Sanitized source object, or null if invalid.
	 */
	private function validate_source_object( $source ) {
		if ( ! is_object( $source ) ) {
			return null;
		}

		$source = (array) $source;

		// Validate required fields exist.
		if ( empty( $source['srcset'] ) ) {
			return null;
		}

		// Validate and sanitize srcset.
		$sanitized_srcset = $this->sanitize_srcset( $source['srcset'] );
		if ( empty( $sanitized_srcset ) ) {
			return null;
		}

		// Validate and sanitize media query.
		$sanitized_media = ! empty( $source['media'] )
			? $this->sanitize_media_query( $source['media'] )
			: '';

		// Validate and sanitize sizes.
		$sanitized_sizes = ! empty( $source['sizes'] )
			? $this->sanitize_sizes( $source['sizes'] )
			: '';

		// Validate MIME type.
		$sanitized_type = $this->validate_mime_type( $source['type'] );
		return [
			'srcset' => $sanitized_srcset,
			'media'  => $sanitized_media,
			'type'   => $sanitized_type,
			'sizes'  => $sanitized_sizes,
		];
	}

	/**
	 * Sanitize srcset attribute.
	 *
	 * @param string $srcset Raw srcset value.
	 * @return string Sanitized srcset or empty string if invalid.
	 */
	private function sanitize_srcset( $srcset ) {
		// Validate srcset format: url [descriptor], url [descriptor], ...
		// An invalid candidate is skipped (never stored) without rejecting the valid ones.
		$candidates    = $this->parse_srcset_candidates( $srcset );
		$clean_sources = [];

		foreach ( $candidates as $candidate ) {
			list( $url, $descriptor ) = $candidate;

			if ( $this->is_valid_srcset_candidate( $url, $descriptor ) ) {
				$clean_sources[] = $url . ( $descriptor ? ' ' . $descriptor : '' );
			}
		}

		return implode( ', ', $clean_sources );
	}

	/**
	 * Checks a single srcset candidate.
	 *
	 * @param string $url        Candidate URL.
	 * @param string $descriptor Candidate descriptor, empty when none.
	 * @return bool
	 */
	private function is_valid_srcset_candidate( string $url, string $descriptor ): bool {
		// Check for quotes, angle brackets, whitespace or event handlers in the URL.
		if (
			! preg_match( '/^[^\s<>"\']+$/', $url )
			|| $this->hasOnAttribute( $url )
		) {
			return false;
		}

		// Absolute http(s), protocol-relative or relative URL, but no other scheme (e.g. data:, javascript:).
		if (
			! preg_match( '/^https?:\/\//i', $url )
			&& preg_match( '/^[a-z][a-z0-9+.\-]*:/i', $url )
		) {
			return false;
		}

		// Width descriptors are integers, density descriptors may be decimals.
		// Example: "1x", "1.5x", ".5x" or "480w".
		return '' === $descriptor || (bool) preg_match( '/^(?:\d+w|(?:\d+(?:\.\d+)?|\.\d+)x)$/i', $descriptor );
	}

	/**
	 * Split a srcset into its candidates following the HTML srcset parsing rules.
	 *
	 * A candidate URL is a run of non-whitespace characters, so it may contain commas
	 * (e.g. "w_400,c_fill" or "fit=crop,faces"). Candidates are separated by the comma
	 * following the descriptor, or by trailing commas of the URL when it has no descriptor.
	 *
	 * @see https://html.spec.whatwg.org/multipage/images.html#parse-a-srcset-attribute
	 *
	 * @param string $srcset Raw srcset value.
	 * @return array<int, array{0: string, 1: string}> List of [ url, descriptor ] pairs.
	 */
	private function parse_srcset_candidates( string $srcset ): array {
		$whitespace = " \t\n\r\f";
		$length     = strlen( $srcset );
		$position   = 0;
		$candidates = [];

		while ( $position < $length ) {
			// Skip whitespace and commas between candidates.
			$position += strspn( $srcset, $whitespace . ',', $position );

			if ( $position >= $length ) {
				break;
			}

			$url_length = strcspn( $srcset, $whitespace, $position );
			$url        = substr( $srcset, $position, $url_length );
			$position  += $url_length;
			$descriptor = '';

			if ( ',' === substr( $url, -1 ) ) {
				// Trailing commas end the candidate: it has no descriptor.
				$url = rtrim( $url, ',' );
			} else {
				$descriptor_length = strcspn( $srcset, ',', $position );
				$descriptor        = trim( substr( $srcset, $position, $descriptor_length ), $whitespace );
				$position         += $descriptor_length;
			}

			$candidates[] = [ $url, $descriptor ];
		}

		return $candidates;
	}

	/**
	 * Sanitize media query attribute.
	 *
	 * @param string $media Raw media query value.
	 * @return string Sanitized media query or empty string if invalid.
	 */
	private function sanitize_media_query( $media ) {
		// Check for event handlers or malicious content.
		if ( $this->hasOnAttribute( $media ) ) {
			return '';
		}

		// Check for quotes or angle brackets.
		if ( $this->hasQuotes( $media ) ) {
			return '';
		}

		// Validate media query contains only allowed characters.
		// Allow: (, ), and, or, not, min/max-width, spaces, numbers, px, em, rem.
		if ( ! preg_match( '/^[\w\s\(\)\-:,\.]+$/i', $media ) ) {
			return '';
		}

		return sanitize_text_field( $media );
	}

	/**
	 * Sanitize sizes attribute.
	 *
	 * @param string $sizes Raw sizes value.
	 * @return string Sanitized sizes or empty string if invalid.
	 */
	private function sanitize_sizes( $sizes ) {
		// Check for event handlers or malicious content.
		if ( $this->hasOnAttribute( $sizes ) ) {
			return '';
		}

		// Set aside media range conditions, e.g. "(width >= 1000px)" or "(400px <= width <= 700px)":
		// they are the only place where `<`, `>` and `=` are accepted. The rest of the value goes
		// through the checks below unchanged.
		$ranges = [];
		$check  = preg_replace_callback(
			$this->get_media_range_pattern(),
			function ( $matches ) use ( &$ranges ) {
				$ranges[] = preg_replace( '/\s+/', ' ', $matches[0] );

				return '(rocket-range-' . ( count( $ranges ) - 1 ) . ')';
			},
			$sizes
		);

		// Check for quotes or angle brackets.
		if ( ! is_string( $check ) || $this->hasQuotes( $check ) ) {
			return '';
		}

		// Validate sizes format: media_query width, media_query width, ...
		// Example: "(max-width: 600px) 480px, 800px" or "calc(100vw / 2), calc(50vw + 10px)".
		// The allow-list is deliberately permissive enough for media queries and calc()
		// (including / + *); it relies on hasOnAttribute()/hasQuotes() above having already
		// run, and on the returned value being passed through esc_attr() on output.
		if ( ! preg_match( '/^[\w\s\(\)\-:,\.vwpxem%\/\+\*]+$/i', $check ) ) {
			return '';
		}

		// The range conditions are already strictly validated: put them back after
		// sanitize_text_field(), which would otherwise encode their `<` as `&lt;`.
		return (string) preg_replace_callback(
			'/\(rocket-range-(\d+)\)/',
			function ( $matches ) use ( $ranges ) {
				return $ranges[ (int) $matches[1] ] ?? $matches[0];
			},
			sanitize_text_field( $check )
		);
	}

	/**
	 * Gets the pattern matching a media range condition.
	 *
	 * Matches one parenthesized condition comparing a range media feature to a value, e.g.
	 * "(width >= 1000px)", "(1000px < width)", "(aspect-ratio > 16/9)" or a two-sided
	 * "(400px <= width <= 700px)" where both operators point the same way.
	 *
	 * @return string
	 */
	private function get_media_range_pattern(): string {
		$feature = '(?:device-width|device-height|aspect-ratio|resolution|width|height)';
		$value   = '(?:\d*\.?\d+(?:[a-z]{1,4}|%)?(?:\s*\/\s*\d*\.?\d+)?)';
		$compare = '(?:[<>]=?|=)';

		return '/\(\s*(?:'
			. $feature . '\s*' . $compare . '\s*' . $value
			. '|' . $value . '\s*<=?\s*' . $feature . '\s*<=?\s*' . $value
			. '|' . $value . '\s*>=?\s*' . $feature . '\s*>=?\s*' . $value
			. '|' . $value . '\s*' . $compare . '\s*' . $feature
			. ')\s*\)/i';
	}

	/**
	 * Validate MIME type for picture sources.
	 *
	 * Ensures the MIME type is:
	 * - A valid image type
	 * - Allowed by WordPress
	 * - Safe for use in <picture> elements
	 *
	 * @param string $type Raw MIME type.
	 * @return string Sanitized MIME type or empty string if invalid.
	 */
	private function validate_mime_type( $type ) {
		// Sanitize input.
		$type = strtolower( trim( $type ) );

		// Must be an image type.
		if ( ! str_starts_with( $type, 'image/' ) ) {
			return '';
		}

		// Exclude SVG for security (optional - can contain inline scripts).
		// Remove this check if you want to support SVG.
		if ( 'image/svg+xml' === $type ) {
			return '';
		}

		// Get WordPress allowed image MIME types.
		$allowed_mimes = get_allowed_mime_types();
		$image_mimes   = array_filter(
			$allowed_mimes,
			function ( $mime ) {
				return str_starts_with( $mime, 'image/' );
			}
			);

		// Check if type is in WordPress's allowed list.
		if ( ! in_array( $type, $image_mimes, true ) ) {
			return '';
		}

		return $type;
	}

	/**
	 * Check if item has On JS attributes like onload.
	 *
	 * @param string $item Item to be checked.
	 * @return false|int
	 */
	private function hasOnAttribute( $item ) {
		// "on" must start a token so values like "options=crop" are not flagged.
		return preg_match( '/(?:^|[\s"\'])on\w+\s*=/i', $item );
	}

	/**
	 * Check for quotes, angle brackets, or other HTML-like content.
	 *
	 * @param string $item Item to be checked.
	 * @return false|int
	 */
	private function hasQuotes( $item ) {
		return preg_match( '/[<>"\']/i', $item );
	}
}
