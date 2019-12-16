<?php

defined( 'ABSPATH' ) || exit;

/**
 * Conflict with Thrive Leads: override the DONOTCACHEPAGE behavior because this plugin add this constant!
 *
 * @since 2.5
 */
function rocket_override_donotcachepage_on_thrive_leads() {
	return defined( 'TVE_LEADS_VERSION' ) && TVE_LEADS_VERSION > 0;
}
add_filter( 'rocket_override_donotcachepage', 'rocket_override_donotcachepage_on_thrive_leads' );
