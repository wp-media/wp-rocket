<?php
/**
 * Mocked wpe_param() from WP Engine to the minimum requirement for tests to run.
 */
if ( ! function_exists( 'wpe_param') ) {
	function wpe_param( $option ) {
		if ( 'purge-all' === $option ) {
			return true;
		}
		return false;
	}
}
