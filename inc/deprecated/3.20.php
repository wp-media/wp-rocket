<?php

/**
 * Determines if we should send the analytics data
 *
 * @since 2.11
 * @deprecated 3.20.5
 * @author Remy Perona
 *
 * @return bool True if we should send them, false otherwise
 */
function rocket_send_analytics_data() {
	_deprecated_function( __FUNCTION__, '3.20.5' );

	if ( ! get_rocket_option( 'analytics_enabled' ) ) {
		return false;
	}

	if ( ! current_user_can( 'rocket_manage_options' ) ) {
		return false;
	}

	if ( false === get_transient( 'rocket_send_analytics_data' ) ) {
		set_transient( 'rocket_send_analytics_data', 1, 7 * DAY_IN_SECONDS );
		return true;
	}

	return false;
}
