<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );

/**
 * Personnalisation de l'interval de temps (en secondes) entre chaque purge automatique du cache
 * Ce paramètre peut être modifié à partir de la page d'options du plugin
 * Par défaut, l'interval est de 4 heures
 *
 * since 1.0
 *
 */

add_filter( 'cron_schedules', 'rocket_purge_cron_schedule' );
function rocket_purge_cron_schedule( $schedules )
{

	$schedules['rocket_purge'] = array(
		'interval'	=> get_rocket_cron_interval(),
		'display' 	=> 'WP Rocket Purge',
	);

	return $schedules;

}



/**
 * Planification de la tâche cron
 * Si la tâche n'est pas programmée, elle est automatiquement déclenchée
 *
 * since 1.0
 *
 */

add_action( 'wp', 'rocket_purge_cron_scheduled' );
function rocket_purge_cron_scheduled()
{

	if( !wp_next_scheduled( 'rocket_purge_time_event' ) )
		wp_schedule_event( time() + get_rocket_cron_interval(), 'rocket_purge', 'rocket_purge_time_event' );

}



/**
 * TO DO - Description
 *
 * since 1.0
 *
 */

add_action( 'rocket_purge_time_event', 'do_rocket_purge_cron' );
function do_rocket_purge_cron() {

	// Remove cache dir
	rocket_rrmdir( WP_ROCKET_CACHE_PATH );

	// Re-create the cache dir
	mkdir( WP_ROCKET_CACHE_PATH, 0755 );

}