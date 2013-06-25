<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );



/**
 * Add menu in admin bar
 * From this menu, you can preload the cache files, clear entire domain cache or post cache (front & back-end)
 *
 * since 1.0
 *
 */

add_action( 'admin_bar_menu', 'rocket_admin_bar', PHP_INT_MAX );
function rocket_admin_bar( $wp_admin_bar )
{
	$action = 'purge_cache';
	// Parent
    $wp_admin_bar->add_menu(array(
	    'id'    => 'wp-rocket',
	    'title' => 'WP Rocket',
	    'href'  => admin_url( 'options-general.php?page=wprocket' ),
	));
		// Purge All
		$wp_admin_bar->add_menu(array(
			'parent'	=> 'wp-rocket',
			'id' 		=> 'purge-all',
			'title' 	=> sprintf( __( 'Vider le cache <span class="count-cache" title="%1$d files">%1$d</span>', 'rocket' ), rocket_count_cache_contents() ),
			'href' 		=> wp_nonce_url( admin_url( 'admin-post.php?action='.$action.'&type=all' ), $action.'_all' ),
		));

		if( is_admin() )
		{
			// Purge a post
			global $pagenow, $post;
			if( $post && $pagenow=='post.php' && isset( $_GET['action'], $_GET['post'] ) )
			{
				$pobject = get_post_type_object( $post->post_type );
				$wp_admin_bar->add_menu(array(
					'parent' => 'wp-rocket',
					'id' => 'purge-post',
					'title' => sprintf( __( 'Purger cet article', 'rocket' ), $pobject->labels->singular_name ),
					'href' => wp_nonce_url( admin_url( 'admin-post.php?action='.$action.'&type=post-'.$post->ID ), $action.'_post-'.$post->ID ),
				));
			}
		}
		else {
			// Purge this URL (frontend)
			$wp_admin_bar->add_menu(array(
				'parent' => 'wp-rocket',
				'id' => 'purge-url',
				'title' => __( 'Purger cette URL', 'rocket' ),
				'href' => wp_nonce_url( admin_url( 'admin-post.php?action='.$action.'&type=url' ), $action.'_url' ),
			));
		}
		$action = 'preload';
		// Go robot gogo !
		$wp_admin_bar->add_menu(array(
			'parent' => 'wp-rocket',
			'id' => 'preload-cache',
			'title' => __( 'Précharger le cache', 'rocket' ),
			'href' => wp_nonce_url( admin_url( 'admin-post.php?action='.$action ), $action ),
		));
}



/**
 * Add CSS for WP Rocket Admin Bar
 *
 * since 1.0
 *
 */

add_action( 'wp_before_admin_bar_render', 'rocket_wp_before_admin_bar_render' );
function rocket_wp_before_admin_bar_render()
{ ?>

	<style>
		.count-cache, #wpadminbar .count-cache {
			background-color: #464646;
			border-radius: 10px;
			color: white;
			display: inline-block;
			font-size: 11px;
			font-weight: bold;
			height: 13px;
			line-height: 1.2em;
			margin-left: 2px;
			min-width: 12px;
			padding: 2px 3px;
			text-align: center;
		}
	</style>
<?php
}