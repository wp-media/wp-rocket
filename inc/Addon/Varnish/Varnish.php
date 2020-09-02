<?php
namespace WP_Rocket\Addon\Varnish;

/**
 * Varnish cache purge
 *
 * @since 3.5
 */
class Varnish {
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
		 *
		 * @param array $args Array of arguments for the request.
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

		foreach ( $this->get_varnish_ips() as $ip ) {
			$host           = ! empty( $ip ) ? $ip : str_replace( '*', '', $parse_url['host'] );
			$purge_url_main = $scheme . '://' . $host . $parse_url['path'];

			/**
			 * Filters the purge url.
			 *
			 * @since 3.6.3
			 *
			 * @param string $purge_url_full Full url contains the main url plus regex pattern.
			 * @param string $purge_url_main Main purge url without any additions params.
			 * @param string $regex          Regex string.
			 */
			$purge_url = apply_filters(
				'rocket_varnish_purge_url',
				$purge_url_main . $regex,
				$purge_url_main,
				$regex
			);

			wp_remote_request( $purge_url, $args );
		}
	}

	/**
	 * Gets an array of Varnish IPs to send the purge request to
	 *
	 * @return array
	 */
	private function get_varnish_ips() {
		/**
		* Filter the Varnish IP to call
		*
		* @since 2.6.8
		* @param string|array $varnish_ip The Varnish IP
		*/
		$varnish_ip = apply_filters( 'rocket_varnish_ip', [] );
		$constant   = rocket_get_constant( 'WP_ROCKET_VARNISH_IP' );

		if (
			! empty( $constant )
			&&
			empty( $varnish_ip )
		) {
			$varnish_ip = $constant;
		}

		if ( empty( $varnish_ip ) ) {
			$varnish_ip = [ '' ];
		} elseif ( is_string( $varnish_ip ) ) {
			$varnish_ip = (array) $varnish_ip;
		}

		return $varnish_ip;
	}
}
