<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Conflict with Thrive Leads: override the DONOTCACHEPAGE behavior because this plugin add this constant!
 *
 * @since 2.5
 */
add_filter( 'rocket_override_donotcachepage', '__override_rocket_donotcachepage_on_thrive_leads' );
function __override_rocket_donotcachepage_on_thrive_leads() {
	return defined( 'TVE_LEADS_VERSION' ) && TVE_LEADS_VERSION > 0;
}