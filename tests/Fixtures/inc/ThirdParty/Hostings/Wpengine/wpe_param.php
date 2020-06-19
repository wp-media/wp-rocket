<?php
/**
 * Mocked wpe_param() from WP Engine to the minimum requirement for tests to run.
 */
function wpe_param( $option ) {
	if ( 'purge-all' === $option ) {
		return true;
	}
	return false;
}
