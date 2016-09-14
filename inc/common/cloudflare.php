<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Set Real IP from CloudFlare
 *
 * @since 2.5.4
 * @source cloudflare.php - https://wordpress.org/plugins/cloudflare/
 */
add_action( 'init', '__rocket_set_real_ip_cloudflare' , 1 );
function __rocket_set_real_ip_cloudflare() {
    global $is_cf;

    $is_cf = ( isset( $_SERVER["HTTP_CF_CONNECTING_IP"] ) ) ? true : false;

	// only run this logic if the REMOTE_ADDR is populated, to avoid causing notices in CLI mode
    if ( isset( $_SERVER["REMOTE_ADDR"] ) ) {
		if ( strpos( $_SERVER["REMOTE_ADDR"], ":" ) === false ) {

			$cf_ip_ranges = array(
				"199.27.128.0/21",
				"173.245.48.0/20",
				"103.21.244.0/22",
				"103.22.200.0/22",
				"103.31.4.0/22",
				"141.101.64.0/18",
				"108.162.192.0/18",
				"190.93.240.0/20",
				"188.114.96.0/20",
				"197.234.240.0/22",
				"198.41.128.0/17",
				"162.158.0.0/15",
				"104.16.0.0/12",
				"172.64.0.0/13"
			);
			// IPV4: Update the REMOTE_ADDR value if the current REMOTE_ADDR value is in the specified range.
			foreach ( $cf_ip_ranges as $range ) {
				if ( rocket_ipv4_in_range( $_SERVER["REMOTE_ADDR"], $range ) ) {
					if ( $_SERVER["HTTP_CF_CONNECTING_IP"] ) {
						$_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_CF_CONNECTING_IP"];
					}
					break;
				}
			}
		}
		else {
			$cf_ip_ranges = array(
				"2400:cb00::/32",
				"2606:4700::/32",
				"2803:f800::/32",
				"2405:b500::/32",
				"2405:8100::/32"
			);
			$ipv6 = get_rocket_ipv6_full($_SERVER["REMOTE_ADDR"]);
			foreach ( $cf_ip_ranges as $range ) {
				if ( rocket_ipv6_in_range( $ipv6, $range ) ) {
					if ( $_SERVER["HTTP_CF_CONNECTING_IP"]) {
						$_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_CF_CONNECTING_IP"];
					}
					break;
				}
			}
		}
	}

    // Let people know that the CF WP plugin is turned on, except if white label is active
    if ( ! headers_sent() ) {
        if ( rocket_is_white_label() ) {
            $powered_by = get_rocket_option( 'wl_plugin_name' );
        } else {
            $powered_by = 'WP Rocket ' . WP_ROCKET_VERSION;
        }

        header( "X-CF-Powered-By: " . $powered_by );
    }
}

/**
 * Reporting Spam IP to CloudFlare
 *
 * @since 2.5.4
 */
add_action( 'wp_set_comment_status', '__rocket_reporting_spam_ip_to_cloudflare', 1, 2 );
function __rocket_reporting_spam_ip_to_cloudflare( $id, $status ) {
	if ( $status == 'spam' ) {
		$comment = get_comment( $id );

		if ( !is_null( $comment ) ) {
			$payload = array(
				"a" 	=> $comment->comment_author,
				"am" 	=> $comment->comment_author_email,
				"ip" 	=> $comment->comment_author_IP,
				"con" 	=> substr( $comment->comment_content, 0, 100 )
			);

			$payload = urlencode( json_encode( $payload ) );

			$GLOBALS['rocket_cloudflare']->reporting_spam_ip( $payload );
		}
	}
}