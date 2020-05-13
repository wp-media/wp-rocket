<?php
namespace WP_Rocket\Addon\Varnish;

use WP_Rocket\Admin\Options_Data;

/**
 * Varnish cache purge
 *
 * @since 3.5
 * @author Remy Perona
 */
class Varnish {
	/**
	 * WP Rocket options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Send purge request to Varnish
	 *
	 * @since 3.5
	 *
	 * @param  string $url The URL to purge.
	 * @return void
	 */
	public function purge( $url ) {
		$parse_url      = get_rocket_parse_url( $url );
		$x_purge_method = 'default';
		$regex          = '';

		if ( 'regex' === $parse_url['query'] ) {
			$x_purge_method = 'regex';
			$regex          = '.*';
		}

		/**
		* Filter the Varnish IP to call
		*
		* @since 2.6.8
		* @param string|array $varnish_ip The Varnish IP
		*/
		$varnish_ip = apply_filters( 'rocket_varnish_ip', [] );

		if ( defined( 'WP_ROCKET_VARNISH_IP' ) && ! $varnish_ip ) {
			$varnish_ip = WP_ROCKET_VARNISH_IP;
		}

		if ( empty( $varnish_ip ) ) {
			$varnish_ip = [ '' ];
		} elseif ( \is_string( $varnish_ip ) ) {
			$varnish_ip = (array) $varnish_ip;
		}

		/**
		 * Filter the HTTP protocol (scheme)
		 *
		 * @since 2.7.3
		 * @param string $scheme The HTTP protocol
		 */
		$scheme = apply_filters( 'rocket_varnish_http_purge_scheme', 'http' );

		/**
		 * Filters the headers to send with the Varnish purge request
		 *
		 * @since 3.1
		 * @author Remy Perona
		 *
		 * @param array $headers Headers to send.
		 */
		$headers = apply_filters(
			'rocket_varnish_purge_headers',
			[
				/**
				* Filters the host value passed in the request headers
				*
				* @since 2.8.15
				* @param string $host The host value.
				*/
				'host'           => apply_filters( 'rocket_varnish_purge_request_host', $parse_url['host'] ),
				'X-Purge-Method' => $x_purge_method,
			]
		);

		/**
		 * Filters the arguments passed to the Varnish purge request
		 *
		 * @since 3.5
		 * @author Remy Perona
		 *
		 * @param array Array of arguments for the request.
		 */
		$args = apply_filters(
			'rocket_varnish_purge_request_args',
			[
				'method'      => 'PURGE',
				'blocking'    => false,
				'redirection' => 0,
				'headers'     => $headers,
			]
		);

		foreach ( $varnish_ip as $ip ) {
			$host      = ! empty( $ip ) ? $ip : str_replace( '*', '', $parse_url['host'] );
			$purge_url = $scheme . '://' . $host . $parse_url['path'] . $regex;

			wp_remote_request( $purge_url, $args );
		}
	}
}
