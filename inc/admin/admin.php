<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Link to the configuration page of the plugin
 *
 * Since 1.0
 *
 */

add_filter( 'plugin_action_links_'.plugin_basename( WP_ROCKET_FILE ), 'rocket_settings_action_links' );
function rocket_settings_action_links( $actions )
{
    array_unshift( $actions, '<a href="' . admin_url( 'options-general.php?page=wprocket' ) . '">' . __( 'Settings' ) . '</a>' );
    return $actions;
}



/**
 * Add some informations about authors in plugins list area
 *
 * Since 1.0
 *
 */
 
add_filter( 'plugin_row_meta', 'rocket_plugin_row_meta', 10, 2 );
function rocket_plugin_row_meta( $plugin_meta, $plugin_file )
{
	if( plugin_basename( WP_ROCKET_FILE ) == $plugin_file ):
		$last = end( $plugin_meta );
		$plugin_meta = array_slice( $plugin_meta, 0, -2 );
		$a = array();
		$authors = array(// array(	'name'=>'WP-Rocket', 'url'=>'http://wp-rocket.me' ),
						array( 	'name'=>'Jonathan Buttigieg', 'url'=>'http://www.geekpress.fr' ),
						array( 	'name'=>'Julio Potier', 'url'=>'http://www.boiteaweb.fr' ),
						array( 	'name'=>'Jean-Baptiste Marchand-Arvier', 'url'=>'http://jbma.me/blog/' ),
					);
		foreach( $authors as $author )
			$a[] = '<a href="' . $author['url'] . '" title="' . esc_attr__( 'Visit author homepage' ) . '">' . $author['name'] . '</a>';
		$a = sprintf( __( 'By %s' ), wp_sprintf( '%l', $a ) );
		$plugin_meta[] = $a;
		$plugin_meta[] = $last;
	endif;
	return $plugin_meta;
}



/**
 * Add a link "Purge this cache" in the post edit area
 *
 * since 1.0
 *
 */
 
add_filter( 'page_row_actions', 'rocket_row_actions', 10, 2 );
add_filter( 'post_row_actions', 'rocket_row_actions', 10, 2 );
function rocket_row_actions( $actions, $post )
{
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=post-'.$post->ID ), 'purge_cache_post-'.$post->ID );
    $actions['rocket_purge'] = '<a href="'.$url.'">Purger le cache</a>';
    return $actions;
}



/**
 * Add a link "Purge cache" in the post submit area
 *
 * since 1.0
 *
 */

add_action( 'post_submitbox_start', 'rocket_post_submitbox_start' );
function rocket_post_submitbox_start()
{
	global $post;
	if ( current_user_can( 'edit_post', $post->ID ) )
		echo '<div id="purge-action"><a class="button-secondary" href="'.wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=post-' . $post->ID ), 'purge_cache_post-' . $post->ID ).'">'.__( 'Purge cache', 'wp-rocket' ).'</a></div>';
}